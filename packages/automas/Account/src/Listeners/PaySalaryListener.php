<?php

namespace Automas\Account\Listeners;

use Automas\Account\Models\BankAccount;
use Automas\Hrm\Events\PaySalary;
use Automas\Account\Services\JournalService;
use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Models\ChartOfAccount;

class PaySalaryListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(PaySalary $event)
    {
        if (Module_is_active('Account'))
        {
            $this->journalService->createPayrollJournal($event->payrollEntry);
            $this->bankTransactionsService->createPayrollPayment($event->payrollEntry);
        }
    }
}

