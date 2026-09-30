<?php

namespace Automas\AIBusinessAdvisor\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command; 
use Illuminate\Support\Facades\DB; 
use Automas\AIBusinessAdvisor\Models\AiBusinessAlert;
use Automas\AIBusinessAdvisor\Models\AiBusinessHealthScore;
use Automas\AIBusinessAdvisor\Models\AiBusinessInsight;
use Automas\AIBusinessAdvisor\Models\AiBusinessRecommendation;
use Automas\AIBusinessAdvisor\Services\AIAnalysisService;
use Automas\AIBusinessAdvisor\Services\DataAggregationService;
use Automas\AIBusinessAdvisor\Services\HealthScoreService;

class GenerateAIInsightsCommand extends Command
{
    protected $signature = 'ai-advisor:generate {user?}';
    protected $description = 'Generate daily AI business insights for all companies or a specific user';

    public function handle(
        DataAggregationService $aggregator,
        HealthScoreService $scorer,
        AIAnalysisService $ai
    ): int {
        // Build user query
        $userQuery = User::where('type', 'company')
            ->where('is_disable', 0);

        // If user argument is provided, process only that user
        $userIdArg = $this->argument('user');
        if ($userIdArg) {
            $userQuery->where('id', $userIdArg);
        }

        $users = $userQuery->pluck('id');

        $this->info("Found " . $users->count() . " user(s) to process: " . $users->implode(', '));

        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return self::SUCCESS;
        }

        // Fetch superadmin settings once before loop
        $superadminSettings = getAdminAllSetting();
        $aiAdvisorEnabled = $superadminSettings['ai_advisor_enabled'] ?? 'NOT_SET';
        $this->info("ai_advisor_enabled = {$aiAdvisorEnabled}");

        if (empty($superadminSettings['ai_advisor_enabled']) || $superadminSettings['ai_advisor_enabled'] !== 'on') {
            $this->warn("⊘ AI Advisor is disabled at superadmin level. Aborting.");
            return self::SUCCESS;
        }

        foreach ($users as $index => $userId) {
            try {
                $this->info("Processing user: {$userId}");

                // Skip if already generated today
                $alreadyDone = AiBusinessHealthScore::where('created_by', $userId)
                    ->whereDate('analysis_date', today())
                    ->exists();

                $this->info("  Already generated today: " . ($alreadyDone ? 'YES' : 'NO'));

                if ($alreadyDone) {
                    $this->warn("⊘ Skipped user {$userId} (already generated today)");
                    continue;
                }

                // Pre-check AI agent settings BEFORE starting transaction/API call
                $provider = company_setting('ai_agent_provider', $userId);
                $model    = company_setting('ai_agent_model', $userId);
                $apiKey   = company_setting('ai_agent_api_key', $userId);

                if (!$provider || !$model || !$apiKey) {
                    $this->warn("⊘ Skipped user {$userId} (AI Agent not configured: provider={$provider}, model={$model}, hasKey=" . ($apiKey ? 'yes' : 'no') . ")");
                    continue;
                }

                $this->info("  AI provider: {$provider}, model: {$model}");

                DB::transaction(function () use ($aggregator, $scorer, $ai, $userId) {
                    // 1. Collect metrics
                    $metrics = $aggregator->getAllMetrics($userId);

                    // 2. Calculate health score
                    $scoreData = $scorer->calculate($metrics);

                    // 3. Save health score
                    $healthScore = new AiBusinessHealthScore();
                    $healthScore->score            = $scoreData['score'];
                    $healthScore->financial_score  = $scoreData['financial_score'];
                    $healthScore->team_score       = $scoreData['team_score'];
                    $healthScore->sales_score      = $scoreData['sales_score'];
                    $healthScore->project_score    = $scoreData['project_score'];
                    $healthScore->operations_score = $scoreData['operations_score'];
                    $healthScore->raw_metrics      = $metrics;
                    $healthScore->analysis_date    = today();
                    $healthScore->created_by       = $userId;
                    $healthScore->save();

                    // 4. Call AI API
                    $aiResult = $ai->analyze($metrics, $scoreData, $userId);

                    // 5. Save insights
                    foreach ($aiResult['insights'] ?? [] as $insight) {
                        $aiInsight = new AiBusinessInsight();
                        $aiInsight->health_score_id = $healthScore->id;
                        $aiInsight->title           = $insight['title'];
                        $aiInsight->description     = $insight['description'];
                        $aiInsight->severity        = $insight['severity'];
                        $aiInsight->module          = $insight['module'] ?? null;
                        $aiInsight->analysis_date   = today();
                        $aiInsight->created_by      = $userId;
                        $aiInsight->save();
                    }

                    // 6. Save recommendations
                    foreach ($aiResult['recommendations'] ?? [] as $rec) {
                        $aiRec = new AiBusinessRecommendation();
                        $aiRec->health_score_id = $healthScore->id;
                        $aiRec->recommendation  = $rec['recommendation'];
                        $aiRec->reason          = $rec['reason'] ?? null;
                        $aiRec->priority        = $rec['priority'] ?? 'medium';
                        $aiRec->related_module  = $rec['related_module'] ?? null;
                        $aiRec->analysis_date   = today();
                        $aiRec->created_by      = $userId;
                        $aiRec->save();
                    }

                    // 7. Save alerts
                    foreach ($aiResult['alerts'] ?? [] as $alert) {
                        $aiAlert = new AiBusinessAlert();
                        $aiAlert->health_score_id = $healthScore->id;
                        $aiAlert->title           = $alert['title'];
                        $aiAlert->message         = $alert['message'];
                        $aiAlert->severity        = $alert['severity'];
                        $aiAlert->module          = $alert['module'] ?? null;
                        $aiAlert->analysis_date   = today();
                        $aiAlert->created_by      = $userId;
                        $aiAlert->save();
                    }

                    // 8. Calculate trend (compare with yesterday's score)
                    $this->updateTrend($healthScore);
                });

                $this->info("✅ Generated insights for user: {$userId}");
            } catch (\Exception $e) {
                $this->error("❌ Failed for user: {$userId} — " . $e->getMessage());
            }

            // Add delay between users to avoid rate limiting (except for the last user)
            if ($index < $users->count() - 1) {
                $this->info("  ⏳ Waiting 3 seconds before next user...");
                sleep(3);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Calculate and update trend by comparing with yesterday's score.
     */
    private function updateTrend(AiBusinessHealthScore $current): void
    {
        $yesterday = AiBusinessHealthScore::where('created_by', $current->created_by)
            ->whereDate('analysis_date', today()->subDay())
            ->first();

        if (!$yesterday) {
            $current->update(['trend' => 'stable']);
            return;
        }

        $diff = $current->score - $yesterday->score;
        $trend = $diff > 1 ? 'improving' : ($diff < -1 ? 'declining' : 'stable');

        $current->update(['trend' => $trend]);
    }
}
