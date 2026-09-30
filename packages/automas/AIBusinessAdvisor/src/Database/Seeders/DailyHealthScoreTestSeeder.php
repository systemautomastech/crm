<?php

namespace Automas\AIBusinessAdvisor\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Automas\Account\Models\JournalEntry;
use Automas\Account\Models\BankAccount;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\Pos\Models\Pos;
use Automas\Pos\Models\PosItem;
use Automas\Pos\Models\PosPayment;
use Automas\Hrm\Models\Employee;
use Automas\Hrm\Models\Attendance;
use Automas\Taskly\Models\Project;
use Automas\Taskly\Models\ProjectTask;
use Automas\ProductService\Models\WarehouseStock;
use Automas\AIBusinessAdvisor\Models\AiBusinessHealthScore;
use Automas\AIBusinessAdvisor\Models\AiBusinessInsight;
use Automas\AIBusinessAdvisor\Models\AiBusinessRecommendation;
use Automas\AIBusinessAdvisor\Models\AiBusinessAlert;
use Automas\AIBusinessAdvisor\Services\DataAggregationService;
use Automas\AIBusinessAdvisor\Services\HealthScoreService;

class DailyHealthScoreTestSeeder extends Seeder
{
    /**
     * Run the seeder to generate multi-day test data and calculate daily health scores.
     *
     * Usage:
     *   php artisan db:seed --class="Automas\AIBusinessAdvisor\Database\Seeders\DailyHealthScoreTestSeeder"
     *
     * This seeder creates DIFFERENT business data for each of the last N days,
     * then calculates REAL health scores using the actual services.
     * It uses Carbon::setTestNow() to simulate each day during calculation.
     */
    public function run($userId = null): void
    {
        $silent = !app()->runningInConsole();
        if ($silent) {
            ob_start();
        }

        $userId = $userId ?? 2;    // Use passed user ID or fallback to 2
        $days = 7;                 // Number of days to generate (default: last 7 days)
        $skipAiApi = true;         // Skip AI API call — create mock insights instead

        echo "🎯 Starting Daily Health Score Test Seeder for User ID: $userId\n";
        echo "📅 Generating $days days of varied test data...\n\n";

        // Store real "now" so we can restore it later
        $realNow = Carbon::now();

        // Pre-scenarios: define different business situations per day
        $scenarios = $this->getScenarios($days);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $realNow->copy()->subDays($i);
            $scenario = $scenarios[$i];

            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "📆 DAY {$date->toDateString()} — {$scenario['label']}\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

            // 1. Fake "today" so all queries think this date is "now"
            Carbon::setTestNow($date);

            // 2. Clean up existing health score for this day (so we can re-run)
            $this->cleanupExistingData($userId, $date);

            // 3. Generate business data for this specific day
            $this->generateDailyFinancialData($userId, $date, $scenario);
            $this->generateDailySalesData($userId, $date, $scenario);
            $this->generateDailyPOSData($userId, $date, $scenario);
            $this->generateDailyHRMData($userId, $date, $scenario);
            $this->generateDailyProjectData($userId, $date, $scenario);
            $this->generateDailyInventoryData($userId, $date, $scenario);

            // 4. Calculate REAL metrics & health score using actual services
            $aggregator = app(DataAggregationService::class);
            $scorer = app(HealthScoreService::class);

            $metrics = $aggregator->getAllMetrics($userId);
            $scoreData = $scorer->calculate($metrics);

            // 5. Save health score
            $healthScore = AiBusinessHealthScore::create([
                'score'            => $scoreData['score'],
                'financial_score'  => $scoreData['financial_score'],
                'team_score'       => $scoreData['team_score'],
                'sales_score'      => $scoreData['sales_score'],
                'project_score'    => $scoreData['project_score'],
                'operations_score' => $scoreData['operations_score'],
                'trend'            => 'stable', // will be updated later
                'raw_metrics'      => $metrics,
                'analysis_date'    => $date->toDateString(),
                'created_by'       => $userId,
            ]);

            // 6. Create insights / recommendations / alerts
            if ($skipAiApi) {
                $this->generateMockInsights($healthScore, $scoreData, $userId, $date);
            }

            echo "   ✅ Health Score: {$scoreData['score']} " .
                 "(F:{$scoreData['financial_score']} T:{$scoreData['team_score']} " .
                 "S:{$scoreData['sales_score']} P:{$scoreData['project_score']} O:{$scoreData['operations_score']})\n\n";
        }

