<?php

namespace Automas\Lead\Console\Commands;

use Automas\Lead\Models\LeadImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExpiredLeadImportFiles extends Command
{
    protected $signature = 'lead-import:clean-expired-files';

    protected $description = 'Safely clean up temporary lead import CSV files older than 7 days.';

    public function handle(): int
    {
        $expiredImports = LeadImport::query()
            ->where('created_at', '<', now()->subDays(7))
            ->get();

        $count = 0;
        foreach ($expiredImports as $import) {
            $directory = "lead-imports/{$import->uuid}";
            if (Storage::disk('local')->exists($directory)) {
                Storage::disk('local')->deleteDirectory($directory);
                $count++;
            }
        }

        $this->info("Successfully cleaned up {$count} expired lead import directory(ies).");
        return 0;
    }
}
