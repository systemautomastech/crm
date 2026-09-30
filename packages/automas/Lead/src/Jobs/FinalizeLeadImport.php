<?php

namespace Automas\Lead\Jobs;

use Automas\Lead\Models\LeadImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FinalizeLeadImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $leadImportId
    ) {
        $this->onQueue('lead-imports');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                "finalize-lead-import:{$this->leadImportId}"
            ))->expireAfter(350),
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
                ['completed', 'completed_with_errors'],
                true
            )
        ) {
            return;
        }

        if ($import->completed_chunks < $import->total_chunks) {
            return;
        }

        $hasErrorsOrSkippedRows =
            $import->failed_rows > 0 ||
            $import->skipped_rows > 0 ||
            $import->duplicate_rows > 0;

        $import->update([
            'status' => $hasErrorsOrSkippedRows
                ? 'completed_with_errors'
                : 'completed',
            'completed_at' => now(),
        ]);

        /*
         * Source CSV remains available for reports/retry.
         * Processed JSON chunk files are no longer required.
         */
        Storage::disk('local')->deleteDirectory(
            "lead-imports/{$import->uuid}/chunks"
        );
    }
}