        // Restore real "now"
        Carbon::setTestNow($realNow);

        // Update trends by comparing each day with previous day
        $this->updateTrends($userId, $days);

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ Test data generation complete!\n";
        echo "📊 Check ai_business_health_scores table for $days days of records.\n";
        echo "🚀 You can now test the AI Advisor dashboard and history.\n";

        if ($silent) {
            ob_end_clean();
        }
    }

    /**
     * Define business scenarios for each day.
     */
    private function getScenarios(int $days): array
    {
        return [
            6 => ['label' => '🔴 Bad Day (Low revenue, high expenses, poor attendance)', 'revenue' => 3000,  'expense' => 8000,  'attendanceRate' => 60, 'posCount' => 1, 'invoiceCount' => 1, 'tasksCompleted' => 20],
            5 => ['label' => '🟠 Poor Day (Below average performance)',                'revenue' => 5000,  'expense' => 7000,  'attendanceRate' => 70, 'posCount' => 2, 'invoiceCount' => 1, 'tasksCompleted' => 35],
            4 => ['label' => '🟡 Average Day (Steady but not great)',                  'revenue' => 8000,  'expense' => 6000,  'attendanceRate' => 80, 'posCount' => 3, 'invoiceCount' => 2, 'tasksCompleted' => 50],
            3 => ['label' => '🔵 Good Day (Above average, solid numbers)',             'revenue' => 12000, 'expense' => 5000,  'attendanceRate' => 88, 'posCount' => 4, 'invoiceCount' => 2, 'tasksCompleted' => 65],
            2 => ['label' => '🟢 Great Day (Strong revenue, low expenses)',            'revenue' => 15000, 'expense' => 4000,  'attendanceRate' => 92, 'posCount' => 5, 'invoiceCount' => 3, 'tasksCompleted' => 80],
            1 => ['label' => '⭐ Excellent Day (High performance across board)',        'revenue' => 18000, 'expense' => 3000,  'attendanceRate' => 96, 'posCount' => 6, 'invoiceCount' => 4, 'tasksCompleted' => 90],
            0 => ['label' => '🏆 Outstanding Day (Peak business performance)',         'revenue' => 22000, 'expense' => 2500,  'attendanceRate' => 100, 'posCount' => 8, 'invoiceCount' => 5, 'tasksCompleted' => 100],
        ];
    }

    /**
     * Clean up existing health score and child records for a specific date.
     */
    private function cleanupExistingData(int $userId, Carbon $date): void
    {
        $existing = AiBusinessHealthScore::where('created_by', $userId)
            ->whereDate('analysis_date', $date->toDateString())
            ->first();

        if ($existing) {
            AiBusinessInsight::where('health_score_id', $existing->id)->delete();
            AiBusinessRecommendation::where('health_score_id', $existing->id)->delete();
            AiBusinessAlert::where('health_score_id', $existing->id)->delete();
            $existing->delete();
            echo "   ♻️ Cleaned up existing record for {$date->toDateString()}\n";
        }
    }

    /**
     * Generate financial data (Journal Entries) for a specific day.
     */
    private function generateDailyFinancialData(int $userId, Carbon $date, array $scenario): void
    {
        $revenue = $scenario['revenue'];
        $expense = $scenario['expense'];

        JournalEntry::create([
            'journal_number' => 'JE-' . $date->format('Ymd') . '-' . uniqid(),
            'journal_date'   => $date->toDateString(),
            'entry_type'     => 'automatic',
            'reference_type' => 'sales_invoice',
            'reference_id'   => rand(100, 999),
            'description'    => 'Daily Revenue - ' . $date->toDateString(),
            'total_debit'    => $revenue,
            'total_credit'   => $revenue,
            'status'         => 'posted',
            'creator_id'     => $userId,
            'created_by'     => $userId,
        ]);

        JournalEntry::create([
            'journal_number' => 'JE-' . $date->format('Ymd') . '-' . uniqid(),
            'journal_date'   => $date->toDateString(),
            'entry_type'     => 'automatic',
            'reference_type' => 'expense',
            'reference_id'   => rand(100, 999),
            'description'    => 'Daily Expense - ' . $date->toDateString(),
            'total_debit'    => $expense,
            'total_credit'   => $expense,
            'status'         => 'posted',
            'creator_id'     => $userId,
            'created_by'     => $userId,
        ]);

        // Customer payment
        $payment = (int) ($revenue * 0.6);
        JournalEntry::create([
            'journal_number' => 'JE-' . $date->format('Ymd') . '-' . uniqid(),
            'journal_date'   => $date->toDateString(),
            'entry_type'     => 'automatic',
            'reference_type' => 'customer_payment',
            'reference_id'   => rand(100, 999),
            'description'    => 'Customer Payment - ' . $date->toDateString(),
            'total_debit'    => $payment,
            'total_credit'   => $payment,
            'status'         => 'posted',
            'creator_id'     => $userId,
            'created_by'     => $userId,
        ]);

        echo "   💰 Revenue: $revenue | Expense: $expense | Payment: $payment\n";
    }

    /**
     * Generate Sales Invoices for a specific day.
     */
    private function generateDailySalesData(int $userId, Carbon $date, array $scenario): void
    {
        $product = ProductServiceItem::where('created_by', $userId)->first();
        if (!$product) {
            echo "   ⚠️ No product found, skipping sales invoices\n";
            return;
        }

        $invoiceCount = $scenario['invoiceCount'];
        for ($i = 0; $i < $invoiceCount; $i++) {
            $quantity = rand(1, 5);
            $unitPrice = rand(100, 500);
            $subtotal = $quantity * $unitPrice;
            $tax = $subtotal * 0.10;
            $total = $subtotal + $tax;

            $invoice = SalesInvoice::create([
                'invoice_number' => 'INV-' . $date->format('Ymd') . '-' . uniqid(),
                'invoice_date'   => $date->toDateString(),
                'due_date'       => $date->copy()->addDays(rand(15, 45))->toDateString(),
                'customer_id'    => rand(5, 10),
                'warehouse_id'   => rand(1, 3),
                'subtotal'       => $subtotal,
                'discount_amount'=> 0,
                'tax_amount'     => $tax,
                'total_amount'   => $total,
                'paid_amount'    => 0,
                'balance_amount' => $total,
                'status'         => 'posted',
                'creator_id'     => $userId,
                'created_by'     => $userId,
            ]);

            SalesInvoiceItem::create([
                'invoice_id'          => $invoice->id,
                'product_id'          => $product->id,
                'quantity'            => $quantity,
                'unit_price'          => $unitPrice,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'tax_percentage'      => 10,
                'tax_amount'          => $tax,
                'total_amount'        => $total,
            ]);
        }

        echo "   📈 Created $invoiceCount sales invoices\n";
    }

    /**
     * Generate POS Transactions for a specific day.
     */
    private function generateDailyPOSData(int $userId, Carbon $date, array $scenario): void
    {
        $bankAccount = BankAccount::where('created_by', $userId)->first();
        $product = ProductServiceItem::where('created_by', $userId)->first();

        if (!$bankAccount || !$product) {
            echo "   ⚠️ Missing bank account or products\n";
            return;
        }

        $posCount = $scenario['posCount'];
        for ($i = 0; $i < $posCount; $i++) {
            $quantity = rand(1, 10);
            $unitPrice = rand(50, 300);
            $itemTotal = $quantity * $unitPrice;

            $pos = Pos::create([
                'sale_number'     => 'POS-' . $date->format('Ymd') . '-' . uniqid(),
                'pos_date'        => $date->toDateString(),
                'customer_id'     => rand(5, 10),
                'warehouse_id'    => rand(1, 3),
                'status'          => 'completed',
                'creator_id'      => $userId,
                'created_by'      => $userId,
            ]);

            PosItem::create([
                'pos_id'              => $pos->id,
                'product_id'          => $product->id,
                'quantity'            => $quantity,
                'price'               => $unitPrice,
                'subtotal'            => $itemTotal,
                'tax_ids'             => json_encode([1]),
                'tax_amount'          => $itemTotal * 0.05,
                'total_amount'        => $itemTotal * 1.05,
                'item_discount_value' => 0,
                'item_discount_amount'=> 0,
                'creator_id'          => $userId,
                'created_by'          => $userId,
            ]);

            PosPayment::create([
                'pos_id'          => $pos->id,
                'amount'          => $itemTotal * 1.05,
                'discount'        => 0,
                'discount_amount' => 0,
                'creator_id'      => $userId,
                'created_by'      => $userId,
            ]);
        }

        echo "   🛒 Created $posCount POS transactions\n";
    }

    /**
     * Generate HRM Data (Attendance) for a specific day.
     */
    private function generateDailyHRMData(int $userId, Carbon $date, array $scenario): void
    {
        $employees = Employee::where('created_by', $userId)->limit(10)->get();

        if ($employees->isEmpty()) {
            echo "   ⚠️ No employees found\n";
            return;
        }

        $targetRate = $scenario['attendanceRate'];
        $presentCount = 0;

        foreach ($employees as $index => $employee) {
            // Decide present/absent based on target rate
            $rand = rand(1, 100);
            $status = ($rand <= $targetRate) ? 'present' : 'absent';

            $clockIn = $date->copy()->setHour(rand(8, 10))->setMinute(rand(0, 59))->format('Y-m-d H:i:s');
            $clockOut = $status === 'present'
                ? $date->copy()->setHour(rand(17, 19))->setMinute(rand(0, 59))->format('Y-m-d H:i:s')
                : null;

            Attendance::create([
                'employee_id'     => $employee->user_id,
                'shift_id'        => 1,
                'date'            => $date->toDateString(),
                'status'          => $status,
                'clock_in'        => $clockIn,
                'clock_out'       => $clockOut,
                'creator_id'      => $userId,
                'created_by'      => $userId,
            ]);

            if ($status === 'present') {
                $presentCount++;
            }
        }

        $actualRate = round(($presentCount / $employees->count()) * 100, 1);
        echo "   👥 Attendance: $presentCount/{$employees->count()} present ({$actualRate}%)\n";
    }

    /**
     * Generate Project Data for a specific day.
     */
    private function generateDailyProjectData(int $userId, Carbon $date, array $scenario): void
    {
        $projects = Project::where('created_by', $userId)->where('status', 'Ongoing')->limit(3)->get();

        if ($projects->isEmpty()) {
            echo "   ⚠️ No ongoing projects found\n";
            return;
        }

        $tasksCreated = 0;
        $completedRate = $scenario['tasksCompleted'];

        foreach ($projects as $project) {
            $taskCount = rand(2, 4);
            for ($i = 0; $i < $taskCount; $i++) {
                $isCompleted = rand(1, 100) <= $completedRate;

                ProjectTask::create([
                    'project_id'   => $project->id,
                    'milestone_id' => null,
                    'title'        => 'Test Task ' . uniqid(),
                    'description'  => 'Auto-generated for ' . $date->toDateString(),
                    'assigned_to'  => json_encode([rand(1, 10)]),
                    'priority'     => ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])],
                    'duration'     => rand(1, 8) . ' hours',
                    'stage_id'     => $isCompleted ? 4 : rand(1, 3), // stage 4 = Done (complete=1)
                    'creator_id'   => $userId,
                    'created_by'   => $userId,
                ]);
                $tasksCreated++;
            }
        }

        echo "   📋 Created $tasksCreated project tasks (completion target: {$completedRate}%)\n";
    }

    /**
     * Generate Inventory Data for a specific day.
     */
    private function generateDailyInventoryData(int $userId, Carbon $date, array $scenario): void
    {
        $products = ProductServiceItem::where('created_by', $userId)->limit(5)->get();

        if ($products->isEmpty()) {
            echo "   ⚠️ No products found\n";
            return;
        }

        $updatedCount = 0;
        foreach ($products as $product) {
            $warehouseId = rand(1, 3);
            // Vary stock levels: lower stock on bad days, higher on good days
            $baseStock = rand(5, 50);
            if ($scenario['revenue'] < 6000) {
                $baseStock = rand(0, 15); // low stock on bad days
            } elseif ($scenario['revenue'] > 15000) {
                $baseStock = rand(30, 100); // high stock on good days
            }

            $stock = WarehouseStock::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($stock) {
                $stock->update([
                    'quantity'   => $baseStock,
                    'updated_at' => $date,
                ]);
            } else {
                WarehouseStock::create([
                    'product_id'   => $product->id,
                    'warehouse_id' => $warehouseId,
                    'quantity'     => $baseStock,
                ]);
            }
            $updatedCount++;
        }

        echo "   📦 Updated inventory for $updatedCount products\n";
    }

    /**
     * Generate mock insights, recommendations and alerts based on the score.
     */
    private function generateMockInsights(AiBusinessHealthScore $healthScore, array $scoreData, int $userId, Carbon $date): void
    {
        $insights_data = [
            'positive' => [
                ['title' => 'Strong Revenue Growth', 'description' => 'Revenue increased by 15% this period', 'module' => 'account'],
                ['title' => 'Excellent Team Attendance', 'description' => 'Team attendance rate is above 95%', 'module' => 'hrm'],
                ['title' => 'High Sales Performance', 'description' => 'Sales targets exceeded by 20%', 'module' => 'sales'],
                ['title' => 'Efficient Project Execution', 'description' => 'Projects completed on schedule with quality metrics', 'module' => 'projects'],
                ['title' => 'Inventory Optimization', 'description' => 'Stock levels are well-balanced and efficient', 'module' => 'inventory'],
            ],
            'warning' => [
                ['title' => 'Increasing Operating Costs', 'description' => 'Operating expenses have risen by 12% compared to last week', 'module' => 'account'],
                ['title' => 'Declining Team Attendance', 'description' => 'Attendance rate dropped to 75%, monitor for trends', 'module' => 'hrm'],
                ['title' => 'Below Target Sales', 'description' => 'Current sales are 18% below monthly target', 'module' => 'sales'],
                ['title' => 'Project Delays Detected', 'description' => 'Some tasks are behind schedule by 2-3 days', 'module' => 'projects'],
                ['title' => 'Low Stock Alert', 'description' => 'Several products approaching minimum stock levels', 'module' => 'inventory'],
            ],
            'critical' => [
                ['title' => 'Critical Cash Flow Issue', 'description' => 'Cash outflow significantly exceeds inflow', 'module' => 'account'],
                ['title' => 'Severe Attendance Crisis', 'description' => 'Attendance rate has dropped below 60%', 'module' => 'hrm'],
                ['title' => 'Sales Performance Crisis', 'description' => 'Sales are 40% below target with negative trend', 'module' => 'sales'],
                ['title' => 'Critical Project Delays', 'description' => 'Multiple projects are significantly behind schedule', 'module' => 'projects'],
                ['title' => 'Critical Stock Shortage', 'description' => 'Multiple products out of stock affecting sales', 'module' => 'inventory'],
            ],
        ];

        $recommendations_data = [
            'high' => [
                ['recommendation' => 'Implement cost control measures', 'reason' => 'Operating expenses are growing faster than revenue', 'module' => 'account'],
                ['recommendation' => 'Review and adjust workforce scheduling', 'reason' => 'Attendance issues are impacting productivity', 'module' => 'hrm'],
                ['recommendation' => 'Launch targeted sales campaign', 'reason' => 'Sales performance needs immediate boost', 'module' => 'sales'],
                ['recommendation' => 'Reallocate project resources', 'reason' => 'Current resource allocation is inadequate', 'module' => 'projects'],
                ['recommendation' => 'Accelerate inventory replenishment', 'reason' => 'Stock levels are critically low', 'module' => 'inventory'],
            ],
            'medium' => [
                ['recommendation' => 'Review invoice payment terms', 'reason' => 'Payment collection efficiency can be improved', 'module' => 'account'],
                ['recommendation' => 'Conduct employee engagement session', 'reason' => 'Address attendance and morale concerns', 'module' => 'hrm'],
                ['recommendation' => 'Analyze customer feedback', 'reason' => 'Customer satisfaction metrics need improvement', 'module' => 'sales'],
                ['recommendation' => 'Update project timeline estimates', 'reason' => 'Current estimates appear overly optimistic', 'module' => 'projects'],
                ['recommendation' => 'Optimize warehouse organization', 'reason' => 'Improve inventory accessibility and efficiency', 'module' => 'inventory'],
            ],
            'low' => [
                ['recommendation' => 'Monitor financial metrics closely', 'reason' => 'Maintain awareness of financial health indicators', 'module' => 'account'],
                ['recommendation' => 'Continue team performance monitoring', 'reason' => 'Regular monitoring ensures early issue detection', 'module' => 'hrm'],
                ['recommendation' => 'Track sales trends', 'reason' => 'Understanding trends helps with forecasting', 'module' => 'sales'],
                ['recommendation' => 'Schedule regular project reviews', 'reason' => 'Keep stakeholders informed of progress', 'module' => 'projects'],
                ['recommendation' => 'Review inventory levels quarterly', 'reason' => 'Ensure optimal stock management', 'module' => 'inventory'],
            ],
        ];

        $alerts_data = [
            'critical' => [
                ['title' => 'Critical: Negative Cash Balance', 'message' => 'Account balance has gone negative', 'module' => 'account'],
                ['title' => 'Critical: Mass Absence Report', 'message' => 'More than 40% staff absent today', 'module' => 'hrm'],
                ['title' => 'Critical: Sales Stopped', 'message' => 'Zero sales transactions detected today', 'module' => 'sales'],
                ['title' => 'Critical: Project Milestone Missed', 'message' => 'Critical project milestone has been missed', 'module' => 'projects'],
                ['title' => 'Critical: Complete Stock Out', 'message' => 'All products out of stock', 'module' => 'inventory'],
            ],
            'warning' => [
                ['title' => 'Warning: Expense Spike', 'message' => 'Unusual high expenses detected today', 'module' => 'account'],
                ['title' => 'Warning: High Absenteeism', 'message' => 'Absentee rate exceeds 25%', 'module' => 'hrm'],
                ['title' => 'Warning: Low Sales Day', 'message' => 'Sales significantly below daily average', 'module' => 'sales'],
                ['title' => 'Warning: Tasks Overdue', 'message' => 'Multiple project tasks are overdue', 'module' => 'projects'],
                ['title' => 'Warning: Stock Low', 'message' => 'Several items approaching minimum levels', 'module' => 'inventory'],
            ],
        ];

        // Determine severity based on health score
        $severity = $scoreData['score'] > 70 ? 'positive' : ($scoreData['score'] < 40 ? 'critical' : 'warning');

        // Create 3-5 insights
        $insightCount = rand(3, 5);
        foreach (range(1, $insightCount) as $i) {
            $severity_key = $scoreData['score'] > 70 ? 'positive' : ($scoreData['score'] < 40 ? 'critical' : 'warning');
            $insight = $insights_data[$severity_key][array_rand($insights_data[$severity_key])];
            
            AiBusinessInsight::create([
                'health_score_id' => $healthScore->id,
                'title'           => $insight['title'],
                'description'     => $insight['description'],
                'severity'        => $severity_key,
                'module'          => $insight['module'],
                'analysis_date'   => $date->toDateString(),
                'created_by'      => $userId,
            ]);
        }

        // Create 2-3 recommendations
        $recCount = rand(2, 3);
        foreach (range(1, $recCount) as $i) {
            $priority = ['high', 'medium', 'low'][array_rand(['high', 'medium', 'low'])];
            $rec = $recommendations_data[$priority][array_rand($recommendations_data[$priority])];

            AiBusinessRecommendation::create([
                'health_score_id' => $healthScore->id,
                'recommendation'  => $rec['recommendation'],
                'reason'          => $rec['reason'],
                'priority'        => $priority,
                'related_module'  => $rec['module'],
                'status'          => 'pending',
                'analysis_date'   => $date->toDateString(),
                'created_by'      => $userId,
            ]);
        }

        // Create 0-2 alerts (only if score is low)
        $alertCount = $scoreData['score'] < 50 ? rand(1, 2) : rand(0, 1);
        foreach (range(1, $alertCount) as $i) {
            $alert_severity = $scoreData['score'] < 30 ? 'critical' : 'warning';
            $alert = $alerts_data[$alert_severity][array_rand($alerts_data[$alert_severity])];

            AiBusinessAlert::create([
                'health_score_id' => $healthScore->id,
                'title'           => $alert['title'],
                'message'         => $alert['message'],
                'severity'        => $alert_severity,
                'module'          => $alert['module'],
                'analysis_date'   => $date->toDateString(),
                'created_by'      => $userId,
            ]);
        }
    }

    /**
     * Update trend for each health score by comparing with previous day.
     */
    private function updateTrends(int $userId, int $days): void
    {
        echo "📈 Updating trends...\n";

        $scores = AiBusinessHealthScore::where('created_by', $userId)
            ->orderBy('analysis_date', 'asc')
            ->get();

        $prevScore = null;
        foreach ($scores as $score) {
            if ($prevScore !== null) {
                $diff = $score->score - $prevScore;
                $trend = $diff > 1 ? 'improving' : ($diff < -1 ? 'declining' : 'stable');
                $score->update(['trend' => $trend]);
            }
            $prevScore = $score->score;
        }

        echo "✅ Trends updated for " . $scores->count() . " records.\n";
    }
}
