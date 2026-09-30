<?php

namespace Automas\Account\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Automas\Account\Models\BankAccount;
use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;

class BeautyBookingPaymentListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle($event)
    {
        if (Module_is_active('Account', $event->booking->created_by)) {
            $bankAccount = BankAccount::where('payment_gateway', $event->booking->payment_option)->where('created_by', $event->booking->created_by)->first();
            if ($bankAccount) {
                $this->bankTransactionsService->createBeautyBookingPayment($event->booking, $bankAccount->id);
                $this->journalService->createBeautyBookingPaymentJournal($event->booking, $bankAccount->id);
            }
        }
    }
}
