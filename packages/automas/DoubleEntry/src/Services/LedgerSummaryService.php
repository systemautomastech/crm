<?php

namespace Automas\DoubleEntry\Services;

use Automas\Account\Models\ChartOfAccount;
use Automas\Account\Models\OpeningBalance;
use Automas\Account\Models\JournalEntry;
use Automas\Account\Models\JournalEntryItem;
use Illuminate\Support\Facades\DB;

class LedgerSummaryService
{
    public function getAllLedgerEntries($fromDate = null, $toDate = null, $accountId = null, $paginate = true)
    {
        $query = JournalEntryItem::select(
                'journal_entry_items.id',
                'journal_entries.created_at',
                'journal_entries.journal_date',
                'journal_entries.reference_type',
                'journal_entries.description as journal_description',
                'journal_entry_items.description',
                'journal_entry_items.debit_amount',
                'journal_entry_items.credit_amount',
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name'
            )
            ->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_items.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.created_by', creatorId())
            ->when($fromDate, fn($q) => $q->where('journal_entries.journal_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->where('journal_entries.journal_date', '<=', $toDate))
            ->when($accountId, fn($q) => $q->where('journal_entry_items.account_id', $accountId))
            ->when(request('search'), fn($q) => $q->where(function($query) {
                $query->where('chart_of_accounts.account_code', 'like', '%' . request('search') . '%')
                      ->orWhere('chart_of_accounts.account_name', 'like', '%' . request('search') . '%')
                      ->orWhere('journal_entry_items.description', 'like', '%' . request('search') . '%')
                      ->orWhere('journal_entries.description', 'like', '%' . request('search') . '%');
            }))
            ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest());

        if ($paginate) {
            return $query->paginate(request('per_page', 10))->withQueryString();
        }

        return $query->get();
    }


}
