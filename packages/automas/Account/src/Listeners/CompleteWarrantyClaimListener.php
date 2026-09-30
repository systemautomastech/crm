<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\Warranty\Events\CompleteWarrantyClaim;

class CompleteWarrantyClaimListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(CompleteWarrantyClaim $event)
    {
        if (Module_is_active('Account')) {
            $this->bankTransactionsService->completeWarrantyClaim($event->claim);
            $this->journalService->completeWarrantyClaimJournal($event->claim);
        }
    }
}
