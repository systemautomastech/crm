<?php

namespace Automas\Account\Services;

use Automas\Account\Models\BankTransaction;
use Illuminate\Support\Facades\Auth;
use Automas\Account\Models\BankAccount;

class BankTransactionsService
{
    public function createVendorPayment($vendorPayment)
    {
        if (!$vendorPayment->bank_account_id) {
            throw new \Exception("Bank account is required for vendor payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $vendorPayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $vendorPayment->payment_amount : -$vendorPayment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $vendorPayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $vendorPayment->payment_number;
        $bankTransaction->description = 'Vendor Payment #' . $vendorPayment->payment_number . ' - ' . $vendorPayment->vendor->name;
        $bankTransaction->amount = $vendorPayment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

         // Update bank account balance
        $this->updateBankBalance($vendorPayment->bank_account_id, -$vendorPayment->payment_amount);
    }

    public function createCustomerPayment($customerPayment)
    {
        if (!$customerPayment->bank_account_id) {
            throw new \Exception("Bank account is required for customer payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $customerPayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $customerPayment->payment_amount : $customerPayment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $customerPayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $customerPayment->payment_number;
        $bankTransaction->description = 'Customer Payment #' . $customerPayment->payment_number . ' - ' . $customerPayment->customer->name;
        $bankTransaction->amount = $customerPayment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($customerPayment->bank_account_id, $customerPayment->payment_amount);
    }

    public function createTransferBankTransactions($transfer)
    {
        // Get running balance for source account
        $fromLastTransaction = BankTransaction::where('bank_account_id', $transfer->from_account_id)->orderBy('id', 'desc')->first();
        $fromRunningBalance = $fromLastTransaction ? $fromLastTransaction->running_balance - $transfer->transfer_amount : -$transfer->transfer_amount;

        // Debit transaction from source account
        $debitTransaction = new BankTransaction();
        $debitTransaction->bank_account_id = $transfer->from_account_id;
        $debitTransaction->transaction_date = now();
        $debitTransaction->transaction_type = 'debit';
        $debitTransaction->reference_number = $transfer->transfer_number;
        $debitTransaction->description = 'Transfer to ' . $transfer->toAccount->account_name;
        $debitTransaction->amount = $transfer->transfer_amount;
        $debitTransaction->running_balance = $fromRunningBalance;
        $debitTransaction->transaction_status = 'cleared';
        $debitTransaction->reconciliation_status = 'unreconciled';
        $debitTransaction->created_by = creatorId();
        $debitTransaction->save();

        // Get running balance for destination account
        $toLastTransaction = BankTransaction::where('bank_account_id', $transfer->to_account_id)->orderBy('id', 'desc')->first();
        $toRunningBalance = $toLastTransaction ? $toLastTransaction->running_balance + $transfer->transfer_amount : $transfer->transfer_amount;

        // Credit transaction to destination account
        $creditTransaction = new BankTransaction();
        $creditTransaction->bank_account_id = $transfer->to_account_id;
        $creditTransaction->transaction_date = now();
        $creditTransaction->transaction_type = 'credit';
        $creditTransaction->reference_number = $transfer->transfer_number;
        $creditTransaction->description = 'Transfer from ' . $transfer->fromAccount->account_name;
        $creditTransaction->amount = $transfer->transfer_amount;
        $creditTransaction->running_balance = $toRunningBalance;
        $creditTransaction->transaction_status = 'cleared';
        $creditTransaction->reconciliation_status = 'unreconciled';
        $creditTransaction->created_by = creatorId();
        $creditTransaction->save();

        // Additional debit for transfer charges (if any)
        if ($transfer->transfer_charges > 0) {
            $chargesRunningBalance = $fromRunningBalance - $transfer->transfer_charges;

            $chargesTransaction = new BankTransaction();
            $chargesTransaction->bank_account_id = $transfer->from_account_id;
            $chargesTransaction->transaction_date = now();
            $chargesTransaction->transaction_type = 'debit';
            $chargesTransaction->reference_number = $transfer->transfer_number . '-CHARGES';
            $chargesTransaction->description = 'Transfer charges for ' . $transfer->transfer_number;
            $chargesTransaction->amount = $transfer->transfer_charges;
            $chargesTransaction->running_balance = $chargesRunningBalance;
            $chargesTransaction->transaction_status = 'cleared';
            $chargesTransaction->reconciliation_status = 'unreconciled';
            $chargesTransaction->created_by = creatorId();
            $chargesTransaction->save();
        }
    }

    public function updateBankBalance($bankAccountId, $amount) {
        $bankAccount = BankAccount::find($bankAccountId);
        $bankAccount->current_balance += $amount;
        $bankAccount->save();

        // Update running balance for latest transaction
        $latestTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
                            ->latest()
                            ->first();

        if ($latestTransaction) {
            $latestTransaction->running_balance = $bankAccount->current_balance;
            $latestTransaction->save();
        }
    }
    public function createRetainerPayment($retainerPayment)
    {
        if (!$retainerPayment->bank_account_id) {
            throw new \Exception("Bank account is required for retainer payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $retainerPayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $retainerPayment->payment_amount : $retainerPayment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $retainerPayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $retainerPayment->payment_number;
        $bankTransaction->description = 'Retainer Payment #' . $retainerPayment->payment_number . ' - ' . $retainerPayment->customer->name;
        $bankTransaction->amount = $retainerPayment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($retainerPayment->bank_account_id, $retainerPayment->payment_amount);
    }

    public function createRevenuePayment($revenue)
    {
        if (!$revenue->bank_account_id) {
            throw new \Exception("Bank account is required for revenue payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $revenue->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $revenue->amount : $revenue->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $revenue->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $revenue->revenue_number;
        $bankTransaction->description = 'Revenue Posted: ' . ($revenue->description ?? 'Revenue transaction');
        $bankTransaction->amount = $revenue->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($revenue->bank_account_id, $revenue->amount);
    }

    public function createExpensePayment($expense)
    {
        if (!$expense->bank_account_id) {
            throw new \Exception("Bank account is required for expense payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $expense->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $expense->amount : -$expense->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $expense->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $expense->expense_number;
        $bankTransaction->description = 'Expense Posted: ' . ($expense->description ?? 'Expense transaction');
        $bankTransaction->amount = $expense->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance (negative amount to decrease balance)
        $this->updateBankBalance($expense->bank_account_id, -$expense->amount);
    }
    public function createCommissionPayment($commissionPayment)
    {
        if (!$commissionPayment->bank_account_id) {
            throw new \Exception("Bank account is required for commission payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $commissionPayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $commissionPayment->payment_amount : -$commissionPayment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $commissionPayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $commissionPayment->payment_number;
        $bankTransaction->description = 'Commission Payment #' . $commissionPayment->payment_number . ' - ' . $commissionPayment->agent->name;
        $bankTransaction->amount = $commissionPayment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance (negative amount to decrease balance)
        $this->updateBankBalance($commissionPayment->bank_account_id, -$commissionPayment->payment_amount);
    }

    public function createPayrollPayment($payrollEntry)
    {
        $bankAccountId = $payrollEntry->payroll->bank_account_id;

        if (!$bankAccountId) {
            throw new \Exception("Bank account is required for payroll payment");
        }
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $payrollEntry->net_pay : -$payrollEntry->net_pay;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = 'PAYROLL-' . $payrollEntry->id;
        $bankTransaction->description = 'Salary Payment - ' . $payrollEntry->employee->user->name;
        $bankTransaction->amount = $payrollEntry->net_pay;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, -$payrollEntry->net_pay);
    }

    public function createPosPayment($posSale, $bankAccountId)
    {
        $posSale->load('payment');
        $amount = $posSale->payment->discount_amount ?? 0;

        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $amount : $amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $posSale->sale_number;
        $bankTransaction->description = 'POS Sale ' . $posSale->sale_number;
        $bankTransaction->amount = $amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, $amount);
    }

    public function approvePosReturnPayment($posReturn, $bankAccountId)
    {
        $amount = $posReturn->total_amount ?? 0;

        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $amount : -$amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $posReturn->return_number;
        $bankTransaction->description = 'POS Return ' . $posReturn->return_number;
        $bankTransaction->amount = $amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, -$amount);
    }

    public function createMobileServicePayment($payment)
    {
        if (!$payment->bank_account_id) {
            throw new \Exception("Bank account is required for mobile service payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $payment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
         $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $payment->payment_amount : $payment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $payment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $payment->payment_number;
        $bankTransaction->description = 'Mobile Service Payment: ' . ($payment->description ?? 'Mobile service transaction');
        $bankTransaction->amount = $payment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($payment->bank_account_id, $payment->payment_amount);
    }

    public function createMarkFleetBookingPayment($payment)
    {
        if (!$payment->bank_account_id) {
            throw new \Exception("Bank account is required for fleet booking payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $payment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $payment->payment_amount : $payment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $payment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $payment->payment_number;
        $bankTransaction->description = 'Fleet Booking Payment: ' . ($payment->description ?? 'Fleet booking transaction');
        $bankTransaction->amount = $payment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($payment->bank_account_id, $payment->payment_amount);
    }

    public function createBeautyBookingPayment($booking, $bankAccountId)
    {
        // Find bank account by payment gateway
        $bankAccount = BankAccount::where('id', $bankAccountId)->where('created_by', $booking->created_by)->first();

        if (!$bankAccount) {
            throw new \Exception('Bank account not found for payment gateway: ' . $booking->payment_option);
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccount->id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $booking->price : $booking->price;
        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccount->id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $booking->payment_number ?? 'BEAUTY-' . $booking->id;
        $bankTransaction->description = 'Beauty Booking Payment via ' . $booking->payment_option;
        $bankTransaction->amount = $booking->price;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = $booking->created_by;
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($bankAccount->id, $booking->price);
    }

    public function createDairyCattlePayment($dairyCattlePayment)
    {
        if (!$dairyCattlePayment->bank_account_id) {
            throw new \Exception("Bank account is required for dairy cattle payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $dairyCattlePayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $dairyCattlePayment->payment_amount : $dairyCattlePayment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $dairyCattlePayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $dairyCattlePayment->payment_number;
        $bankTransaction->description = 'Dairy Cattle Payment: ' . ($dairyCattlePayment->description ?? 'Dairy cattle transaction');
        $bankTransaction->amount = $dairyCattlePayment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($dairyCattlePayment->bank_account_id, $dairyCattlePayment->payment_amount);
    }

    public function dairyCattleExpenseTracking($expenseTracking)
    {
        if (!$expenseTracking->bank_account_id) {
            throw new \Exception("Bank account is required for dairy cattle expense tracking");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $expenseTracking->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $expenseTracking->amount : -$expenseTracking->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $expenseTracking->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = 'DCE-' . $expenseTracking->id;
        $bankTransaction->description = 'Dairy Cattle Expense: ' . ($expenseTracking->description ?? 'Expense tracking');
        $bankTransaction->amount = $expenseTracking->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($expenseTracking->bank_account_id, -$expenseTracking->amount);
    }


    public function createCateringOrderPayment($payment)
    {
        if (!$payment->bank_account_id) {
            throw new \Exception("Bank account is required for catering order payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $payment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $payment->amount : $payment->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $payment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $payment->reference_number;
        $bankTransaction->description = 'Catering Order Payment #' . $payment->id;
        $bankTransaction->amount = $payment->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($payment->bank_account_id, $payment->amount);
    }

    public function cateringExpenseTracking($expenseTracking)
    {
        if (!$expenseTracking->bank_account_id) {
            throw new \Exception("Bank account is required for catering expense tracking");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $expenseTracking->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $expenseTracking->amount : -$expenseTracking->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $expenseTracking->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $expenseTracking->reference_number ?? 'CET-' . $expenseTracking->id;
        $bankTransaction->description = 'Catering Expense: ' . ($expenseTracking->description ?? 'Catering expense tracking');
        $bankTransaction->amount = $expenseTracking->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($expenseTracking->bank_account_id, -$expenseTracking->amount);
    }


    public function createUpdateSalesAgentCommissionPayment($payment)
    {
        if (!$payment->bank_account_id) {
            throw new \Exception("Bank account is required for sales agent commission payment");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $payment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $payment->payment_amount : -$payment->payment_amount;

        $agentName = $payment->agent && $payment->agent->user ? $payment->agent->user->name : 'Agent';

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $payment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $payment->payment_number;
        $bankTransaction->description = 'Commission Payment #' . $payment->payment_number . ' - ' . $agentName;
        $bankTransaction->amount = $payment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($payment->bank_account_id, -$payment->payment_amount);
    }

    public function createCommissionAdjustmentBankTransaction($adjustment)
    {
        // Only create bank transaction if adjustment has bank_account_id
        if (!isset($adjustment->bank_account_id) || !$adjustment->bank_account_id) {
            return;
        }
        $lastTransaction = BankTransaction::where('bank_account_id', $adjustment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $agentName = $adjustment->agent && $adjustment->agent->user ? $adjustment->agent->user->name : 'Agent';
        $amount = abs($adjustment->adjustment_amount);

        // Bonus/Correction(+) = Debit (cash out to agent)
        // Penalty/Correction(-) = Credit (cash in from agent)
        if ($adjustment->adjustment_type === 'bonus' || ($adjustment->adjustment_type === 'correction' && $adjustment->adjustment_amount > 0)) {
            $transactionType = 'debit';
            $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $amount : -$amount;
            $balanceChange = -$amount;
        } else {
            $transactionType = 'credit';
            $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $amount : $amount;
            $balanceChange = $amount;
        }

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $adjustment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = $transactionType;
        $bankTransaction->reference_number = 'ADJ-' . $adjustment->id;
        $bankTransaction->description = 'Commission Adjustment (' . ucfirst($adjustment->adjustment_type) . ') - ' . $agentName;
        $bankTransaction->amount = $amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($adjustment->bank_account_id, $balanceChange);
    }

    public function createHolidayzBookingPayment($booking, $bankAccountId)
    {
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $booking->paid_amount : $booking->paid_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $booking->booking_number;
        $bankTransaction->description = 'Hotel Booking Payment: ' . $booking->booking_number;
        $bankTransaction->amount = $booking->paid_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = $booking->created_by;
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, $booking->paid_amount);
    }

    public function createPostFleetExpense($fleetExpense)
    {
        if (!$fleetExpense->bank_account_id) {
            throw new \Exception("Bank account is required for fleet expense");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $fleetExpense->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $fleetExpense->amount : -$fleetExpense->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $fleetExpense->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = 'FE-' . $fleetExpense->id;
        $bankTransaction->description = 'Fleet Expense: ' . ($fleetExpense->description ?? 'Fleet expense');
        $bankTransaction->amount = $fleetExpense->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($fleetExpense->bank_account_id, -$fleetExpense->amount);
    }

    public function eventBookingPaymentStatus($booking, $bankAccountId)
    {
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $booking->amount : $booking->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = 'EVENT-' . $booking->id;
        $bankTransaction->description = 'Event Booking Payment - ' . $booking->name;
        $bankTransaction->amount = $booking->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = $booking->created_by;
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, $booking->amount);
    }

    public function markCaseExpenseAsPaid($caseExpense)
    {
        if (!$caseExpense->bank_account_id) {
            throw new \Exception("Bank account is required to mark case expense as paid");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $caseExpense->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $caseExpense->amount : -$caseExpense->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $caseExpense->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $caseExpense->legalCase->case_number;
        $bankTransaction->description = 'Case Expense: ' . ($caseExpense->legalCase->title ?? 'Legal case expense');
        $bankTransaction->amount = $caseExpense->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($caseExpense->bank_account_id, -$caseExpense->amount);
    }

    public function feeReceiveAsCleared($feeReceive)
    {
        if (!$feeReceive->bank_account_id) {
            throw new \Exception("Bank account is required to case fee as clear");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $feeReceive->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $feeReceive->amount : $feeReceive->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $feeReceive->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $feeReceive->legalCase->case_number;
        $bankTransaction->description = 'Fee Received: ' . ($feeReceive->legalCase->title ?? 'Legal fee received');
        $bankTransaction->amount = $feeReceive->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($feeReceive->bank_account_id, $feeReceive->amount);
    }

    public function membershipPlanAssigned($payment)
    {
        if (!$payment->bank_account_id) {
            throw new \Exception("Bank account is required to membership plan payment");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $payment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $payment->fee : $payment->fee;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $payment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $payment->reference_number;
        $bankTransaction->description = 'Membership Plan Payment - ' . $payment->member->user->name ?? null;
        $bankTransaction->amount = $payment->fee;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($payment->bank_account_id, $payment->fee);
    }

    public function parkingBookingPayments($booking, $bankAccountId)
    {
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $booking->total_amount : $booking->total_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $booking->vehicle_number;
        $bankTransaction->description = 'Parking Booking Payment - ' . $booking->customer_name;
        $bankTransaction->amount = $booking->total_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = $booking->created_by;
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, $booking->total_amount);
    }

    public function vehicleBookingPayments($booking, $bankAccountId)
    {
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $booking->total_amount : $booking->total_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $booking->booking_number;
        $bankTransaction->description = 'Vehicle Booking Payment';
        $bankTransaction->amount = $booking->total_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = $booking->created_by;
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, $booking->total_amount);
    }

    public function laundryExpenseStatus($expense)
    {
        if (!$expense->bank_account_id) {
            throw new \Exception("Bank account is required for laundry expense");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $expense->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $expense->amount : -$expense->amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $expense->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = 'LE-' . $expense->id;
        $bankTransaction->description = 'Laundry Expense: ' . ($expense->description ?? 'Laundry expense');
        $bankTransaction->amount = $expense->amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($expense->bank_account_id, -$expense->amount);
    }

    public function laundryPaymentStatus($laundryRequest, $bankAccountId)
    {
        $lastTransaction = BankTransaction::where('bank_account_id', $bankAccountId)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $laundryRequest->total : $laundryRequest->total;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $bankAccountId;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $laundryRequest->request_number;
        $bankTransaction->description = 'Laundry Payment - ' . $laundryRequest->name;
        $bankTransaction->amount = $laundryRequest->total;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = $laundryRequest->created_by;
        $bankTransaction->save();

        $this->updateBankBalance($bankAccountId, $laundryRequest->total);
    }

    public function medicalOrderPayment($medicalOrderPayment)
    {
        if (!$medicalOrderPayment->bank_account_id) {
            throw new \Exception("Bank account is required for medical order payment");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $medicalOrderPayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $medicalOrderPayment->payment_amount : $medicalOrderPayment->payment_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $medicalOrderPayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $medicalOrderPayment->payment_number;
        $bankTransaction->description = 'Medical Lab Payment #' . $medicalOrderPayment->payment_number;
        $bankTransaction->amount = $medicalOrderPayment->payment_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($medicalOrderPayment->bank_account_id, $medicalOrderPayment->payment_amount);
    }

    public function completeWarrantyClaim($warrantyClaim)
    {
        if (!$warrantyClaim->bank_account_id) {
            throw new \Exception("Bank account is required to warranty claim");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $warrantyClaim->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();

        // Repair cost - debit (money out)
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $warrantyClaim->repair_cost : -$warrantyClaim->repair_cost;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $warrantyClaim->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $warrantyClaim->claim_number;
        $bankTransaction->description = 'Warranty Repair Cost - ' . $warrantyClaim->claim_number;
        $bankTransaction->amount = $warrantyClaim->repair_cost;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($warrantyClaim->bank_account_id, -$warrantyClaim->repair_cost);

        // Customer charge - credit (money in)
        if ($warrantyClaim->customer_charge_amount > 0) {
            $lastTransaction = BankTransaction::where('bank_account_id', $warrantyClaim->bank_account_id)
                ->orderBy('id', 'desc')
                ->first();
            $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $warrantyClaim->customer_charge_amount : $warrantyClaim->customer_charge_amount;

            $bankTransaction = new BankTransaction();
            $bankTransaction->bank_account_id = $warrantyClaim->bank_account_id;
            $bankTransaction->transaction_date = now();
            $bankTransaction->transaction_type = 'credit';
            $bankTransaction->reference_number = $warrantyClaim->claim_number;
            $bankTransaction->description = 'Warranty Customer Charge - ' . $warrantyClaim->claim_number;
            $bankTransaction->amount = $warrantyClaim->customer_charge_amount;
            $bankTransaction->running_balance = $runningBalance;
            $bankTransaction->transaction_status = 'cleared';
            $bankTransaction->reconciliation_status = 'unreconciled';
            $bankTransaction->created_by = creatorId();
            $bankTransaction->save();

            $this->updateBankBalance($warrantyClaim->bank_account_id, $warrantyClaim->customer_charge_amount);
        }
    }

    public function approvePettyCash($pettyCash) {
        if (!$pettyCash->bank_account_id) {
            throw new \Exception("Bank account is required for petty cash approval");
        }

        $lastTransaction = BankTransaction::where('bank_account_id', $pettyCash->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance - $pettyCash->added_amount : -$pettyCash->added_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $pettyCash->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'debit';
        $bankTransaction->reference_number = $pettyCash->pettycash_number ?? 'PC-' . $pettyCash->id;
        $bankTransaction->description = 'Petty Cash Expense - ' . ($pettyCash->description ?? 'Petty cash transaction');
        $bankTransaction->amount = $pettyCash->added_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        $this->updateBankBalance($pettyCash->bank_account_id, -$pettyCash->added_amount);
    }

    public function createProjectPayment($projectPayment)
    {
        if (!$projectPayment->bank_account_id) {
            throw new \Exception("Bank account is required for project payment");
        }

        // Get current running balance for the bank account
        $lastTransaction = BankTransaction::where('bank_account_id', $projectPayment->bank_account_id)
            ->orderBy('id', 'desc')
            ->first();
        $runningBalance = $lastTransaction ? $lastTransaction->running_balance + $projectPayment->total_amount : $projectPayment->total_amount;

        $bankTransaction = new BankTransaction();
        $bankTransaction->bank_account_id = $projectPayment->bank_account_id;
        $bankTransaction->transaction_date = now();
        $bankTransaction->transaction_type = 'credit';
        $bankTransaction->reference_number = $projectPayment->payment_number;
        $bankTransaction->description = 'Project Payment #' . $projectPayment->payment_number . ' - ' . $projectPayment->customer->name;
        $bankTransaction->amount = $projectPayment->total_amount;
        $bankTransaction->running_balance = $runningBalance;
        $bankTransaction->transaction_status = 'cleared';
        $bankTransaction->reconciliation_status = 'unreconciled';
        $bankTransaction->created_by = creatorId();
        $bankTransaction->save();

        // Update bank account balance
        $this->updateBankBalance($projectPayment->bank_account_id, $projectPayment->total_amount);
    }
}
