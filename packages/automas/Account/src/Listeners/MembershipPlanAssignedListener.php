<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\GymManagement\Events\MembershipPlanAssigned;

class MembershipPlanAssignedListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(MembershipPlanAssigned $event)
    {
        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->membershipPlanAssigned($event->payment);
            $this->journalService->membershipPlanAssignedJournal($event->payment);
        }
    }
}
