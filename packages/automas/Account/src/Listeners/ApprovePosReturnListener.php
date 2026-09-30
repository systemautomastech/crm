<?php

namespace Automas\Account\Listeners;

use Automas\Account\Models\BankAccount;
use Automas\Pos\Events\ApprovePosReturn;
use Automas\Account\Services\JournalService;
use Automas\Account\Services\BankTransactionsService;

class ApprovePosReturnListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(ApprovePosReturn $event)
    {
        if (Module_is_active('Account')) {
            $posReturn = $event->return;

            // Get bank account from original POS sale
            $bankAccount = BankAccount::where('id', $posReturn->originalPos->bank_account_id)
                ->where('created_by', creatorId())
                ->first();

            if ($bankAccount) {
                $this->bankTransactionsService->approvePosReturnPayment($posReturn, $bankAccount->id);
            }

            $this->journalService->approvePosReturnJournal($posReturn);
            $this->journalService->approvePosReturnCOGSJournal($posReturn);
        }
    }
}
