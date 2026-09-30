<?php

namespace Automas\Lead\Jobs;

use Automas\Lead\Models\LeadImport;
use Automas\Lead\Models\LeadImportChunk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SplFileObject;
use Throwable;

class PrepareLeadImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public int $leadImportId
    ) {
        $this->onQueue('lead-imports');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                "prepare-lead-import:{$this->leadImportId}"
            ))->expireAfter(650),
        ];
    }

    public function handle(): void
    {
        $import = LeadImport::query()->findOrFail(
            $this->leadImportId
        );

        if (
            in_array(
                $import->status,
                ['processing', 'completed', 'cancelled'],
                true
            )
        ) {
            return;
        }

        if (
            !Storage::disk('local')->exists(
                $import->stored_path
            )
        ) {
            throw new RuntimeException(
                'The source CSV file could not be found.'
            );
        }

        $options = $import->options ?? [];
        $mapping = $import->column_mapping ?? [];
        $duplicateBy = (string) data_get($options, 'duplicate_by', 'phone');
        $phoneIndex = isset($mapping['phone']) ? (int) $mapping['phone'] : null;
        $emailIndex = isset($mapping['email']) ? (int) $mapping['email'] : null;

        $seenPhones = [];
        $seenEmails = [];

        $delimiter = (string) data_get(
            $options,
            'delimiter',
            ','
        );

        $hasHeader = (bool) data_get(
            $options,
            'has_header',
            true
        );

        $chunkSize = 1000;

        $directory = "lead-imports/{$import->uuid}/chunks";

        /*
         * Make retrying preparation idempotent.
         */
        Storage::disk('local')->deleteDirectory($directory);
        Storage::disk('local')->makeDirectory($directory);

        DB::transaction(function () use ($import): void {
            $import->chunks()->delete();

            $import->update([
                'status' => 'preparing',
                'total_chunks' => 0,
                'completed_chunks' => 0,
                'processed_rows' => 0,
                'inserted_rows' => 0,
                'updated_rows' => 0,
                'duplicate_rows' => 0,
                'skipped_rows' => 0,
                'skipped_unassigned_rows' => 0,
                'failed_rows' => 0,
                'failure_message' => null,
                'started_at' => now(),
                'completed_at' => null,
                'cancelled_at' => null,
            ]);
        });

        $source = new SplFileObject(
            Storage::disk('local')->path(
                $import->stored_path
            ),
            'r'
        );

        $source->setFlags(
            SplFileObject::READ_CSV
                | SplFileObject::DROP_NEW_LINE
        );

        $source->setCsvControl(
            separator: $delimiter,
            enclosure: '"',
            escape: '\\'
        );

        $headerSkipped = !$hasHeader;
        $dataRowNumber = 0;
        $chunkNumber = 0;
        $chunkRows = [];

        while (!$source->eof()) {
            if ($dataRowNumber % 1000 === 0) {
                $freshStatus = DB::table('lead_imports')->where('id', $import->id)->value('status');
                if (in_array($freshStatus, ['cancel_requested', 'cancelled'], true)) {
                    DB::table('lead_imports')->where('id', $import->id)->update(['status' => 'cancelled']);
                    return;
                }
            }

            $row = $source->fgetcsv();

            if (
                $row === false ||
                $this->isEmptyRow($row)
            ) {
                continue;
            }

            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $dataRowNumber++;

            $rawValues = array_map(
                fn(mixed $value): string => trim((string) $value),
                $row
            );

            $isFileDuplicate = false;
            $phone = null;
            $email = null;

            if ($phoneIndex !== null && isset($rawValues[$phoneIndex])) {
                $phone = $this->normalizePhoneForDeduplication($rawValues[$phoneIndex]);
            }
            if ($emailIndex !== null && isset($rawValues[$emailIndex])) {
                $email = mb_strtolower(trim($rawValues[$emailIndex]));
            }

            if (in_array($duplicateBy, ['phone', 'phone_or_email'], true) && $phone && isset($seenPhones[$phone])) {
                $isFileDuplicate = true;
            }
            if (in_array($duplicateBy, ['email', 'phone_or_email'], true) && $email && isset($seenEmails[$email])) {
                $isFileDuplicate = true;
            }

            if ($phone) {
                $seenPhones[$phone] = true;
            }
            if ($email) {
                $seenEmails[$email] = true;
            }

            $chunkRows[] = [
                'row_number' => $dataRowNumber,
                'is_file_duplicate' => $isFileDuplicate,
                'values' => $rawValues,
            ];

            if (count($chunkRows) >= $chunkSize) {
                $chunkNumber++;

                $this->storeChunk(
                    import: $import,
                    directory: $directory,
                    chunkNumber: $chunkNumber,
                    rows: $chunkRows
                );

                $chunkRows = [];
            }
        }

        if ($chunkRows !== []) {
            $chunkNumber++;

            $this->storeChunk(
                import: $import,
                directory: $directory,
                chunkNumber: $chunkNumber,
                rows: $chunkRows
            );
        }

        if ($dataRowNumber === 0 || $chunkNumber === 0) {
            throw new RuntimeException(
                'The CSV file does not contain any data rows.'
            );
        }

        $import->update([
            'total_rows' => $dataRowNumber,
            'total_chunks' => $chunkNumber,
            'status' => 'queued',
        ]);

        $chunkIds = LeadImportChunk::query()
            ->where('lead_import_id', $import->id)
            ->where('status', 'pending')
            ->orderBy('chunk_number')
            ->pluck('id');

        foreach ($chunkIds as $chunkId) {
            ImportLeadChunk::dispatch((int) $chunkId);
        }

        /*
         * The actual ImportLeadChunk jobs will be dispatched
         * in the next step.
         */
    }

    private function storeChunk(
        LeadImport $import,
        string $directory,
        int $chunkNumber,
        array $rows
    ): void {
        $firstRow = (int) $rows[0]['row_number'];
        $lastRow = (int) $rows[array_key_last($rows)]['row_number'];

        $relativePath = sprintf(
            '%s/chunk-%05d.json',
            $directory,
            $chunkNumber
        );

        $written = Storage::disk('local')->put(
            $relativePath,
            json_encode(
                $rows,
                JSON_THROW_ON_ERROR
                    | JSON_UNESCAPED_UNICODE
            )
        );

        if (!$written) {
            throw new RuntimeException(
                "Could not create chunk {$chunkNumber}."
            );
        }

        LeadImportChunk::query()->create([
            'lead_import_id' => $import->id,
            'chunk_number' => $chunkNumber,
            'stored_path' => $relativePath,
            'status' => 'pending',
            'first_row_number' => $firstRow,
            'last_row_number' => $lastRow,
            'total_rows' => count($rows),
        ]);
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function failed(Throwable $exception): void
    {
        LeadImport::query()
            ->whereKey($this->leadImportId)
            ->update([
                'status' => 'failed',
                'failure_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    5000
                ),
                'completed_at' => now(),
            ]);
    }

    private function normalizePhoneForDeduplication(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^[+-]?\d+(?:\.\d+)?E[+-]?\d+$/i', $value)) {
            $value = number_format((float) $value, 0, '', '');
        }

        $value = preg_replace('/\D+/', '', $value) ?? '';

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '880')) {
            $value = substr($value, 3);
        }

        if (strlen($value) === 10 && str_starts_with($value, '1')) {
            $value = '0' . $value;
        }

        if (strlen($value) === 11 && str_starts_with($value, '0')) {
            return $value;
        }

        return null;
    }
}
