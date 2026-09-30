<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\BeautySpaManagement\Events\MarkBeautyBookingPaymentPaid;
use Automas\VehicleBookingManagement\Events\UpdateVehicleBookingPaymentStatus;

class UpdateVehicleBookingPaymentStatusListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(UpdateVehicleBookingPaymentStatus $event)
    {
        if(Module_is_active('Account'))
        {
            $bankAccountId = $event->payment->bank_account_id ?? null;
            if($bankAccountId) {
                $this->bankTransactionsService->vehicleBookingPayments($event->booking, $bankAccountId);
                $this->journalService->vehicleBookingPaymentsJournal($event->booking, $bankAccountId);
            }
        }
    }
}
