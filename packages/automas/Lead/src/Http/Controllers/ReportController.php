<?php

namespace Automas\Lead\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Automas\Lead\Models\Lead;
use Automas\Lead\Models\Deal;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\Source;
use App\Models\User;
use Carbon\Carbon;
use Automas\Lead\Models\ClientDeal;
use Automas\Lead\Models\DealStage;

class ReportController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('view-reports')) {
            return Inertia::render('Lead/Reports/Index');
        }
        return back()->with('error', __('Permission denied'));
    }

    public function leadReports(Request $request)
    {
        if (Auth::user()->can('view-reports')) {
            // Weekly Conversions (Pie Chart)
            $weeklyConversions = $this->getWeeklyConversions();

            // Sources Conversion (Bar Chart)
            $sourcesConversion = $this->getSourcesConversion();

            // Monthly Leads (Bar Chart)
            $monthlyLeads = $this->getMonthlyLeads();

            // Staff Leads (Bar Chart with date filter)
            $staffLeads = $this->getStaffLeads($request->from_date, $request->to_date);

            // Pipeline Leads (Bar Chart)
            $pipelineLeads = $this->getPipelineLeads();

            return Inertia::render('Lead/Reports/LeadReports', [
                'weeklyConversions' => $weeklyConversions,
                'sourcesConversion' => $sourcesConversion,
                'monthlyLeads' => $monthlyLeads,
                'staffLeads' => $staffLeads,
                'pipelineLeads' => $pipelineLeads,
            ]);
        }
        return back()->with('error', __('Permission denied'));
    }

    private function getWeeklyConversions()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            $dayName = $currentDay->format('l');

            $count = Lead::where('created_by', creatorId())
                ->whereDate('created_at', $currentDay)
                ->count();

            if ($count > 0) {
                $data[] = [
                    'name' => $dayName,
                    'value' => $count
                ];
            }
        }

        if (empty($data)) {
            $data = [
                ['name' => 'Monday', 'value' => 0],
                ['name' => 'Tuesday', 'value' => 1],
                ['name' => 'Wednesday', 'value' => 0],
                ['name' => 'Thursday', 'value' => 0],
                ['name' => 'Friday', 'value' => 0],
                ['name' => 'Saturday', 'value' => 0],
                ['name' => 'Sunday', 'value' => 0]
            ];
        }

        return $data;
    }

    private function getSourcesConversion()
    {
        $sources = Source::where('created_by', creatorId())->get();
        $data = [];

        foreach ($sources as $source) {
            $count = Lead::where('created_by', creatorId())
                ->where('sources', 'like', '%' . $source->id . '%')
                ->count();

            $data[] = [
                'name' => $source->name,
                'value' => $count
            ];
        }

        return $data;
    }

    private function getMonthlyLeads()
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create(date('Y'), $month)->format('M Y');
            $count = Lead::where('created_by', creatorId())
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', date('Y'))
                ->count();

            $data[] = [
                'month' => $month,
                'name' => $monthName,
                'leads' => $count
            ];
        }

        return $data;
    }

    private function getStaffLeads($fromDate = null, $toDate = null)
    {
        $lead_user = User::where('created_by', '=', creatorId())->emp(['vendor', 'client'])->get();
        $leaduserName = [];
        $leadusereData = [];

        foreach ($lead_user as $lead_user_data) {
            if (!empty($fromDate) && !empty($toDate)) {
                $form_date = date('Y-m-d', strtotime($fromDate));
                $to_date = date('Y-m-d', strtotime($toDate));

                $lead_count = Lead::select('leads.*')
                    ->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')
                    ->where('user_leads.user_id', '=', $lead_user_data->id)
                    ->where('leads.date', '>=', $form_date)
                    ->where('leads.date', '<=', $to_date)
                    ->count();
            } else {
                $lead_count = Lead::select('leads.*')
                    ->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')
                    ->where('user_leads.user_id', '=', $lead_user_data->id)
                    ->count();
            }

            $leaduserName[] = $lead_user_data->name;
            $leadusereData[] = $lead_count;
        }

        $data = [];
        for ($i = 0; $i < count($leaduserName); $i++) {
            $data[] = [
                'name' => $leaduserName[$i],
                'leads' => $leadusereData[$i]
            ];
        }

        return $data;
    }

    private function getPipelineLeads()
    {
        $pipelines = Pipeline::where('created_by', creatorId())->get();
        $data = [];

        foreach ($pipelines as $pipeline) {
            $count = Lead::where('created_by', creatorId())
                ->where('pipeline_id', $pipeline->id)
                ->count();

            $data[] = [
                'name' => $pipeline->name,
                'leads' => $count
            ];
        }

        return $data;
    }

    public function dealReports(Request $request)
    {
        if (Auth::user()->can('view-reports')) {
            // Weekly Deal Conversions (Pie Chart)
            $weeklyDealConversions = $this->getWeeklyDealConversions();

            // Deal Sources Conversion (Bar Chart)
            $dealSourcesConversion = $this->getDealSourcesConversion();

            // Monthly Deals (Bar Chart)
            $monthlyDeals = $this->getMonthlyDeals();

            // Staff Deals (Bar Chart with date filter)
            $staffDeals = $this->getStaffDeals($request->from_date, $request->to_date);

            // Client Deals (Bar Chart with date filter)
            $clientDeals = $this->getClientDeals($request->from_date, $request->to_date);

            // Pipeline Deals (Bar Chart)
            $pipelineDeals = $this->getPipelineDeals();

            // Deals by Stage Chart (with pipeline filter)
            $pipelineId = $request->get('pipeline_id');
            $dealStageChart = [];
            $dealStages = DealStage::where('created_by', creatorId())
                ->when($pipelineId, fn($q) => $q->where('pipeline_id', $pipelineId))
                ->orderBy('order', 'ASC')
                ->get();

            foreach ($dealStages as $stage) {
                $dealCount = Deal::where('created_by', creatorId())
                    ->where('stage_id', $stage->id)
                    ->count();

                $dealStageChart[] = [
                    'name' => $stage->name,
                    'deals' => $dealCount
                ];
            }

            // Get all pipelines for dropdown
            $pipelines = Pipeline::where('created_by', creatorId())->get(['id', 'name']);

            return Inertia::render('Lead/Reports/DealReports', [
                'weeklyDealConversions' => $weeklyDealConversions,
                'dealSourcesConversion' => $dealSourcesConversion,
                'monthlyDeals' => $monthlyDeals,
                'staffDeals' => $staffDeals,
                'clientDeals' => $clientDeals,
                'pipelineDeals' => $pipelineDeals,
                'dealStageChart' => $dealStageChart,
                'pipelines' => $pipelines,
            ]);
        }
        return back()->with('error', __('Permission denied'));
    }

    private function getWeeklyDealConversions()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $data = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            $dayName = $currentDay->format('l');

            $count = Deal::where('created_by', creatorId())
                ->whereDate('created_at', $currentDay)
                ->count();

            if ($count > 0) {
                $data[] = [
                    'name' => $dayName,
                    'value' => $count
                ];
            }
        }

        if (empty($data)) {
            $data = [
                ['name' => 'Monday', 'value' => 0],
                ['name' => 'Tuesday', 'value' => 1],
                ['name' => 'Wednesday', 'value' => 0],
                ['name' => 'Thursday', 'value' => 0],
                ['name' => 'Friday', 'value' => 0],
                ['name' => 'Saturday', 'value' => 0],
                ['name' => 'Sunday', 'value' => 0]
            ];
        }

        return $data;
    }

    private function getDealSourcesConversion()
    {
        $sources = Source::where('created_by', creatorId())->get();
        $data = [];

        foreach ($sources as $source) {
            $count = Deal::where('created_by', creatorId())
                ->where('sources', 'like', '%' . $source->id . '%')
                ->count();

            $data[] = [
                'name' => $source->name,
                'value' => $count
            ];
        }

        return $data;
    }

    private function getMonthlyDeals()
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create(date('Y'), $month)->format('M Y');
            $count = Deal::where('created_by', creatorId())
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', date('Y'))
                ->count();

            $data[] = [
                'month' => $month,
                'name' => $monthName,
                'deals' => $count
            ];
        }

        return $data;
    }

    private function getStaffDeals($fromDate = null, $toDate = null)
    {
        $deal_user = User::where('created_by', '=', creatorId())->emp()->get();
        $dealuserName = [];
        $dealusereData = [];

        foreach ($deal_user as $deal_user_data) {
            if (!empty($fromDate) && !empty($toDate)) {
                $form_date = date('Y-m-d', strtotime($fromDate));
                $to_date = date('Y-m-d', strtotime($toDate));

                $deal_count = Deal::select('deals.*')
                    ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                    ->where('user_deals.user_id', '=', $deal_user_data->id)
                    ->whereDate('deals.created_at', '>=', $form_date)
                    ->whereDate('deals.created_at', '<=', $to_date)
                    ->count();
            } else {
                $deal_count = Deal::select('deals.*')
                    ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                    ->where('user_deals.user_id', '=', $deal_user_data->id)
                    ->count();
            }

            $dealuserName[] = $deal_user_data->name;
            $dealusereData[] = $deal_count;
        }

        $data = [];
        for ($i = 0; $i < count($dealuserName); $i++) {
            $data[] = [
                'name' => $dealuserName[$i],
                'deals' => $dealusereData[$i]
            ];
        }

        return $data;
    }

    private function getPipelineDeals()
    {
        $pipelines = Pipeline::where('created_by', creatorId())->get();
        $data = [];

        foreach ($pipelines as $pipeline) {
            $count = Deal::where('created_by', creatorId())
                ->where('pipeline_id', $pipeline->id)
                ->count();

            $data[] = [
                'name' => $pipeline->name,
                'deals' => $count
            ];
        }

        return $data;
    }

    private function getClientDeals($fromDate = null, $toDate = null)
    {
        $client_deal = User::where('created_by', '=', creatorId())->where('type', '=', 'client')->get();
        $dealClientName = [];
        $dealClientData = [];

        foreach ($client_deal as $client_deal_data) {
            if (!empty($fromDate) && !empty($toDate)) {
                $form_date = date('Y-m-d', strtotime($fromDate));
                $to_date = date('Y-m-d', strtotime($toDate));

                $deals_client = ClientDeal::where('client_id', $client_deal_data->id)
                    ->whereDate('created_at', '>=', $form_date)
                    ->whereDate('created_at', '<=', $to_date)
                    ->count();
            } else {
                $deals_client = ClientDeal::where('client_id', $client_deal_data->id)->count();
            }

            $dealClientName[] = $client_deal_data->name;
            $dealClientData[] = $deals_client;
        }

        $data = [];
        for ($i = 0; $i < count($dealClientName); $i++) {
            $data[] = [
                'name' => $dealClientName[$i],
                'deals' => $dealClientData[$i]
            ];
        }

        return $data;
    }
}
