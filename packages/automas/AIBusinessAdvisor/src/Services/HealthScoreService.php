<?php

namespace Automas\AIBusinessAdvisor\Services;

class HealthScoreService
{
    /**
     * Calculate overall health score and 5 sub-scores from aggregated metrics.
     */
    public function calculate(array $metrics): array
    {
        $financial  = $this->scoreFinancial($metrics['financial'] ?? []);
        $team       = $this->scoreTeam($metrics['hrm'] ?? []);
        $sales      = $this->scoreSales($metrics['sales'] ?? []);
        $projects   = $this->scoreProjects($metrics['projects'] ?? []);
        $operations = $this->scoreOperations($metrics['pos'] ?? [], $metrics['inventory'] ?? []);

        $overall = round(
            ($financial  * 0.30) +
            ($team       * 0.20) +
            ($sales      * 0.25) +
            ($projects   * 0.15) +
            ($operations * 0.10),
            2
        );

        return [
            'score'            => $overall,
            'financial_score'  => $financial,
            'team_score'       => $team,
            'sales_score'      => $sales,
            'project_score'    => $projects,
            'operations_score' => $operations,
        ];
    }

    /**
     * Financial health score (0-100).
     */
    private function scoreFinancial(array $f): float
    {
        $score = 50; // base

        // Profit margin: ideal > 20%
        $profitMargin = $f['profit_margin_percent'] ?? 0;
        if ($profitMargin >= 20) {
            $score += 20;
        } elseif ($profitMargin >= 10) {
            $score += 10;
        } elseif ($profitMargin < 0) {
            $score -= 20;
        }

        // Revenue growth
        $revenueGrowth = $f['revenue_growth_percent'] ?? 0;
        if ($revenueGrowth >= 10) {
            $score += 15;
        } elseif ($revenueGrowth >= 0) {
            $score += 5;
        } else {
            $score -= 10;
        }

        // Overdue invoices
        $overdueCount = $f['overdue_invoices_count'] ?? 0;
        if ($overdueCount === 0) {
            $score += 15;
        } elseif ($overdueCount <= 3) {
            $score += 5;
        } else {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }

    /**
     * Team / HRM health score (0-100).
     */
    private function scoreTeam(array $h): float
    {
        $score = 50;

        // Attendance rate: ideal > 90%
        $attendanceRate = $h['attendance_rate_percent'] ?? 0;
        if ($attendanceRate >= 90) {
            $score += 25;
        } elseif ($attendanceRate >= 80) {
            $score += 10;
        } else {
            $score -= 15;
        }

        // Pending leaves (admin responsiveness)
        $pendingLeaves = $h['pending_leave_requests'] ?? 0;
        if ($pendingLeaves === 0) {
            $score += 15;
        } elseif ($pendingLeaves <= 3) {
            $score += 5;
        } else {
            $score -= 5;
        }

        // Growth (new hires)
        if (($h['new_hires_this_month'] ?? 0) > 0) {
            $score += 10;
        }

        return max(0, min(100, $score));
    }

    /**
     * Sales / CRM health score (0-100).
     */
    private function scoreSales(array $s): float
    {
        $score = 50;

        // Conversion rate: ideal > 20%
        $conversionRate = $s['conversion_rate_percent'] ?? 0;
        if ($conversionRate >= 20) {
            $score += 20;
        } elseif ($conversionRate >= 10) {
            $score += 10;
        } else {
            $score -= 10;
        }

        // Pipeline value (has active deals)
        if (($s['pipeline_value'] ?? 0) > 0) {
            $score += 15;
        }

        // Inactive leads (bad)
        $inactiveLeads = $s['inactive_leads_30_days'] ?? 0;
        if ($inactiveLeads === 0) {
            $score += 15;
        } elseif ($inactiveLeads <= 5) {
            $score += 5;
        } else {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }

    /**
     * Project health score (0-100).
     */
    private function scoreProjects(array $p): float
    {
        $score = 50;

        // Task completion rate
        $taskCompletion = $p['task_completion_rate_percent'] ?? 0;
        if ($taskCompletion >= 85) {
            $score += 25;
        } elseif ($taskCompletion >= 70) {
            $score += 10;
        } else {
            $score -= 10;
        }

        // Delayed projects
        $delayedProjects = $p['delayed_projects'] ?? 0;
        if ($delayedProjects === 0) {
            $score += 25;
        } elseif ($delayedProjects <= 1) {
            $score += 10;
        } else {
            $score -= ($delayedProjects * 5);
        }

        return max(0, min(100, $score));
    }

    /**
     * Operations health score (POS + Inventory) (0-100).
     */
    private function scoreOperations(array $pos, array $inv): float
    {
        $score = 50;

        // POS growth
        $posGrowth = $pos['pos_growth_percent'] ?? 0;
        if ($posGrowth >= 10) {
            $score += 20;
        } elseif ($posGrowth >= 0) {
            $score += 10;
        } else {
            $score -= 10;
        }

        // Stock health
        $outOfStock = $inv['out_of_stock_count'] ?? 0;
        if ($outOfStock === 0) {
            $score += 20;
        } elseif ($outOfStock <= 2) {
            $score += 5;
        } else {
            $score -= ($outOfStock * 3);
        }

        // Low stock warning
        if (($inv['low_stock_count'] ?? 0) > 5) {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }
}
