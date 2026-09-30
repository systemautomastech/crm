<?php

namespace Automas\DoubleEntry\Services;

use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    public function generateTrialBalance($fromDate, $toDate)
    {
        $accounts = DB::select("
            SELECT
                coa.id,
                coa.account_code,
                coa.account_name,
                coa.normal_balance,
                coa.current_balance as balance
            FROM chart_of_accounts coa
            WHERE coa.is_active = 1
              AND coa.created_by = ?
            ORDER BY coa.account_code ASC
        ", [creatorId()]);

        $totalDebit = 0;
        $totalCredit = 0;
        $accountsList = [];

        foreach($accounts as $account) {
            $balance = (float)$account->balance;

            if (abs($balance) > 0.01) {
                $debit = 0;
                $credit = 0;

                if ($balance > 0) {
                    if ($account->normal_balance === 'debit') {
                        $debit = $balance;
                        $totalDebit += $debit;
                    } else {
                        $credit = $balance;
                        $totalCredit += $credit;
                    }
                } else {
                    // Negative balance goes to opposite side
                    if ($account->normal_balance === 'debit') {
                        $credit = abs($balance);
                        $totalCredit += $credit;
                    } else {
                        $debit = abs($balance);
                        $totalDebit += $debit;
                    }
                }

                $accountsList[] = [
                    'id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'debit' => $debit,
                    'credit' => $credit
                ];
            }
        }

        return [
            'accounts' => $accountsList,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
    }
}
