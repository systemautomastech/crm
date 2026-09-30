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
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImportLeadChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public int $leadImportChunkId
    ) {
        $this->onQueue('lead-imports');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                "lead-import-chunk:{$this->leadImportChunkId}"
            ))->expireAfter(650),
        ];
    }

    public function handle(): void
    {
        $chunk = LeadImportChunk::query()
            ->with('import')
            ->findOrFail($this->leadImportChunkId);

        $import = $chunk->import;

        if (!$import) {
            throw new RuntimeException(
                'The parent lead import could not be found.'
            );
        }

        if ($chunk->status === 'completed') {
            return;
        }

        if (in_array($import->status, ['cancelled', 'failed'], true)) {
            return;
        }

        if (!Storage::disk('local')->exists($chunk->stored_path)) {
            throw new RuntimeException(
                "Chunk file {$chunk->chunk_number} could not be found."
            );
        }

        $claimed = LeadImportChunk::query()
            ->whereKey($chunk->id)
            ->whereIn('status', [
                'pending',
                'failed',
                'processing',
            ])
            ->update([
                'status' => 'processing',
                'attempts' => DB::raw('attempts + 1'),
                'started_at' => DB::raw(
                    'COALESCE(started_at, NOW())'
                ),
                'failure_message' => null,
                'completed_at' => null,
            ]);

        if ($claimed !== 1) {
            return;
        }

        try {
            LeadImport::query()
                ->whereKey($import->id)
                ->whereIn('status', ['queued', 'preparing'])
                ->update([
                    'status' => 'processing',
                ]);

            $rows = json_decode(
                Storage::disk('local')->get($chunk->stored_path),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($rows)) {
                throw new RuntimeException(
                    "Chunk {$chunk->chunk_number} contains invalid data."
                );
            }

            $mapping = $import->column_mapping ?? [];
            $options = $import->options ?? [];
            $defaults = $import->default_values ?? [];

            $assignmentRanges = data_get(
                $options,
                'assignment_ranges',
                []
            );

            $duplicateBy = (string) data_get(
                $options,
                'duplicate_by',
                'phone'
            );

            $duplicateStrategy = $import->duplicate_strategy ?: 'skip';
            $sourceId = data_get($options, 'source_id');

            $now = now();

            $processedCount = 0;
            $insertedCount = 0;
            $updatedCount = 0;
            $duplicateCount = 0;
            $skippedCount = 0;
            $skippedUnassignedCount = 0;
            $failedCount = 0;

            $preparedRows = [];
            $failureRows = [];

            foreach ($rows as $chunkRow) {
                if ($processedCount % 200 === 0) {
                    $freshStatus = DB::table('lead_imports')->where('id', $import->id)->value('status');
                    if (in_array($freshStatus, ['cancel_requested', 'cancelled'], true)) {
                        DB::table('lead_import_chunks')->where('id', $chunk->id)->update(['status' => 'cancelled']);
                        return;
                    }
                }

                $processedCount++;

                $rowNumber = (int) ($chunkRow['row_number'] ?? 0);
                $values = is_array($chunkRow['values'] ?? null)
                    ? $chunkRow['values']
                    : [];

                $assignedUserId = $this->resolveAssignedUser(
                    $rowNumber,
                    $options
                );

                if (!$assignedUserId) {
                    $skippedCount++;
                    $skippedUnassignedCount++;

                    $failureRows[] = $this->failureRow(
                        importId: $import->id,
                        chunkId: $chunk->id,
                        rowNumber: $rowNumber,
                        category: 'unassigned_user',
                        values: $values,
                        errors: ['No assigned user was found for this row.'],
                        now: $now
                    );

                    continue;
                }

                $leadData = [
                    'name' => $this->mappedValue($values, $mapping, 'name'),
                    'subject' => $this->mappedValue($values, $mapping, 'subject'),
                    'phone' => $this->normalizePhone(
                        $this->mappedValue($values, $mapping, 'phone')
                    ),
                    'email' => $this->normalizeEmail(
                        $this->mappedValue($values, $mapping, 'email')
                    ),
                    'notes' => $this->nullableValue(
                        $this->mappedValue($values, $mapping, 'notes')
                    ),
                    'date' => $this->normalizeDate(
                        $this->mappedValue($values, $mapping, 'date')
                    ),
                ];

                $errors = $this->validateLeadData($leadData);

                if ($errors !== []) {
                    $failedCount++;

                    $hasMissingField = !$leadData['name'] || !$leadData['subject'] || !$leadData['phone'];

                    $failureRows[] = $this->failureRow(
                        importId: $import->id,
                        chunkId: $chunk->id,
                        rowNumber: $rowNumber,
                        category: $hasMissingField ? 'missing_required_field' : 'validation_error',
                        values: $values,
                        errors: $errors,
                        now: $now
                    );

                    continue;
                }

                if (!empty($chunkRow['is_file_duplicate'])) {
                    $duplicateCount++;
                    $skippedCount++;

                    $failureRows[] = $this->failureRow(
                        importId: $import->id,
                        chunkId: $chunk->id,
                        rowNumber: $rowNumber,
                        category: 'duplicate_in_csv',
                        values: $values,
                        errors: ['This row is a duplicate within the uploaded CSV file.'],
                        now: $now
                    );

                    continue;
                }

                $preparedRows[] = [
                    'row_number' => $rowNumber,
                    'assigned_user_id' => $assignedUserId,
                    'lead' => $leadData,
                ];
            }

            /*
                * Load possible duplicates in a few queries instead
                * of querying once for every CSV row.
                */
            $existingMaps = $this->loadExistingLeadMaps(
                $preparedRows,
                $duplicateBy,
                (int) $import->created_by
            );

            $insertRows = [];
            $insertAssignmentMap = [];
            $updateRows = [];
            $updateAssignmentMap = [];

            $seenPhones = [];
            $seenEmails = [];

            foreach ($preparedRows as $preparedRow) {
                $leadData = $preparedRow['lead'];
                $assignedUserId = (int) $preparedRow['assigned_user_id'];
                $rowNumber = (int) $preparedRow['row_number'];

                $existingLeadId = $this->findDuplicateLeadId(
                    leadData: $leadData,
                    duplicateBy: $duplicateBy,
                    existingMaps: $existingMaps,
                    seenPhones: $seenPhones,
                    seenEmails: $seenEmails
                );

                if ($existingLeadId === -1) {
                    $duplicateCount++;
                    $skippedCount++;

                    $failureRows[] = $this->failureRow(
                        importId: $import->id,
                        chunkId: $chunk->id,
                        rowNumber: $rowNumber,
                        category: 'duplicate_in_csv',
                        values: $leadData,
                        errors: ['This value is duplicated inside the uploaded CSV file.'],
                        now: $now
                    );

                    continue;
                }

                if ($existingLeadId) {
                    $duplicateCount++;

                    if ($duplicateStrategy === 'skip') {
                        $skippedCount++;

                        $failureRows[] = $this->failureRow(
                            importId: $import->id,
                            chunkId: $chunk->id,
                            rowNumber: $rowNumber,
                            category: 'duplicate_in_crm',
                            values: $leadData,
                            errors: ['A duplicate lead already exists in CRM.'],
                            now: $now
                        );

                        continue;
                    }

                    if ($duplicateStrategy === 'update') {
                        $updateRows[$existingLeadId] = [
                            'id' => $existingLeadId,
                            'name' => $leadData['name'],
                            'email' => $leadData['email'],
                            'subject' => $leadData['subject'],
                            'phone' => $leadData['phone'],
                            'notes' => $leadData['notes'],
                            'date' => $leadData['date'],
                            'user_id' => $assignedUserId,
                            'pipeline_id' => (int) $defaults['pipeline_id'],
                            'stage_id' => (int) $defaults['stage_id'],
                            'sources' => $sourceId,
                            'is_active' => (bool) data_get(
                                $defaults,
                                'is_active',
                                true
                            ),
                            'creator_id' => (int) $import->creator_id,
                            'created_by' => (int) $import->created_by,
                            'lead_import_id' => (int) $import->id,
                            'updated_at' => $now,
                        ];

                        $updateAssignmentMap[$existingLeadId] = $assignedUserId;

                        continue;
                    }
                }

                $importKey = (string) Str::uuid();

                $insertRows[] = [
                    'name' => $leadData['name'],
                    'email' => $leadData['email'],
                    'subject' => $leadData['subject'],
                    'user_id' => $assignedUserId,
                    'pipeline_id' => (int) $defaults['pipeline_id'],
                    'stage_id' => (int) $defaults['stage_id'],
                    'sources' => $sourceId,
                    'products' => null,
                    'notes' => $leadData['notes'],
                    'labels' => null,
                    'order' => null,
                    'phone' => $leadData['phone'],
                    'is_active' => (bool) data_get(
                        $defaults,
                        'is_active',
                        true
                    ),
                    'is_converted' => 0,
                    'date' => $leadData['date'],
                    'creator_id' => (int) $import->creator_id,
                    'created_by' => (int) $import->created_by,
                    'lead_import_id' => (int) $import->id,
                    'import_key' => $importKey,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $insertAssignmentMap[$importKey] = $assignedUserId;

                if ($leadData['phone']) {
                    $seenPhones[$leadData['phone']] = true;
                }

                if ($leadData['email']) {
                    $seenEmails[$leadData['email']] = true;
                }
            }

            DB::transaction(function () use (
                $insertRows,
                $insertAssignmentMap,
                $updateRows,
                $updateAssignmentMap,
                $failureRows,
                $import,
                $chunk,
                $processedCount,
                &$insertedCount,
                &$updatedCount,
                $duplicateCount,
                $skippedCount,
                $skippedUnassignedCount,
                $failedCount,
                $now
            ): void {
                /*
             * Insert new leads in smaller database batches.
             */
                foreach (array_chunk($insertRows, 500) as $batch) {
                    DB::table('leads')->insert($batch);
                }

                if ($insertAssignmentMap !== []) {
                    $insertedLeads = DB::table('leads')
                        ->whereIn(
                            'import_key',
                            array_keys($insertAssignmentMap)
                        )
                        ->get([
                            'id',
                            'import_key',
                        ]);

                    $userLeadRows = [];

                    foreach ($insertedLeads as $insertedLead) {
                        $userId = $insertAssignmentMap[$insertedLead->import_key] ?? null;

                        if (!$userId) {
                            continue;
                        }

                        $userLeadRows[] = [
                            'user_id' => (int) $userId,
                            'lead_id' => (int) $insertedLead->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    foreach (array_chunk($userLeadRows, 500) as $batch) {
                        DB::table('user_leads')->insertOrIgnore($batch);
                    }

                    DB::table('leads')
                        ->whereIn(
                            'import_key',
                            array_keys($insertAssignmentMap)
                        )
                        ->update([
                            'import_key' => null,
                        ]);

                    $insertedCount = $insertedLeads->count();
                }

                if ($updateRows !== []) {
                    DB::table('leads')->upsert(
                        array_values($updateRows),
                        ['id'],
                        [
                            'name',
                            'email',
                            'subject',
                            'phone',
                            'notes',
                            'date',
                            'user_id',
                            'pipeline_id',
                            'stage_id',
                            'is_active',
                            'creator_id',
                            'created_by',
                            'updated_at',
                        ]
                    );

                    $updatedLeadIds = array_keys($updateRows);

                    DB::table('user_leads')
                        ->whereIn('lead_id', $updatedLeadIds)
                        ->delete();

                    $updatedUserLeadRows = [];

                    foreach ($updateAssignmentMap as $leadId => $userId) {
                        $updatedUserLeadRows[] = [
                            'user_id' => (int) $userId,
                            'lead_id' => (int) $leadId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    foreach (array_chunk($updatedUserLeadRows, 500) as $batch) {
                        DB::table('user_leads')->insertOrIgnore($batch);
                    }

                    $updatedCount = count($updateRows);
                }

                foreach (array_chunk($failureRows, 500) as $batch) {
                    DB::table('lead_import_failures')->insert($batch);
                }

                LeadImportChunk::query()
                    ->whereKey($chunk->id)
                    ->update([
                        'status' => 'completed',
                        'processed_rows' => $processedCount,
                        'inserted_rows' => $insertedCount,
                        'updated_rows' => $updatedCount,
                        'duplicate_rows' => $duplicateCount,
                        'skipped_rows' => $skippedCount,
                        'failed_rows' => $failedCount,
                        'completed_at' => $now,
                        'failure_message' => null,
                    ]);

                DB::table('lead_imports')
                    ->where('id', $import->id)
                    ->update([
                        'completed_chunks' => DB::raw(
                            'completed_chunks + 1'
                        ),
                        'processed_rows' => DB::raw(
                            'processed_rows + ' . (int) $processedCount
                        ),
                        'inserted_rows' => DB::raw(
                            'inserted_rows + ' . (int) $insertedCount
                        ),
                        'updated_rows' => DB::raw(
                            'updated_rows + ' . (int) $updatedCount
                        ),
                        'duplicate_rows' => DB::raw(
                            'duplicate_rows + ' . (int) $duplicateCount
                        ),
                        'skipped_rows' => DB::raw(
                            'skipped_rows + ' . (int) $skippedCount
                        ),
                        'skipped_unassigned_rows' => DB::raw(
                            'skipped_unassigned_rows + '
                                . (int) $skippedUnassignedCount
                        ),
                        'failed_rows' => DB::raw(
                            'failed_rows + ' . (int) $failedCount
                        ),
                        'updated_at' => $now,
                    ]);
            });

            $freshImport = LeadImport::query()->find($import->id);

            if (
                $freshImport &&
                $freshImport->completed_chunks >= $freshImport->total_chunks
            ) {
                FinalizeLeadImport::dispatch($freshImport->id);
            }
        } catch (Throwable $exception) {
            LeadImportChunk::query()
                ->whereKey($chunk->id)
                ->update([
                    'status' => 'failed',
                    'failure_message' => mb_substr(
                        $exception->getMessage(),
                        0,
                        5000
                    ),
                    'completed_at' => null,
                ]);

            throw $exception;
        }
    }

    private function mappedValue(
        array $values,
        array $mapping,
        string $field
    ): ?string {
        if (!array_key_exists($field, $mapping)) {
            return null;
        }

        $columnIndex = (int) $mapping[$field];

        if (!array_key_exists($columnIndex, $values)) {
            return null;
        }

        $value = trim((string) $values[$columnIndex]);

        return $value !== '' ? $value : null;
    }

    private function validateLeadData(array $leadData): array
    {
        $errors = [];

        if (!$leadData['name']) {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($leadData['name']) > 255) {
            $errors[] = 'Name may not be longer than 255 characters.';
        }

        if (!$leadData['subject']) {
            $errors[] = 'Subject is required.';
        } elseif (mb_strlen($leadData['subject']) > 255) {
            $errors[] = 'Subject may not be longer than 255 characters.';
        }

        if (!$leadData['phone']) {
            $errors[] = 'Phone is required.';
        } elseif (mb_strlen($leadData['phone']) > 20) {
            $errors[] = 'Phone may not be longer than 20 characters.';
        }

        if (
            $leadData['email'] &&
            !filter_var($leadData['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $errors[] = 'Email is invalid.';
        }

        if (
            $leadData['email'] &&
            mb_strlen($leadData['email']) > 255
        ) {
            $errors[] = 'Email may not be longer than 255 characters.';
        }

        return $errors;
    }

    private function normalizePhone(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        // Handle Excel scientific notation
        if (preg_match('/^[+-]?\d+(?:\.\d+)?E[+-]?\d+$/i', $value)) {
            $value = number_format((float) $value, 0, '', '');
        }

        // Keep digits only
        $value = preg_replace('/\D+/', '', $value) ?? '';

        if ($value === '') {
            return null;
        }

        // +88017xxxxxxxx / 88017xxxxxxxx
        if (str_starts_with($value, '880')) {
            $value = substr($value, 3);
        }

        // Excel removed leading zero
        // 1765656777 -> 01765656777
        if (strlen($value) === 10 && str_starts_with($value, '1')) {
            $value = '0' . $value;
        }

        // Already correct
        if (strlen($value) === 11 && str_starts_with($value, '0')) {
            return $value;
        }

        // Invalid number
        return null;
    }

    private function normalizeEmail(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return mb_strtolower(trim($value));
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return now()
                ->parse($value)
                ->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function nullableValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function resolveAssignedUser(
        int $rowNumber,
        array $options
    ): ?int {
        $method = (string) data_get($options, 'assignment_method', 'ranges');

        if ($method === 'round_robin') {
            $selectedUsers = (array) data_get($options, 'selected_user_ids', []);
            if (!empty($selectedUsers)) {
                $idx = ($rowNumber - 1) % count($selectedUsers);
                return (int) $selectedUsers[$idx];
            }
            return null;
        }

        $ranges = (array) data_get($options, 'assignment_ranges', []);
        foreach ($ranges as $range) {
            $from = (int) ($range['from_row'] ?? 0);
            $to = (int) ($range['to_row'] ?? 0);
            $userId = (int) ($range['user_id'] ?? 0);

            if (
                $userId > 0 &&
                $rowNumber >= $from &&
                $rowNumber <= $to
            ) {
                return $userId;
            }
        }

        return null;
    }

    private function loadExistingLeadMaps(
        array $preparedRows,
        string $duplicateBy,
        int $companyCreatorId
    ): array {
        if ($duplicateBy === 'none') {
            return [
                'phones' => [],
                'emails' => [],
            ];
        }

        $phones = collect($preparedRows)
            ->pluck('lead.phone')
            ->filter()
            ->unique()
            ->values();

        $emails = collect($preparedRows)
            ->pluck('lead.email')
            ->filter()
            ->unique()
            ->values();

        $phoneMap = [];
        $emailMap = [];

        if (
            in_array($duplicateBy, ['phone', 'phone_or_email'], true) &&
            $phones->isNotEmpty()
        ) {
            $phoneMap = DB::table('leads')
                ->where('created_by', $companyCreatorId)
                ->whereIn('phone', $phones)
                ->whereNotNull('phone')
                ->pluck('id', 'phone')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        if (
            in_array($duplicateBy, ['email', 'phone_or_email'], true) &&
            $emails->isNotEmpty()
        ) {
            $emailMap = DB::table('leads')
                ->where('created_by', $companyCreatorId)
                ->whereIn('email', $emails)
                ->whereNotNull('email')
                ->pluck('id', 'email')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        return [
            'phones' => $phoneMap,
            'emails' => $emailMap,
        ];
    }

    private function findDuplicateLeadId(
        array $leadData,
        string $duplicateBy,
        array $existingMaps,
        array &$seenPhones,
        array &$seenEmails
    ): ?int {
        if ($duplicateBy === 'none') {
            return null;
        }

        $phone = $leadData['phone'];
        $email = $leadData['email'];

        if (
            in_array($duplicateBy, ['phone', 'phone_or_email'], true) &&
            $phone
        ) {
            if (isset($existingMaps['phones'][$phone])) {
                return (int) $existingMaps['phones'][$phone];
            }

            /*
             * Duplicate inside the current chunk.
             * Returning -1 lets skip logic treat it as duplicate.
             */
            if (isset($seenPhones[$phone])) {
                return -1;
            }
        }

        if (
            in_array($duplicateBy, ['email', 'phone_or_email'], true) &&
            $email
        ) {
            if (isset($existingMaps['emails'][$email])) {
                return (int) $existingMaps['emails'][$email];
            }

            if (isset($seenEmails[$email])) {
                return -1;
            }
        }

        return null;
    }

    private function failureRow(
        int $importId,
        int $chunkId,
        int $rowNumber,
        string $category,
        array $values,
        array $errors,
        mixed $now
    ): array {
        return [
            'lead_import_id' => $importId,
            'lead_import_chunk_id' => $chunkId,
            'row_number' => $rowNumber,
            'category' => $category,
            'row_data' => json_encode(
                $values,
                JSON_UNESCAPED_UNICODE
            ),
            'errors' => json_encode(
                $errors,
                JSON_UNESCAPED_UNICODE
            ),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    public function failed(Throwable $exception): void
    {
        LeadImportChunk::query()
            ->whereKey($this->leadImportChunkId)
            ->update([
                'status' => 'failed',
                'failure_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    5000
                ),
                'completed_at' => now(),
            ]);

        $chunk = LeadImportChunk::query()->find(
            $this->leadImportChunkId
        );

        if ($chunk) {
            LeadImport::query()
                ->whereKey($chunk->lead_import_id)
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
    }
}
