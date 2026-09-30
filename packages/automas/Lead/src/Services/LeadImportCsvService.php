<?php

namespace Automas\Lead\Services;

use Automas\Lead\Models\LeadImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use SplFileObject;

class LeadImportCsvService
{
    private const PREVIEW_LIMIT = 20;

    private const DELIMITERS = [
        ',',
        ';',
        "\t",
        '|',
    ];

    public function preview(string $absolutePath): array
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new RuntimeException(
                __('The uploaded CSV file could not be read.')
            );
        }

        $delimiter = $this->detectDelimiter($absolutePath);

        $file = new SplFileObject($absolutePath, 'r');

        $file->setFlags(
            SplFileObject::READ_CSV
                | SplFileObject::SKIP_EMPTY
                | SplFileObject::DROP_NEW_LINE
        );

        $file->setCsvControl(
            separator: $delimiter,
            enclosure: '"',
            escape: '\\'
        );

        $rawHeader = $file->fgetcsv();

        if ($rawHeader === false || $this->isEmptyRow($rawHeader)) {
            throw new RuntimeException(
                __('The CSV file does not contain any readable rows.')
            );
        }

        $header = $this->normalizeHeader($rawHeader);

        if (count($header) < 2) {
            throw new RuntimeException(
                __('The CSV file must contain at least two columns.')
            );
        }

        $rows = [];
        $rowNumber = 1;

        while (!$file->eof() && count($rows) < self::PREVIEW_LIMIT) {
            $row = $file->fgetcsv();
            $rowNumber++;

            if ($row === false || $this->isEmptyRow($row)) {
                continue;
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'values' => $this->normalizeRow(
                    $row,
                    count($header)
                ),
            ];
        }

        if ($rows === []) {
            throw new RuntimeException(
                __('The CSV file contains a header but no data rows.')
            );
        }

        return [
            'delimiter' => $this->delimiterName($delimiter),
            'delimiter_character' => $delimiter,
            'headers' => $header,
            'rows' => $rows,
            'suggested_mapping' => $this->suggestMapping($header),
            'required_fields' => [
                'name',
                'subject',
                'phone',
            ],
        ];
    }

    private function detectDelimiter(string $absolutePath): string
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                __('The CSV file could not be opened.')
            );
        }

        $sampleLines = [];

        while (
            !feof($handle)
            && count($sampleLines) < 5
        ) {
            $line = fgets($handle);

            if ($line === false) {
                break;
            }

            if (trim($line) !== '') {
                $sampleLines[] = $line;
            }
        }

        fclose($handle);

        if ($sampleLines === []) {
            throw new RuntimeException(
                __('The CSV file is empty.')
            );
        }

        $bestDelimiter = ',';
        $bestScore = -1;

        foreach (self::DELIMITERS as $delimiter) {
            $columnCounts = [];

            foreach ($sampleLines as $line) {
                $columns = str_getcsv(
                    $line,
                    $delimiter,
                    '"',
                    '\\'
                );

                $columnCounts[] = count($columns);
            }

            $minimumColumns = min($columnCounts);
            $maximumColumns = max($columnCounts);

            $consistent = $minimumColumns === $maximumColumns;
            $score = $consistent
                ? $minimumColumns * 10
                : $minimumColumns;

            if ($minimumColumns > 1 && $score > $bestScore) {
                $bestScore = $score;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    public function countDataRows(
        string $absolutePath,
        string $delimiter = ',',
        bool $hasHeader = true
    ): int {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new RuntimeException(
                __('The uploaded CSV file could not be read.')
            );
        }

        $file = new SplFileObject($absolutePath, 'r');

        $file->setFlags(
            SplFileObject::READ_CSV
                | SplFileObject::SKIP_EMPTY
                | SplFileObject::DROP_NEW_LINE
        );

        $file->setCsvControl(
            separator: $delimiter,
            enclosure: '"',
            escape: '\\'
        );

        $count = 0;
        $firstReadableRowFound = false;

        while (!$file->eof()) {
            $row = $file->fgetcsv();

            if (
                $row === false ||
                $this->isEmptyRow($row)
            ) {
                continue;
            }

            if (!$firstReadableRowFound) {
                $firstReadableRowFound = true;

                if ($hasHeader) {
                    continue;
                }
            }

            $count++;
        }

        return $count;
    }

    private function normalizeHeader(array $header): array
    {
        $used = [];

        return array_map(
            function (mixed $value, int $index) use (&$used): array {
                $original = $this->cleanValue($value);

                if ($index === 0) {
                    $original = $this->removeBom($original);
                }

                $label = $original !== ''
                    ? $original
                    : __('Column :column', [
                        'column' => $index + 1,
                    ]);

                $key = $this->makeUniqueKey(
                    $label,
                    $index,
                    $used
                );

                $used[] = $key;

                return [
                    'index' => $index,
                    'key' => $key,
                    'label' => $label,
                ];
            },
            $header,
            array_keys($header)
        );
    }

    private function normalizeRow(
        array $row,
        int $columnCount
    ): array {
        $normalized = [];

        for ($index = 0; $index < $columnCount; $index++) {
            $normalized[] = $this->cleanValue(
                $row[$index] ?? ''
            );
        }

        return $normalized;
    }

    private function cleanValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);

        return mb_convert_encoding(
            $value,
            'UTF-8',
            'UTF-8, ISO-8859-1, Windows-1252'
        );
    }

    private function removeBom(string $value): string
    {
        return preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $value
        ) ?? $value;
    }

    private function makeUniqueKey(
        string $label,
        int $index,
        array $used
    ): string {
        $key = mb_strtolower($label);
        $key = preg_replace('/[^a-z0-9]+/i', '_', $key);
        $key = trim((string) $key, '_');

        if ($key === '') {
            $key = 'column_' . ($index + 1);
        }

        $originalKey = $key;
        $suffix = 2;

        while (in_array($key, $used, true)) {
            $key = $originalKey . '_' . $suffix;
            $suffix++;
        }

        return $key;
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

    private function suggestMapping(array $headers): array
    {
        $aliases = [
            'name' => [
                'name',
                'lead_name',
                'customer_name',
                'client_name',
                'contact_name',
                'full_name',
                'person_name',
            ],
            'subject' => [
                'subject',
                'lead_subject',
                'title',
                'lead_title',
                'inquiry',
                'interest',
            ],
            'phone' => [
                'phone',
                'phone_number',
                'mobile',
                'mobile_number',
                'contact_number',
                'telephone',
                'tel',
            ],
            'email' => [
                'email',
                'email_address',
                'mail',
            ],
            'notes' => [
                'notes',
                'note',
                'description',
                'remarks',
                'comment',
            ],
            'date' => [
                'date',
                'follow_up',
                'follow_up_date',
                'next_follow_up',
            ],
        ];

        $mapping = [];

        foreach ($headers as $header) {
            $normalized = $header['key'];

            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($normalized, $fieldAliases, true)) {
                    if (!isset($mapping[$field])) {
                        $mapping[$field] = $header['index'];
                    }

                    break;
                }
            }
        }

        return $mapping;
    }

    private function delimiterName(string $delimiter): string
    {
        return match ($delimiter) {
            ',' => 'comma',
            ';' => 'semicolon',
            "\t" => 'tab',
            '|' => 'pipe',
            default => 'unknown',
        };
    }

    public function validateImport(
        LeadImport $leadImport,
        array $mapping,
        array $options
    ): array {
        $absolutePath = Storage::disk('local')->path($leadImport->stored_path);

        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new RuntimeException(__('The source CSV file could not be found.'));
        }

        $delimiter = (string) data_get($options, 'delimiter', ',');
        $hasHeader = (bool) data_get($options, 'has_header', true);
        $duplicateBy = (string) data_get($options, 'duplicate_by', 'phone');
        $duplicateStrategy = $leadImport->duplicate_strategy ?: 'skip';
        $assignmentMethod = (string) data_get($options, 'assignment_method', 'ranges');
        $assignmentRanges = (array) data_get($options, 'assignment_ranges', []);
        $selectedUserIds = (array) data_get($options, 'selected_user_ids', []);

        $file = new SplFileObject($absolutePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(separator: $delimiter, enclosure: '"', escape: '\\');

        $headerSkipped = !$hasHeader;
        $totalRows = 0;
        $missingRequired = 0;
        $fileDuplicates = 0;
        $crmDuplicates = 0;
        $unassignedRows = 0;
        $skippedStrategyRows = 0;
        $readyToImport = 0;

        $seenPhones = [];
        $seenEmails = [];

        $companyCreatorId = (int) $leadImport->created_by;

        $crmPhones = [];
        $crmEmails = [];

        if (in_array($duplicateBy, ['phone', 'phone_or_email'], true)) {
            $crmPhones = DB::table('leads')
                ->where('created_by', $companyCreatorId)
                ->whereNotNull('phone')
                ->pluck('phone')
                ->flip()
                ->all();
        }

        if (in_array($duplicateBy, ['email', 'phone_or_email'], true)) {
            $crmEmails = DB::table('leads')
                ->where('created_by', $companyCreatorId)
                ->whereNotNull('email')
                ->pluck('email')
                ->map(fn($e) => mb_strtolower(trim((string) $e)))
                ->flip()
                ->all();
        }

        while (!$file->eof()) {
            $row = $file->fgetcsv();

            if ($row === false || $this->isEmptyRow($row)) {
                continue;
            }

            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $totalRows++;

            $name = $this->mappedValueFromRow($row, $mapping, 'name');
            $subject = $this->mappedValueFromRow($row, $mapping, 'subject');
            $rawPhone = $this->mappedValueFromRow($row, $mapping, 'phone');
            $rawEmail = $this->mappedValueFromRow($row, $mapping, 'email');

            $phone = $this->normalizePhoneValue($rawPhone);
            $email = $this->normalizeEmailValue($rawEmail);

            if (!$name || !$subject || !$phone) {
                $missingRequired++;
                continue;
            }

            $assignedUserId = null;
            if ($assignmentMethod === 'round_robin') {
                if (!empty($selectedUserIds)) {
                    $assignedUserId = (int) $selectedUserIds[($totalRows - 1) % count($selectedUserIds)];
                }
            } else {
                foreach ($assignmentRanges as $range) {
                    $from = (int) ($range['from_row'] ?? 0);
                    $to = (int) ($range['to_row'] ?? 0);
                    $uId = (int) ($range['user_id'] ?? 0);

                    if ($uId > 0 && $totalRows >= $from && $totalRows <= $to) {
                        $assignedUserId = $uId;
                        break;
                    }
                }
            }

            if (!$assignedUserId) {
                $unassignedRows++;
                continue;
            }

            $isFileDuplicate = false;
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

            if ($isFileDuplicate) {
                $fileDuplicates++;
                if ($duplicateStrategy === 'skip') {
                    $skippedStrategyRows++;
                }
                continue;
            }

            $isCrmDuplicate = false;
            if (in_array($duplicateBy, ['phone', 'phone_or_email'], true) && $phone && isset($crmPhones[$phone])) {
                $isCrmDuplicate = true;
            }
            if (in_array($duplicateBy, ['email', 'phone_or_email'], true) && $email && isset($crmEmails[$email])) {
                $isCrmDuplicate = true;
            }

            if ($isCrmDuplicate) {
                $crmDuplicates++;
                if ($duplicateStrategy === 'skip') {
                    $skippedStrategyRows++;
                } else {
                    $readyToImport++;
                }
                continue;
            }

            $readyToImport++;
        }

        return [
            'total_rows' => $totalRows,
            'ready_to_import' => $readyToImport,
            'duplicates_in_file' => $fileDuplicates,
            'existing_crm_duplicates' => $crmDuplicates,
            'missing_required_fields' => $missingRequired,
            'unassigned_rows' => $unassignedRows,
            'skipped_by_strategy' => $skippedStrategyRows,
        ];
    }

    private function mappedValueFromRow(array $row, array $mapping, string $field): ?string
    {
        if (!array_key_exists($field, $mapping)) {
            return null;
        }

        $columnIndex = (int) $mapping[$field];

        if (!array_key_exists($columnIndex, $row)) {
            return null;
        }

        $val = trim((string) $row[$columnIndex]);
        return $val !== '' ? $val : null;
    }

    private function normalizePhoneValue(?string $value): ?string
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

    private function normalizeEmailValue(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return mb_strtolower(trim($value));
    }
}
