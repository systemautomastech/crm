<?php

namespace Automas\Account\Listeners;

use Automas\Taskly\Events\PostProjectPayment;
use Automas\Account\Services\JournalService;
use Automas\Account\Services\BankTransactionsService;

class PostProjectPaymentListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(PostProjectPayment $event)
    {
        if(Module_is_active('Account'))
        {
            $this->journalService->createProjectPaymentJournal($event->projectPayment);
            $this->bankTransactionsService->createProjectPayment($event->projectPayment);
        }
    }
}
