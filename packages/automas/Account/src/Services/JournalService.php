<?php

namespace Automas\Account\Services;

use Automas\Account\Models\JournalEntry;
use Automas\Account\Models\JournalEntryItem;
use Automas\Account\Models\ChartOfAccount;
use Illuminate\Support\Facades\Auth;
use Automas\Account\Events\UpdateBudgetSpending;
use Automas\Account\Models\BankAccount;
use Automas\Retainer\Models\RetainerPaymentAllocation;

/**
 * JournalService - Automatic double-entry journal creation for all transactions
 * Usage: Call from transaction controllers after creating records (Invoice, Payment, etc.)
 */
class JournalService
{
    private function validateAccounts(array $accountCodes , $userID = null)
    {
        foreach ($accountCodes as $code) {
            $account = ChartOfAccount::where('account_code', $code)
                ->where('created_by', $userID ?? creatorId())
                ->first();
            if (!$account) {
                throw new \Exception("Account with code {$code} not found");
            }
        }
    }

    private function validateBalance($totalDebit, $totalCredit)
    {
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \Exception("Journal entry not balanced: Debit {$totalDebit} != Credit {$totalCredit}");
        }
    }

    /**
     * Creates journal entry for sales invoice: Dr: A/R, Cr: Sales Revenue + Tax
     * Usage: SalesInvoiceController->store() after creating invoice
     */
    public function createSalesInvoiceJournal($salesInvoice)
    {
        // Validate required accounts exist
        $requiredAccounts = ['1100', '4100'];
        if ($salesInvoice->tax_amount > 0) {
            $requiredAccounts[] = '2210';
        }
        $this->validateAccounts($requiredAccounts);
        // Validate amounts balance
        $totalDebit = $salesInvoice->total_amount;
        $totalCredit = $salesInvoice->subtotal - $salesInvoice->discount_amount + ($salesInvoice->tax_amount ?? 0);
        $this->validateBalance($totalDebit, $totalCredit);

        $arAccount = ChartOfAccount::where('account_code', '1100')->where('created_by', creatorId())->first();
        $salesAccount = ChartOfAccount::where('account_code', '4100')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '2210')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $salesInvoice->invoice_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'sales_invoice',
            'reference_id' => $salesInvoice->id,
            'description' => 'Sales Invoice #' . $salesInvoice->invoice_number,
            'total_debit' => $salesInvoice->total_amount,
            'total_credit' => $salesInvoice->total_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Accounts Receivable
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'Sales to ' . $salesInvoice->customer->name,
            'debit_amount' => $salesInvoice->total_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Sales Revenue
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesAccount->id,
            'description' => 'Product sales',
            'debit_amount' => 0,
            'credit_amount' => $salesInvoice->subtotal - $salesInvoice->discount_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Tax Payable (if tax exists)
        if ($salesInvoice->tax_amount > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Sales tax collected',
                'debit_amount' => 0,
                'credit_amount' => $salesInvoice->tax_amount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function createSalesRetainerToInvoiceJournal($retainer)
    {

        $allocations = RetainerPaymentAllocation::whereHas('payment', function($q) {
                $q->where('status', 'cleared');
            })
            ->where('retainer_id', $retainer->id)
            ->get();

        $totalAmount = $allocations->sum('allocated_amount');

        if ($totalAmount <= 0) {
            return null;
        }

        $this->validateAccounts(['2350', '1100']);
        $this->validateBalance($totalAmount, $totalAmount);

        $customerDepositsAccount = ChartOfAccount::where('account_code', '2350')->where('created_by', creatorId())->first();
        $arAccount = ChartOfAccount::where('account_code', '1100')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => now(),
            'entry_type' => 'automatic',
            'reference_type' => 'retainer_to_invoice',
            'reference_id' => $retainer->id,
            'description' => 'Retainer converted to invoice',
            'total_debit' => $totalAmount,
            'total_credit' => $totalAmount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $customerDepositsAccount->id,
            'description' => 'Retainer converted to invoice',
            'debit_amount' => $totalAmount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'Retainer converted to invoice',
            'debit_amount' => 0,
            'credit_amount' => $totalAmount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }
    /**
     * Creates journal entry for service invoice: Dr: A/R, Cr: Service Revenue + Tax
     * Usage: PostSalesInvoiceListener for service type invoices
     */
    public function createServiceInvoiceJournal($salesInvoice)
    {
        $requiredAccounts = ['1100', '4200'];
        if ($salesInvoice->tax_amount > 0) {
            $requiredAccounts[] = '2210';
        }
        $this->validateAccounts($requiredAccounts);

        $totalDebit = $salesInvoice->total_amount;
        $totalCredit = $salesInvoice->subtotal - $salesInvoice->discount_amount + ($salesInvoice->tax_amount ?? 0);
        $this->validateBalance($totalDebit, $totalCredit);

        $arAccount = ChartOfAccount::where('account_code', '1100')->where('created_by', creatorId())->first();
        $serviceRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '2210')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $salesInvoice->invoice_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'service_invoice',
            'reference_id' => $salesInvoice->id,
            'description' => 'Service Invoice #' . $salesInvoice->invoice_number,
            'total_debit' => $salesInvoice->total_amount,
            'total_credit' => $salesInvoice->total_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'Service to ' . $salesInvoice->customer->name,
            'debit_amount' => $salesInvoice->total_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $serviceRevenueAccount->id,
            'description' => 'Service revenue',
            'debit_amount' => 0,
            'credit_amount' => $salesInvoice->subtotal - $salesInvoice->discount_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        if ($salesInvoice->tax_amount > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Sales tax collected',
                'debit_amount' => 0,
                'credit_amount' => $salesInvoice->tax_amount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for purchase inventory: Dr: Inventory + Tax, Cr: A/P
     * Usage: PostPurchaseInvoiceListener after creating invoice
     */
    public function createPurchaseInventoryJournal($purchaseInvoice)
    {
        $requiredAccounts = ['2000', '1200'];
        if ($purchaseInvoice->tax_amount > 0) {
            $requiredAccounts[] = '1500';
        }

        $this->validateAccounts($requiredAccounts);

        $totalDebit = $purchaseInvoice->subtotal - $purchaseInvoice->discount_amount + ($purchaseInvoice->tax_amount ?? 0);
        $totalCredit = $purchaseInvoice->total_amount;
        $this->validateBalance($totalDebit, $totalCredit);

        $apAccount = ChartOfAccount::where('account_code', '2000')->where('created_by', creatorId())->first();
        $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '1500')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $purchaseInvoice->invoice_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'purchase_invoice',
            'reference_id' => $purchaseInvoice->id,
            'description' => 'Purchase Invoice #' . $purchaseInvoice->invoice_number,
            'total_debit' => $purchaseInvoice->total_amount,
            'total_credit' => $purchaseInvoice->total_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $inventoryAccount->id,
            'description' => 'Purchase from ' . $purchaseInvoice->vendor->name,
            'debit_amount' => $purchaseInvoice->subtotal - $purchaseInvoice->discount_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        if ($purchaseInvoice->tax_amount > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Purchase tax paid',
                'debit_amount' => $purchaseInvoice->tax_amount,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $apAccount->id,
            'description' => 'Purchase from vendor',
            'debit_amount' => 0,
            'credit_amount' => $purchaseInvoice->total_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for customer payment: Dr: Bank, Cr: A/R
     * Usage: CustomerPaymentController->updateStatus() when payment is cleared
     */
    public function createCustomerPaymentJournal($customerPayment)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = $customerPayment->bankAccount->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Validate A/R account exists
        $this->validateAccounts(['1100']);
        $arAccount = ChartOfAccount::where('account_code', '1100')->where('created_by', creatorId())->first();

        // Validate amounts balance
        $this->validateBalance($customerPayment->payment_amount, $customerPayment->payment_amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $customerPayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'customer_payment',
            'reference_id' => $customerPayment->id,
            'description' => 'Customer Payment #' . $customerPayment->payment_number,
            'total_debit' => $customerPayment->payment_amount,
            'total_credit' => $customerPayment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment received from ' . $customerPayment->customer->name,
            'debit_amount' => $customerPayment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Accounts Receivable
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'Payment from customer',
            'debit_amount' => 0,
            'credit_amount' => $customerPayment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for vendor payment: Dr: A/P, Cr: Bank
     * Usage: VendorPaymentController->store() after making payment
     */
    public function createVendorPaymentJournal($vendorPayment)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = $vendorPayment->bankAccount->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Validate A/P account exists
        $this->validateAccounts(['2000']);
        $apAccount = ChartOfAccount::where('account_code', '2000')->where('created_by', creatorId())->first();
        // Validate amounts balance
        $this->validateBalance($vendorPayment->payment_amount, $vendorPayment->payment_amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $vendorPayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'vendor_payment',
            'reference_id' => $vendorPayment->id,
            'description' => 'Vendor Payment #' . $vendorPayment->payment_number,
            'total_debit' => $vendorPayment->payment_amount,
            'total_credit' => $vendorPayment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);
        // Debit: Accounts Payable
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $apAccount->id,
            'description' => 'Payment to ' . $vendorPayment->vendor->name,
            'debit_amount' => $vendorPayment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment from ' . $vendorPayment->bankAccount->account_name,
            'debit_amount' => 0,
            'credit_amount' => $vendorPayment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for revenue: Dr: Bank, Cr: Revenue
     * Usage: RevenueController->store() after recording revenue
     */
    public function createRevenueEntryJournal($revenueEntry)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = $revenueEntry->bankAccount->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Use selected chart of account
        $revenueAccount = $revenueEntry->chartOfAccount;
        if (!$revenueAccount) {
            throw new \Exception("Revenue account not found");
        }

        // Validate amounts balance
        $this->validateBalance($revenueEntry->amount, $revenueEntry->amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $revenueEntry->entry_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'revenue',
            'reference_id' => $revenueEntry->id,
            'description' => 'Revenue Entry - #' . $revenueEntry->revenue_number,
            'total_debit' => $revenueEntry->amount,
            'total_credit' => $revenueEntry->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Revenue received',
            'debit_amount' => $revenueEntry->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Selected Revenue Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $revenueAccount->id,
            'description' => 'Revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $revenueEntry->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for expense: Dr: Expense, Cr: Bank
     * Usage: ExpenseController->store() after recording expense
     */
    public function createExpenseEntryJournal($expenseEntry)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = $expenseEntry->bankAccount->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Use selected chart of account
        $expenseAccount = $expenseEntry->chartOfAccount;
        if (!$expenseAccount) {
            throw new \Exception("Expense account not found");
        }

        // Validate amounts balance
        $this->validateBalance($expenseEntry->amount, $expenseEntry->amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $expenseEntry->entry_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'expense',
            'reference_id' => $expenseEntry->id,
            'description' => 'Expense Entry - #' . $expenseEntry->expense_number,
            'total_debit' => $expenseEntry->amount,
            'total_credit' => $expenseEntry->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Selected Expense Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $expenseAccount->id,
            'description' => 'Expense incurred',
            'debit_amount' => $expenseEntry->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment made',
            'debit_amount' => 0,
            'credit_amount' => $expenseEntry->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for stock transfer: Dr: To Warehouse, Cr: From Warehouse
     * Usage: StockTransferController->store() after transferring inventory
     */
    public function createStockTransferJournal($stockTransfer)
    {
        // Calculate transfer value (quantity * product cost or use default value)
        $transferValue = $stockTransfer->quantity * ($stockTransfer->product->purchase_price ?? 1);

        // Validate required accounts exist
        $this->validateAccounts(['1200']); // Inventory account

        // Validate amounts balance
        $this->validateBalance($transferValue, $transferValue);

        $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $stockTransfer->date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'stock_transfer',
            'reference_id' => $stockTransfer->id,
            'description' => 'Stock Transfer #' . $stockTransfer->id . ' - ' . $stockTransfer->fromWarehouse->name . ' to ' . $stockTransfer->toWarehouse->name,
            'total_debit' => $transferValue,
            'total_credit' => $transferValue,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: To Warehouse Inventory
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $inventoryAccount->id,
            'description' => 'Stock received at ' . $stockTransfer->toWarehouse->name,
            'debit_amount' => $transferValue,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: From Warehouse Inventory
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $inventoryAccount->id,
            'description' => 'Stock transferred from ' . $stockTransfer->fromWarehouse->name,
            'debit_amount' => 0,
            'credit_amount' => $transferValue,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }


    /**
     * Deletes journal entry and items for stock transfer
     * Usage: DeleteTransferListener after transfer deletion
     */
    public function deleteStockTransferJournal($transferId)
    {
        $journalEntry = JournalEntry::where('reference_type', 'stock_transfer')
                                  ->where('reference_id', $transferId)
                                  ->first();

        if ($journalEntry) {
            // Reverse account balances before deleting
            $this->reverseAccountBalances($journalEntry);

            // Delete journal entry items first
            JournalEntryItem::where('journal_entry_id', $journalEntry->id)->delete();

            // Delete journal entry
            $journalEntry->delete();
        }
    }

    private function updateAccountBalances($journalEntry)
    {
        $journalEntry->load('items.account');

        foreach($journalEntry->items as $item) {
            $account = $item->account;
            $debitAmount = $item->debit_amount;
            $creditAmount = $item->credit_amount;

            if ($account->normal_balance === 'debit') {
                $account->current_balance += ($debitAmount - $creditAmount);
            } else {
                $account->current_balance += ($creditAmount - $debitAmount);
            }

            $account->save();
        }
    }

    private function reverseAccountBalances($journalEntry)
    {
        $journalEntry->load('items.account');

        foreach($journalEntry->items as $item) {
            $account = $item->account;
            $debitAmount = $item->debit_amount;
            $creditAmount = $item->credit_amount;

            // Reverse the balance update
            if ($account->normal_balance === 'debit') {
                $account->current_balance -= ($debitAmount - $creditAmount);
            } else {
                $account->current_balance -= ($creditAmount - $debitAmount);
            }

            $account->save();
        }
    }

    /**
     * Creates journal entry for debit note: Dr: A/P, Cr: Inventory + Tax Receivable
     * Usage: DebitNoteController->approve() after approving debit note
     */
    public function createDebitNoteJournal($debitNote)
    {
        $requiredAccounts = ['2000', '1200'];
        if ($debitNote->tax_amount > 0) {
            $requiredAccounts[] = '1500';
        }
        $this->validateAccounts($requiredAccounts);

        $totalDebit = $debitNote->total_amount;
        $totalCredit = $debitNote->subtotal  - $debitNote->discount_amount + ($debitNote->tax_amount ?? 0);
        $this->validateBalance($totalDebit, $totalCredit);

        $apAccount = ChartOfAccount::where('account_code', '2000')->where('created_by', creatorId())->first();
        $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '1500')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $debitNote->debit_note_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'debit_note',
            'reference_id' => $debitNote->id,
            'description' => 'Debit Note #' . $debitNote->debit_note_number,
            'total_debit' => $debitNote->total_amount,
            'total_credit' => $debitNote->total_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $apAccount->id,
            'description' => 'Debit Note - ' . $debitNote->vendor->name,
            'debit_amount' => $debitNote->total_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $inventoryAccount->id,
            'description' => 'Goods returned to vendor',
            'debit_amount' => 0,
            'credit_amount' => $debitNote->subtotal - $debitNote->discount_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        if ($debitNote->tax_amount > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Tax credit from debit note',
                'debit_amount' => 0,
                'credit_amount' => $debitNote->tax_amount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for credit note: Dr: Sales Revenue + Tax, Cr: A/R
     * Usage: CreditNoteController->approve() after approving credit note
     */
    public function createCreditNoteJournal($creditNote)
    {
        // Validate required accounts exist
        $requiredAccounts = ['1100', '4100'];
        if ($creditNote->tax_amount > 0) {
            $requiredAccounts[] = '2210';
        }
        $this->validateAccounts($requiredAccounts);

        // Validate amounts balance
        $totalDebit = $creditNote->subtotal - $creditNote->discount_amount + ($creditNote->tax_amount ?? 0);
        $totalCredit = $creditNote->total_amount;
        $this->validateBalance($totalDebit, $totalCredit);

        $arAccount = ChartOfAccount::where('account_code', '1100')->where('created_by', creatorId())->first();
        $salesAccount = ChartOfAccount::where('account_code', '4100')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '2210')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $creditNote->credit_note_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'credit_note',
            'reference_id' => $creditNote->id,
            'description' => 'Credit Note #' . $creditNote->credit_note_number,
            'total_debit' => $creditNote->total_amount,
            'total_credit' => $creditNote->total_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Sales Revenue (reduces revenue)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesAccount->id,
            'description' => 'Credit Note adjustment',
            'debit_amount' => $creditNote->subtotal - $creditNote->discount_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Tax Payable (if tax exists)
        if ($creditNote->tax_amount > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Tax reduction from credit note',
                'debit_amount' => $creditNote->tax_amount,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        // Credit: Accounts Receivable (reduces customer debt)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'Credit Note - ' . $creditNote->customer->name,
            'debit_amount' => 0,
            'credit_amount' => $creditNote->total_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for bank transfer: Dr: To Bank + Charges, Cr: From Bank
     * Usage: BankTransferController->process() after processing transfer
     */
    public function createBankTransferJournal($bankTransfer)
    {
        // Get the specific bank accounts' GL accounts
        $fromBankGLAccount = $bankTransfer->fromAccount->glAccount;
        if (!$fromBankGLAccount) {
            throw new \Exception( __("Source bank account must have a GL account assigned"));
        }

        $toBankGLAccount = $bankTransfer->toAccount->glAccount;
        if (!$toBankGLAccount) {
            throw new \Exception( __("Destination bank account must have a GL account assigned"));
        }

        $bankChargesAccount = ChartOfAccount::where('account_code', '5510')->where('created_by', creatorId())->first();

        $totalDebit = $bankTransfer->transfer_amount + $bankTransfer->transfer_charges;
        $totalCredit = $bankTransfer->transfer_amount + $bankTransfer->transfer_charges;

        $this->validateBalance($totalDebit, $totalCredit);

        $journalEntry = JournalEntry::create([
            'journal_date' => $bankTransfer->transfer_date,
            'entry_type' => 'automatic',
            'reference_type' => 'bank_transfer',
            'reference_id' => $bankTransfer->id,
            'description' => 'Bank Transfer #' . $bankTransfer->transfer_number,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Destination Bank Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $toBankGLAccount->id,
            'description' => 'Transfer received from ' . $bankTransfer->fromAccount->account_name,
            'debit_amount' => $bankTransfer->transfer_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Bank Charges (if any)
        if ($bankTransfer->transfer_charges > 0 && $bankChargesAccount) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $bankChargesAccount->id,
                'description' => 'Bank transfer charges',
                'debit_amount' => $bankTransfer->transfer_charges,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        // Credit: Source Bank Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $fromBankGLAccount->id,
            'description' => 'Transfer sent to ' . $bankTransfer->toAccount->account_name,
            'debit_amount' => 0,
            'credit_amount' => $totalDebit,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }
    public function createRetainerPaymentJournal($retainerPayment)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = BankAccount::where('id', $retainerPayment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Validate Customer Deposits exists (2350)
        $this->validateAccounts(['2350']);
        $unearnedRevenueAccount = ChartOfAccount::where('account_code', '2350')->where('created_by', creatorId())->first();

        // Validate amounts balance
        $this->validateBalance($retainerPayment->payment_amount, $retainerPayment->payment_amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $retainerPayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'retainer_payment',
            'reference_id' => $retainerPayment->id,
            'description' => 'Retainer Payment from ' . $retainerPayment->customer->name,
            'total_debit' => $retainerPayment->payment_amount,
            'total_credit' => $retainerPayment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);
        // Debit: Specific Bank Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Retainer advance payment received',
            'debit_amount' => $retainerPayment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Unearned Revenue (Liability)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $unearnedRevenueAccount->id,
            'description' => 'Retainer advance payment received',
            'debit_amount' => 0,
            'credit_amount' => $retainerPayment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);


        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }
    public function createCommissionPaymentJournal($commissionPayment)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = BankAccount::where('id', $commissionPayment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Validate Commission Expense account exists (5220)
        $this->validateAccounts(['5220']);
        $commissionExpenseAccount = ChartOfAccount::where('account_code', '5220')->where('created_by', creatorId())->first();

        // Validate amounts balance
        $this->validateBalance($commissionPayment->payment_amount, $commissionPayment->payment_amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $commissionPayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'commission_payment',
            'reference_id' => $commissionPayment->id,
            'description' => 'Commission Payment to ' . $commissionPayment->agent->name,
            'total_debit' => $commissionPayment->payment_amount,
            'total_credit' => $commissionPayment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Commission Expense
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $commissionExpenseAccount->id,
            'description' => 'Commission paid to agent',
            'debit_amount' => $commissionPayment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Specific Bank Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Commission payment from ' . $bankGLAccount->account_name,
            'debit_amount' => 0,
            'credit_amount' => $commissionPayment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for payroll: Dr: Salary Expense, Cr: Bank
     * Usage: PaySalaryListener after paying salary
     */
    public function createPayrollJournal($payrollEntry)
    {
        $bankGLAccount = BankAccount::where('id', $payrollEntry->payroll->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['5200']);
        $salaryExpenseAccount = ChartOfAccount::where('account_code', '5200')->where('created_by', creatorId())->first();
        $this->validateBalance($payrollEntry->net_pay, $payrollEntry->net_pay);

        $journalEntry = JournalEntry::create([
            'journal_date' => now(),
            'entry_type' => 'automatic',
            'reference_type' => 'payroll',
            'reference_id' => $payrollEntry->id,
            'description' => 'Salary Payment - ' . $payrollEntry->employee->user->name,
            'total_debit' => $payrollEntry->net_pay,
            'total_credit' => $payrollEntry->net_pay,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salaryExpenseAccount->id,
            'description' => 'Salary paid to ' . $payrollEntry->employee->user->name,
            'debit_amount' => $payrollEntry->net_pay,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Salary payment',
            'debit_amount' => 0,
            'credit_amount' => $payrollEntry->net_pay,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for POS sale: Dr: Cash/Bank, Cr: Sales Revenue + Tax
     * Usage: CreatePosListener after POS sale
     */
    public function createPosJournal($posSale)
    {
        $bankGLAccount = BankAccount::where('id', $posSale->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $posSale->load(['payment', 'items']);

        $taxAmount = $posSale->items->sum('tax_amount');
        $requiredAccounts = ['1010', '4100'];
        if ($taxAmount > 0) {
            $requiredAccounts[] = '2210';
        }
        $this->validateAccounts($requiredAccounts);

        $salesAccount = ChartOfAccount::where('account_code', '4100')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '2210')->where('created_by', creatorId())->first();

        $subtotal = $posSale->items->sum('subtotal');
        $discount = $posSale->payment->discount ?? 0;
        $taxAmount = $taxAmount;
        $totalAmount = $subtotal - $discount + $taxAmount;

        $this->validateBalance($totalAmount, $totalAmount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $posSale->pos_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'pos_sale',
            'reference_id' => $posSale->id,
            'description' => 'POS Sale ' . $posSale->sale_number,
            'total_debit' => $totalAmount,
            'total_credit' => $totalAmount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'POS cash sale',
            'debit_amount' => $totalAmount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesAccount->id,
            'description' => 'POS product sales',
            'debit_amount' => 0,
            'credit_amount' => $subtotal - $discount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        if ($taxAmount > 0 && $taxAccount) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Sales tax collected',
                'debit_amount' => 0,
                'credit_amount' => $taxAmount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for POS return: Dr: Sales Revenue + Tax, Cr: Cash/Bank
     * Usage: ApprovePosReturnListener after POS return
     */
    public function approvePosReturnJournal($posReturn)
    {
        $bankGLAccount = BankAccount::where('id', $posReturn->originalPos->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $posReturn->load(['items']);

        $requiredAccounts = ['1010', '4100'];
        if ($posReturn->tax_amount > 0) {
            $requiredAccounts[] = '2210';
        }
        $this->validateAccounts($requiredAccounts);

        $salesAccount = ChartOfAccount::where('account_code', '4100')->where('created_by', creatorId())->first();
        $taxAccount = ChartOfAccount::where('account_code', '2210')->where('created_by', creatorId())->first();

        $subtotal = $posReturn->subtotal ?? 0;
        $discount = $posReturn->discount_amount ?? 0;
        $taxAmount = $posReturn->tax_amount ?? 0;
        $totalAmount = $subtotal - $discount + $taxAmount;

        $this->validateBalance($totalAmount, $totalAmount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $posReturn->return_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'pos_return',
            'reference_id' => $posReturn->id,
            'description' => 'POS Return ' . $posReturn->return_number,
            'total_debit' => $totalAmount,
            'total_credit' => $totalAmount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Sales Revenue (reduces revenue)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesAccount->id,
            'description' => 'POS product return',
            'debit_amount' => $subtotal - $discount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Tax Payable (if tax exists)
        if ($taxAmount > 0 && $taxAccount) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Sales tax refunded',
                'debit_amount' => $taxAmount,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        // Credit: Bank (refund to customer)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'POS cash refund',
            'debit_amount' => 0,
            'credit_amount' => $totalAmount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates COGS journal entry for sales invoice: Dr: COGS, Cr: Inventory
     * Usage: PostSalesInvoiceListener after creating sales invoice
     */
    public function createSalesCOGSJournal($salesInvoice)
    {
        $salesInvoice->load('items.product');
        $totalCost = 0;

        foreach ($salesInvoice->items as $item) {
            if (!$item->product) {
                continue;
            }
            $costPrice = $item->product->purchase_price ?? 0;
            $totalCost += $item->quantity * $costPrice;
        }

        if ($totalCost <= 0.01) {
            return null;
        }

        $this->validateAccounts(['5100', '1200']);

        $cogsAccount = ChartOfAccount::where('account_code', '5100')->where('created_by', creatorId())->first();
        $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();

        $this->validateBalance($totalCost, $totalCost);

        $journalEntry = JournalEntry::create([
            'journal_date' => $salesInvoice->invoice_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'sales_invoice_cogs',
            'reference_id' => $salesInvoice->id,
            'description' => 'COGS for Sales Invoice #' . $salesInvoice->invoice_number,
            'total_debit' => $totalCost,
            'total_credit' => $totalCost,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cogsAccount->id,
            'description' => 'Cost of goods sold',
            'debit_amount' => $totalCost,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $inventoryAccount->id,
            'description' => 'Inventory reduction',
            'debit_amount' => 0,
            'credit_amount' => $totalCost,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates COGS journal entry for POS sale: Dr: COGS, Cr: Inventory
     * Usage: CreatePosListener after creating POS sale
     */
    public function createPosCOGSJournal($posSale)
    {
        try {
            $posSale->load('items.product');
            $totalCost = 0;

            foreach ($posSale->items as $item) {
                if (!$item->product) {
                    continue;
                }
                $costPrice = $item->product->purchase_price ?? 0;
                $totalCost += $item->quantity * $costPrice;
            }

            if ($totalCost <= 0.01) {
                return null;
            }

            $this->validateAccounts(['5100', '1200']);

            $cogsAccount = ChartOfAccount::where('account_code', '5100')->where('created_by', creatorId())->first();
            $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();

            $this->validateBalance($totalCost, $totalCost);

            $journalEntry = JournalEntry::create([
                'journal_date' => $posSale->pos_date ?? now(),
                'entry_type' => 'automatic',
                'reference_type' => 'pos_sale_cogs',
                'reference_id' => $posSale->id,
                'description' => 'COGS for POS Sale ' . $posSale->sale_number,
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'status' => 'posted',
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cogsAccount->id,
                'description' => 'Cost of goods sold',
                'debit_amount' => $totalCost,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'description' => 'Inventory reduction',
                'debit_amount' => 0,
                'credit_amount' => $totalCost,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Creates COGS reversal journal entry for POS return: Dr: Inventory, Cr: COGS
     * Usage: ApprovePosReturnListener after creating POS return
     */
    public function approvePosReturnCOGSJournal($posReturn)
    {
        try {
            $posReturn->load('items.product');
            $totalCost = 0;

            foreach ($posReturn->items as $item) {
                if (!$item->product) {
                    continue;
                }
                $costPrice = $item->product->purchase_price ?? 0;
                $totalCost += $item->return_quantity * $costPrice;
            }

            if ($totalCost <= 0.01) {
                return null;
            }

            $this->validateAccounts(['5100', '1200']);

            $cogsAccount = ChartOfAccount::where('account_code', '5100')->where('created_by', creatorId())->first();
            $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();

            $this->validateBalance($totalCost, $totalCost);

            $journalEntry = JournalEntry::create([
                'journal_date' => $posReturn->return_date ?? now(),
                'entry_type' => 'automatic',
                'reference_type' => 'pos_return_cogs',
                'reference_id' => $posReturn->id,
                'description' => 'COGS Reversal for POS Return ' . $posReturn->return_number,
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'status' => 'posted',
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            // Debit: Inventory (goods returned)
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'description' => 'Inventory returned',
                'debit_amount' => $totalCost,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            // Credit: COGS (reversal)
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cogsAccount->id,
                'description' => 'COGS reversal',
                'debit_amount' => 0,
                'credit_amount' => $totalCost,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Creates COGS reversal journal entry for credit note: Dr: Inventory, Cr: COGS
     * Usage: CreditNoteController->approve() after approving credit note
     */
    public function createCreditNoteCOGSJournal($creditNote)
    {
        try {
            $creditNote->load('items.product');
            $totalCost = 0;

            foreach ($creditNote->items as $item) {
                if (!$item->product) {
                    continue;
                }
                $costPrice = $item->product->purchase_price ?? 0;
                $totalCost += $item->quantity * $costPrice;
            }

            if ($totalCost <= 0.01) {
                return null;
            }

            $this->validateAccounts(['5100', '1200']);

            $cogsAccount = ChartOfAccount::where('account_code', '5100')->where('created_by', creatorId())->first();
            $inventoryAccount = ChartOfAccount::where('account_code', '1200')->where('created_by', creatorId())->first();

            $this->validateBalance($totalCost, $totalCost);

            $journalEntry = JournalEntry::create([
                'journal_date' => $creditNote->credit_note_date ?? now(),
                'entry_type' => 'automatic',
                'reference_type' => 'credit_note_cogs',
                'reference_id' => $creditNote->id,
                'description' => 'COGS Reversal for Credit Note #' . $creditNote->credit_note_number,
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'status' => 'posted',
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'description' => 'Inventory returned',
                'debit_amount' => $totalCost,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cogsAccount->id,
                'description' => 'COGS reversal',
                'debit_amount' => 0,
                'credit_amount' => $totalCost,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            $this->updateAccountBalances($journalEntry);
            return $journalEntry;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Creates journal entry for mobile service payment: Dr: Bank, Cr: Mobile Service Revenue
     * Usage: MobileServiceController->store() after recording mobile service payment
     */
    public function createMobileServicePaymentJournal($payment)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = BankAccount::where('id', $payment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Validate required accounts exist (4200 for Mobile Service Revenue)
        $this->validateAccounts(['4200']);

        // Validate amounts balance
        $this->validateBalance($payment->payment_amount, $payment->payment_amount);

        $mobileServiceRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $payment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'mobile_service_payment',
            'reference_id' => $payment->id,
            'description' => 'Mobile Service Payment - ' . $payment->notes,
            'total_debit' => $payment->payment_amount,
            'total_credit' => $payment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);
        // Debit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Mobile service payment received',
            'debit_amount' => $payment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Mobile Service Revenue Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $mobileServiceRevenueAccount->id,
            'description' => 'Mobile service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $payment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for fleet booking payment: Dr: Bank, Cr: Fleet Service Revenue
     * Usage: FleetBookingController->markPayment() after recording fleet booking payment
     */
    public function createMarkFleetBookingPaymentJournal($payment)
    {
        // Get the specific bank account's GL account
        $bankGLAccount = BankAccount::where('id', $payment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        // Validate required accounts exist (4300 for Fleet Service Revenue)
        $this->validateAccounts(['4300']);

        // Validate amounts balance
        $this->validateBalance($payment->payment_amount, $payment->payment_amount);

        $fleetServiceRevenueAccount = ChartOfAccount::where('account_code', '4300')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $payment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'fleet_booking_payment',
            'reference_id' => $payment->id,
            'description' => 'Fleet Booking Payment - ' . ($payment->notes ?? 'Fleet service payment'),
            'total_debit' => $payment->payment_amount,
            'total_credit' => $payment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);
        // Debit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Fleet booking payment received',
            'debit_amount' => $payment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Fleet Service Revenue Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $fleetServiceRevenueAccount->id,
            'description' => 'Fleet service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $payment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for beauty booking payment: Dr: Bank, Cr: Beauty Service Revenue
     * Usage: BeautyBookingController->markPayment() after recording beauty booking payment
     */
    public function createBeautyBookingPaymentJournal($booking, $bankAccountId)
    {

        // Get bank account by payment gateway
        $bankAccount = BankAccount::where('id', $bankAccountId)->where('created_by', $booking->created_by)->first();

        if (!$bankAccount || !$bankAccount->glAccount) {
            throw new \Exception("Bank account with GL account not found for payment gateway: " . $booking->payment_option);
        }

        // Validate required accounts exist (4200 for Service Revenue)
        $this->validateAccounts(['4200'], $booking->created_by);
        // Validate amounts balance
        $this->validateBalance($booking->price, $booking->price);

        $beautyServiceRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', $booking->created_by)->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $booking->date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'beauty_booking_payment',
            'reference_id' => $booking->id,
            'description' => 'Beauty Booking Payment via ' . $booking->payment_option,
            'total_debit' => $booking->price,
            'total_credit' => $booking->price,
            'status' => 'posted',
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);
        // Debit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankAccount->glAccount->id,
            'description' => 'Beauty booking payment received',
            'debit_amount' => $booking->price,
            'credit_amount' => 0,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        // Credit: Beauty Service Revenue Account
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $beautyServiceRevenueAccount->id,
            'description' => 'Beauty service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $booking->price,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function createDairyCattlePaymentJournal($dairyCattlePayment)
    {
        $bankGLAccount = BankAccount::where('id', $dairyCattlePayment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['4200']);
        $this->validateBalance($dairyCattlePayment->payment_amount, $dairyCattlePayment->payment_amount);

        $dairyCattleRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $dairyCattlePayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'dairy_cattle_payment',
            'reference_id' => $dairyCattlePayment->id,
            'description' => 'Dairy Cattle Payment - ' . ($dairyCattlePayment->notes ?? 'Dairy cattle service payment'),
            'total_debit' => $dairyCattlePayment->payment_amount,
            'total_credit' => $dairyCattlePayment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Dairy cattle payment received',
            'debit_amount' => $dairyCattlePayment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $dairyCattleRevenueAccount->id,
            'description' => 'Dairy cattle service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $dairyCattlePayment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function dairyCattleExpenseTrackingJournal($expenseTracking)
    {
        $bankGLAccount = BankAccount::where('id', $expenseTracking->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['5305']);
        $this->validateBalance($expenseTracking->amount, $expenseTracking->amount);

        $dairyCattleExpenseAccount = ChartOfAccount::where('account_code', '5305')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $expenseTracking->expense_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'dairy_cattle_expense_tracking',
            'reference_id' => $expenseTracking->id,
            'description' => 'Dairy Cattle Expense - ' . ($expenseTracking->description ?? 'Dairy cattle expense'),
            'total_debit' => $expenseTracking->amount,
            'total_credit' => $expenseTracking->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $dairyCattleExpenseAccount->id,
            'description' => 'Dairy cattle expense incurred',
            'debit_amount' => $expenseTracking->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment made',
            'debit_amount' => 0,
            'credit_amount' => $expenseTracking->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function createCateringOrderPaymentJournal($payment) {

        $bankGLAccount = BankAccount::where('id', $payment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }
        $this->validateAccounts(['4200']);
        $this->validateBalance($payment->amount, $payment->amount);

        $cateringServiceRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $payment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'catering_order_payment',
            'reference_id' => $payment->id,
            'description' => 'Catering Order Payment - ' . ($payment->notes ?? 'Catering service payment'),
            'total_debit' => $payment->amount,
            'total_credit' => $payment->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Catering order payment received',
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cateringServiceRevenueAccount->id,
            'description' => 'Catering service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function cateringExpenseTrackingJournal($expenseTracking)
    {
        $bankGLAccount = BankAccount::where('id', $expenseTracking->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['5306']);
        $this->validateBalance($expenseTracking->amount, $expenseTracking->amount);

        $cateringExpenseAccount = ChartOfAccount::where('account_code', '5306')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $expenseTracking->date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'catering_expense_tracking',
            'reference_id' => $expenseTracking->id,
            'description' => 'Catering Expense - ' . ($expenseTracking->description ?? 'Catering expense'),
            'total_debit' => $expenseTracking->amount,
            'total_credit' => $expenseTracking->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cateringExpenseAccount->id,
            'description' => 'Catering expense incurred',
            'debit_amount' => $expenseTracking->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment made',
            'debit_amount' => 0,
            'credit_amount' => $expenseTracking->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function createUpdateSalesAgentCommissionPaymentJournal($payment)
    {
        $bankAccount = BankAccount::where('id', $payment->bank_account_id)->first();
        if (!$bankAccount || !$bankAccount->glAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }
        $bankGLAccount = $bankAccount->glAccount;

        $this->validateAccounts(['5220']);
        $this->validateBalance($payment->payment_amount, $payment->payment_amount);

        $commissionExpenseAccount = ChartOfAccount::where('account_code', '5220')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $payment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'sales_agent_commission_payment',
            'reference_id' => $payment->id,
            'description' => 'Commission Payment #' . $payment->payment_number . ' - ' . $payment->agent->user->name,
            'total_debit' => $payment->payment_amount,
            'total_credit' => $payment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $commissionExpenseAccount->id,
            'description' => 'Commission expense - ' . $payment->agent->user->name,
            'debit_amount' => $payment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment from ' . $bankAccount->account_name,
            'debit_amount' => 0,
            'credit_amount' => $payment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for commission adjustment
     * Bonus = Company Expense, Penalty = Company Income
     * Usage: ApproveSalesAgentCommissionAdjustment listener
     */
    public function createCommissionAdjustmentJournal($adjustment)
    {
        $this->validateAccounts(['5220', '2400', '4300']);
        $this->validateBalance($adjustment->adjustment_amount, $adjustment->adjustment_amount);

        $commissionExpenseAccount = ChartOfAccount::where('account_code', '5220')->where('created_by', creatorId())->first();
        $commissionPayableAccount = ChartOfAccount::where('account_code', '2400')->where('created_by', creatorId())->first();
        $otherIncomeAccount = ChartOfAccount::where('account_code', '4300')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $adjustment->adjustment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'commission_adjustment',
            'reference_id' => $adjustment->id,
            'description' => 'Commission Adjustment (' . ucfirst($adjustment->adjustment_type) . ') - ' . $adjustment->agent->user->name,
            'total_debit' => $adjustment->adjustment_amount,
            'total_credit' => $adjustment->adjustment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        if ($adjustment->adjustment_type === 'bonus') {
            // Bonus = Company Expense
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $commissionExpenseAccount->id,
                'description' => 'Commission bonus - ' . $adjustment->adjustment_reason,
                'debit_amount' => $adjustment->adjustment_amount,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $commissionPayableAccount->id,
                'description' => 'Commission payable to agent',
                'debit_amount' => 0,
                'credit_amount' => $adjustment->adjustment_amount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        } elseif ($adjustment->adjustment_type === 'penalty') {
            // Penalty = Company Income
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $commissionPayableAccount->id,
                'description' => 'Commission deduction from agent',
                'debit_amount' => $adjustment->adjustment_amount,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $otherIncomeAccount->id,
                'description' => 'Commission penalty - ' . $adjustment->adjustment_reason,
                'debit_amount' => 0,
                'credit_amount' => $adjustment->adjustment_amount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        } else {
            // Correction = Can be positive or negative
            $amount = abs($adjustment->adjustment_amount);

            if ($adjustment->adjustment_amount > 0) {
                // Positive correction = Increase expense
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $commissionExpenseAccount->id,
                    'description' => 'Commission correction - ' . $adjustment->adjustment_reason,
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]);

                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $commissionPayableAccount->id,
                    'description' => 'Commission payable adjustment',
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]);
            } else {
                // Negative correction = Decrease expense
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $commissionPayableAccount->id,
                    'description' => 'Commission payable adjustment',
                    'debit_amount' => $amount,
                    'credit_amount' => 0,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]);

                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $commissionExpenseAccount->id,
                    'description' => 'Commission correction - ' . $adjustment->adjustment_reason,
                    'debit_amount' => 0,
                    'credit_amount' => $amount,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId()
                ]);
            }
        }

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function createHolidayzBookingPaymentJournal($booking, $bankAccountId)
    {
        $bankAccount = BankAccount::where('id', $bankAccountId)->where('created_by', $booking->created_by)->first();
        if (!$bankAccount || !$bankAccount->glAccount) {
            throw new \Exception("Bank account with GL account not found");
        }

        $requiredAccounts = ['4200'];
        if ($booking->tax_amount > 0) {
            $requiredAccounts[] = '2210';
        }
        $this->validateAccounts($requiredAccounts, $booking->created_by);
        $this->validateBalance($booking->paid_amount, $booking->total_amount);

        $hotelRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', $booking->created_by)->first();
        $taxAccount = ChartOfAccount::where('account_code', '2210')->where('created_by', $booking->created_by)->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $booking->booking_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'hotel_booking_payment',
            'reference_id' => $booking->id,
            'description' => 'Hotel Booking Payment: ' . $booking->booking_number,
            'total_debit' => $booking->paid_amount,
            'total_credit' => $booking->paid_amount,
            'status' => 'posted',
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankAccount->glAccount->id,
            'description' => 'Hotel booking payment received',
            'debit_amount' => $booking->paid_amount,
            'credit_amount' => 0,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $hotelRevenueAccount->id,
            'description' => 'Hotel booking revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $booking->total_amount - $booking->tax_amount,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        if ($booking->tax_amount > 0) {
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'description' => 'Sales tax collected',
                'debit_amount' => 0,
                'credit_amount' => $booking->tax_amount,
                'creator_id' => $booking->created_by,
                'created_by' => $booking->created_by
            ]);
        }

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function createPostFleetExpenseJournal($fleetExpense)
    {
        $bankGLAccount = BankAccount::where('id', $fleetExpense->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['5800']);
        $this->validateBalance($fleetExpense->amount, $fleetExpense->amount);

        $fleetExpenseAccount = ChartOfAccount::where('account_code', '5800')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $fleetExpense->expense_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'fleet_expense',
            'reference_id' => $fleetExpense->id,
            'description' => 'Fleet Expense - ' . ($fleetExpense->description ?? 'Fleet expense'),
            'total_debit' => $fleetExpense->amount,
            'total_credit' => $fleetExpense->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $fleetExpenseAccount->id,
            'description' => 'Fleet expense incurred',
            'debit_amount' => $fleetExpense->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment made',
            'debit_amount' => 0,
            'credit_amount' => $fleetExpense->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function eventBookingPaymentStatusJournal($booking, $bankAccountId)
    {
        $bankAccount = BankAccount::where('id', $bankAccountId)->where('created_by', $booking->created_by)->first();
        if (!$bankAccount || !$bankAccount->glAccount) {
            throw new \Exception("Bank account with GL account not found");
        }

        $this->validateAccounts(['4200'], $booking->created_by);
        $this->validateBalance($booking->amount, $booking->amount);

        $eventRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', $booking->created_by)->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $booking->start_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'event_booking_payment',
            'reference_id' => $booking->id,
            'description' => 'Event Booking Payment - ' . $booking->name,
            'total_debit' => $booking->amount,
            'total_credit' => $booking->amount,
            'status' => 'posted',
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankAccount->glAccount->id,
            'description' => 'Event booking payment received',
            'debit_amount' => $booking->amount,
            'credit_amount' => 0,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $eventRevenueAccount->id,
            'description' => 'Event service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $booking->amount,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function markCaseExpenseAsPaidJournal($caseExpense)
    {
        $bankGLAccount = BankAccount::where('id', $caseExpense->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $accountCodeMap = [
            'court_fee' => '5420',
            'filing_fee' => '5420',
            'advocate_fee' => '5420',
            'documentation' => '5310',
            'travel' => '5330',
            'expert_witness' => '5420',
            'investigation' => '5420',
            'miscellaneous' => '5800',
        ];

        $accountCode = $accountCodeMap[$caseExpense->expense_type] ?? '5800';
        $this->validateAccounts([$accountCode]);
        $this->validateBalance($caseExpense->amount, $caseExpense->amount);

        $expenseAccount = ChartOfAccount::where('account_code', $accountCode)->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $caseExpense->expense_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'case_expense',
            'reference_id' => $caseExpense->id,
            'description' => 'Case Expense - ' . ($caseExpense->legalCase->title ?? 'Legal case expense'),
            'total_debit' => $caseExpense->amount,
            'total_credit' => $caseExpense->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $expenseAccount->id,
            'description' => 'Case expense incurred',
            'debit_amount' => $caseExpense->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment made',
            'debit_amount' => 0,
            'credit_amount' => $caseExpense->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function feeReceiveAsClearedJournal($feeReceive)
    {
        $bankGLAccount = BankAccount::where('id', $feeReceive->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['4200']);
        $this->validateBalance($feeReceive->amount, $feeReceive->amount);

        $revenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $feeReceive->receive_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'fee_receive',
            'reference_id' => $feeReceive->id,
            'description' => 'Fee Received - ' . ($feeReceive->legalCase->title ?? 'Legal fee received'),
            'total_debit' => $feeReceive->amount,
            'total_credit' => $feeReceive->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Case fee payment received',
            'debit_amount' => $feeReceive->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $revenueAccount->id,
            'description' => 'Legal service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $feeReceive->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function membershipPlanAssignedJournal($payment)
    {
        $bankGLAccount = BankAccount::where('id', $payment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['4200']);
        $this->validateBalance($payment->fee, $payment->fee);

        $membershipRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $payment->date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'membership_plan_payment',
            'reference_id' => $payment->id,
            'description' => 'Membership Plan Payment - ' . $payment->member->user->name ?? null,
            'total_debit' => $payment->fee,
            'total_credit' => $payment->fee,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Membership plan payment received',
            'debit_amount' => $payment->fee,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $membershipRevenueAccount->id,
            'description' => 'Membership service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $payment->fee,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function parkingBookingPaymentsJournal($booking, $bankAccountId)
    {
        $bankAccount = BankAccount::where('id', $bankAccountId)->where('created_by', $booking->created_by)->first();
        if (!$bankAccount || !$bankAccount->glAccount) {
            throw new \Exception("Bank account with GL account not found");
        }

        $this->validateAccounts(['4200'], $booking->created_by);
        $this->validateBalance($booking->total_amount, $booking->total_amount);

        $parkingRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', $booking->created_by)->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $booking->booking_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'parking_booking_payment',
            'reference_id' => $booking->id,
            'description' => 'Parking Booking Payment - ' . $booking->customer_name,
            'total_debit' => $booking->total_amount,
            'total_credit' => $booking->total_amount,
            'status' => 'posted',
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankAccount->glAccount->id,
            'description' => 'Parking booking payment received',
            'debit_amount' => $booking->total_amount,
            'credit_amount' => 0,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $parkingRevenueAccount->id,
            'description' => 'Parking service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $booking->total_amount,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function vehicleBookingPaymentsJournal($booking, $bankAccountId)
    {
        $bankAccount = BankAccount::where('id', $bankAccountId)->where('created_by', $booking->created_by)->first();
        if (!$bankAccount || !$bankAccount->glAccount) {
            throw new \Exception("Bank account with GL account not found");
        }

        $this->validateAccounts(['4200'], $booking->created_by);
        $this->validateBalance($booking->total_amount, $booking->total_amount);

        $vehicleRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', $booking->created_by)->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $booking->booking_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'vehicle_booking_payment',
            'reference_id' => $booking->id,
            'description' => 'Vehicle Booking Payment - ' . $booking->booking_number,
            'total_debit' => $booking->total_amount,
            'total_credit' => $booking->total_amount,
            'status' => 'posted',
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankAccount->glAccount->id,
            'description' => 'Vehicle booking payment received',
            'debit_amount' => $booking->total_amount,
            'credit_amount' => 0,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $vehicleRevenueAccount->id,
            'description' => 'Vehicle service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $booking->total_amount,
            'creator_id' => $booking->created_by,
            'created_by' => $booking->created_by
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function laundryExpenseStatusJournal($expense)
    {
        $bankGLAccount = BankAccount::where('id', $expense->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }
        $this->validateAccounts(['5800']);
        $this->validateBalance($expense->amount, $expense->amount);

        $laundryExpenseAccount = ChartOfAccount::where('account_code', '5800')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $expense->expense_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'laundry_expense',
            'reference_id' => $expense->id,
            'description' => 'Laundry Expense - ' . ($expense->description ?? 'Laundry expense'),
            'total_debit' => $expense->amount,
            'total_credit' => $expense->amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $laundryExpenseAccount->id,
            'description' => 'Laundry expense incurred',
            'debit_amount' => $expense->amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Payment made',
            'debit_amount' => 0,
            'credit_amount' => $expense->amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function laundryPaymentStatusJournal($laundryRequest, $bankAccountId)
    {
        $bankGLAccount = BankAccount::where('id', $bankAccountId)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['4200']);
        $this->validateBalance($laundryRequest->total, $laundryRequest->total);

        $laundryRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', $laundryRequest->created_by)->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $laundryRequest->delivery_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'laundry_payment',
            'reference_id' => $laundryRequest->id,
            'description' => 'Laundry Payment - ' . $laundryRequest->request_number,
            'total_debit' => $laundryRequest->total,
            'total_credit' => $laundryRequest->total,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => $laundryRequest->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Laundry payment received from ' . $laundryRequest->name,
            'debit_amount' => $laundryRequest->total,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => $laundryRequest->created_by
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $laundryRevenueAccount->id,
            'description' => 'Laundry service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $laundryRequest->total,
            'creator_id' => Auth::id(),
            'created_by' => $laundryRequest->created_by
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function medicalOrderPaymentJournal($medicalOrderPayment)
    {
        $bankGLAccount = BankAccount::where('id', $medicalOrderPayment->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['4200']);
        $this->validateBalance($medicalOrderPayment->payment_amount, $medicalOrderPayment->payment_amount);

        $medicalRevenueAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();
        $journalEntry = JournalEntry::create([
            'journal_date' => $medicalOrderPayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'medical_order_payment',
            'reference_id' => $medicalOrderPayment->id,
            'description' => 'Medical Lab Payment #' . $medicalOrderPayment->payment_number,
            'total_debit' => $medicalOrderPayment->payment_amount,
            'total_credit' => $medicalOrderPayment->payment_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Medical lab payment received',
            'debit_amount' => $medicalOrderPayment->payment_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $medicalRevenueAccount->id,
            'description' => 'Medical lab service revenue earned',
            'debit_amount' => 0,
            'credit_amount' => $medicalOrderPayment->payment_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function completeWarrantyClaimJournal($warrantyClaim)
    {
        $bankGLAccount = BankAccount::where('id', $warrantyClaim->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['5430', '4300']);

        $warrantyExpenseAccount = ChartOfAccount::where('account_code', '5430')->where('created_by', creatorId())->first();
        $warrantyRevenueAccount = ChartOfAccount::where('account_code', '4300')->where('created_by', creatorId())->first();

        $totalDebit = $warrantyClaim->repair_cost + $warrantyClaim->customer_charge_amount;
        $totalCredit = $warrantyClaim->repair_cost + $warrantyClaim->customer_charge_amount;
        $this->validateBalance($totalDebit, $totalCredit);

        $journalEntry = JournalEntry::create([
            'journal_date' => $warrantyClaim->solution_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'warranty_claim',
            'reference_id' => $warrantyClaim->id,
            'description' => 'Warranty Claim Completed - ' . $warrantyClaim->claim_number,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Warranty Expense
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $warrantyExpenseAccount->id,
            'description' => 'Warranty repair cost',
            'debit_amount' => $warrantyClaim->repair_cost,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Bank (repair cost paid)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Warranty repair payment',
            'debit_amount' => 0,
            'credit_amount' => $warrantyClaim->repair_cost,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // If customer charged
        if ($warrantyClaim->customer_charge_amount > 0) {
            // Debit: Bank (customer payment received)
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $bankGLAccount->id,
                'description' => 'Warranty customer charge received',
                'debit_amount' => $warrantyClaim->customer_charge_amount,
                'credit_amount' => 0,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);

            // Credit: Warranty Revenue
            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $warrantyRevenueAccount->id,
                'description' => 'Warranty service revenue',
                'debit_amount' => 0,
                'credit_amount' => $warrantyClaim->customer_charge_amount,
                'creator_id' => Auth::id(),
                'created_by' => creatorId()
            ]);
        }

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    public function approvePettyCashJournal($pettyCash){
        $bankGLAccount = BankAccount::where('id', $pettyCash->bank_account_id)->first()->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['1005']);
        $this->validateBalance($pettyCash->added_amount, $pettyCash->added_amount);

        $pettyCashAccount = ChartOfAccount::where('account_code', '1005')->where('created_by', creatorId())->first();

        $journalEntry = JournalEntry::create([
            'journal_date' => $pettyCash->date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'petty_cash',
            'reference_id' => $pettyCash->id,
            'description' => 'Petty Cash Expense - ' . ($pettyCash->description ?? 'Petty cash transaction'),
            'total_debit' => $pettyCash->added_amount,
            'total_credit' => $pettyCash->added_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Expense incurred',
            'debit_amount' => $pettyCash->added_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $pettyCashAccount->id,
            'description' => 'Payment from petty cash',
            'debit_amount' => 0,
            'credit_amount' => $pettyCash->added_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }

    /**
     * Creates journal entry for project payment: Dr: Bank, Cr: A/R
     * Usage: ProjectPaymentController->post() after posting payment
     */
    public function createProjectPaymentJournal($projectPayment)
    {
        $bankGLAccount = $projectPayment->bankAccount->glAccount;
        if (!$bankGLAccount) {
            throw new \Exception("Bank account must have a GL account assigned");
        }

        $this->validateAccounts(['4200']);
        $arAccount = ChartOfAccount::where('account_code', '4200')->where('created_by', creatorId())->first();

        // Validate amounts balance
        $this->validateBalance($projectPayment->total_amount, $projectPayment->total_amount);

        $journalEntry = JournalEntry::create([
            'journal_date' => $projectPayment->payment_date ?? now(),
            'entry_type' => 'automatic',
            'reference_type' => 'project_payment',
            'reference_id' => $projectPayment->id,
            'description' => 'Project Payment #' . $projectPayment->payment_number . ' - ' . $projectPayment->project->name,
            'total_debit' => $projectPayment->total_amount,
            'total_credit' => $projectPayment->total_amount,
            'status' => 'posted',
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Debit: Specific Bank Account (from GL Account)
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $bankGLAccount->id,
            'description' => 'Project payment received from ' . $projectPayment->customer->name,
            'debit_amount' => $projectPayment->total_amount,
            'credit_amount' => 0,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        // Credit: Accounts Receivable
        JournalEntryItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccount->id,
            'description' => 'Project payment - ' . $projectPayment->project->name,
            'debit_amount' => 0,
            'credit_amount' => $projectPayment->total_amount,
            'creator_id' => Auth::id(),
            'created_by' => creatorId()
        ]);

        try {
            UpdateBudgetSpending::dispatch($journalEntry);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->updateAccountBalances($journalEntry);
        return $journalEntry;
    }
}
