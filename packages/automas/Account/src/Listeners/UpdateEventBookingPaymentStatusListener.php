<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\EventsManagement\Events\UpdateEventBookingPaymentStatus;

class UpdateEventBookingPaymentStatusListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(UpdateEventBookingPaymentStatus $event)
    {
        if(Module_is_active('Account'))
        {
            $bankAccountId = $event->payment->bank_account_id ?? null;
            if($bankAccountId) {
                $this->bankTransactionsService->eventBookingPaymentStatus($event->payment, $bankAccountId);
                $this->journalService->eventBookingPaymentStatusJournal($event->payment, $bankAccountId);
            }
        }
    }
}
