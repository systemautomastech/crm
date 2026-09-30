<?php

namespace Automas\AIBusinessAdvisor\Http\Controllers;

use Automas\AIBusinessAdvisor\Models\AiBusinessAlert;
use Automas\AIBusinessAdvisor\Models\AiBusinessHealthScore;
use Automas\AIBusinessAdvisor\Models\AiBusinessInsight;
use Automas\AIBusinessAdvisor\Models\AiBusinessRecommendation;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AIAdvisorController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-ai-business-advisor')){
            $userId = creatorId();
            $selectedDate = request()->query('date');

            if ($selectedDate) {
                $selectedScore = AiBusinessHealthScore::where('created_by', $userId)
                    ->whereDate('analysis_date', $selectedDate)
                    ->first();

                $latestScore = $selectedScore;
                $hasSelectedDateData = (bool) $selectedScore;
            } else {
                $todayScore = AiBusinessHealthScore::where('created_by', $userId)
                    ->whereDate('analysis_date', today())
                    ->first();

                $latestScore = $todayScore ?? AiBusinessHealthScore::where('created_by', $userId)
                    ->orderByDesc('analysis_date')
                    ->first();

                $hasSelectedDateData = (bool) $todayScore;
            }

            $healthScoreId = $latestScore?->id;

            $insights = AiBusinessInsight::where('created_by', $userId)
                ->where('health_score_id', $healthScoreId)
                ->orderBy('is_dismissed')
                ->orderByRaw("FIELD(severity, 'critical', 'warning', 'info', 'positive')")
                ->get();

            $recommendations = AiBusinessRecommendation::where('created_by', $userId)
                ->where('health_score_id', $healthScoreId)
                ->orderByRaw("FIELD(status, 'pending', 'done', 'dismissed')")
                ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                ->get();

            $alerts = AiBusinessAlert::where('created_by', $userId)
                ->when($selectedDate, fn($q) => $q->whereDate('analysis_date', $selectedDate))
                ->orderBy('is_resolved')
                ->orderByRaw("FIELD(severity, 'critical', 'warning')")
                ->get();

            $scoreHistory = AiBusinessHealthScore::where('created_by', $userId)
                ->orderByDesc('analysis_date')
                ->limit(7)
                ->get(['score', 'analysis_date', 'trend']);

            $aiAdvisorEnabled = getAdminAllSetting()['ai_advisor_enabled'] ?? 'off';
            $aiAdvisorEnabled = $aiAdvisorEnabled === 'on';

            return Inertia::render('AIBusinessAdvisor/AIBusinessAdvisor/AIBusinessAdvisor', [
                'health_score'     => $latestScore,
                'insights'         => $insights,
                'recommendations'  => $recommendations,
                'alerts'           => $alerts,
                'score_history'    => $scoreHistory,
                'analysis_date'    => $latestScore?->analysis_date,
                'has_today_data'   => $hasSelectedDateData,
                'aiAdvisorEnabled' => $aiAdvisorEnabled,
                'selected_date'    => $selectedDate,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function markDone($id)
    {
        if (Auth::user()->can('manage-ai-business-adviser-status')) {
            AiBusinessRecommendation::where('id', $id)
                ->where('created_by', creatorId())
                ->update(['status' => 'done', 'actioned_at' => now()]);

            return back()->with('success', __('Recommendation marked as done.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function dismiss($id)
    {
        if (Auth::user()->can('manage-ai-business-adviser-status')) {
            AiBusinessRecommendation::where('id', $id)
                ->where('created_by', creatorId())
                ->update(['status' => 'dismissed', 'actioned_at' => now()]);

            return back()->with('success', __('Recommendation dismissed.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function dismissInsight($id)
    {
        if (Auth::user()->can('manage-ai-business-adviser-status')) {
            AiBusinessInsight::where('id', $id)
                ->where('created_by', creatorId())
                ->update(['is_dismissed' => true]);

            return back()->with('success', __('Insight dismissed.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function resolveAlert($id)
    {
        if (Auth::user()->can('manage-ai-business-adviser-status')) {
            AiBusinessAlert::where('id', $id)
                ->where('created_by', creatorId())
                ->update(['is_resolved' => true, 'resolved_at' => now()]);

            return back()->with('success', __('Alert resolved.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function generateNow()
    {
        if (Auth::user()->can('manage-ai-business-advisor')) {
            // Check if AI Advisor is enabled at superadmin level
            $aiAdvisorEnabled = getAdminAllSetting()['ai_advisor_enabled'] ?? 'off';
            if ($aiAdvisorEnabled !== 'on' && $aiAdvisorEnabled !== '1') {
                return back()->with('error', __('AI Advisor is disabled by superadmin.'));
            }

            $alreadyDone = AiBusinessHealthScore::where('created_by', creatorId())
                ->whereDate('analysis_date', today())
                ->exists();

            if ($alreadyDone) {
                return back()->with('warning', __("Today's analysis already done."));
            }

            Artisan::call('ai-advisor:generate', ['user' => creatorId()]);

            return back()->with('success', __('Analysis completed successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
