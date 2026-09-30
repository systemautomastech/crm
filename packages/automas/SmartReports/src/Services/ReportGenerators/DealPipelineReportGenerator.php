<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use Automas\Lead\Models\Deal;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\DealStage;
use App\Models\User;

class DealPipelineReportGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);
        return [
            'data'    => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = Deal::query()
            ->leftJoin('pipelines', 'deals.pipeline_id', '=', 'pipelines.id')
            ->leftJoin('deal_stages', 'deals.stage_id', '=', 'deal_stages.id')
            ->leftJoin('users as owner', 'deals.creator_id', '=', 'owner.id')
            ->where('deals.created_by', creatorId())
            ->select(
                'deals.id',
                'deals.name as deal_name',
                'deals.price as deal_value',
                DB::raw('CAST(deals.status AS CHAR) as status'),
                'deals.phone',
                'deals.is_active',
                'deals.created_at',
                DB::raw('DATEDIFF(CURDATE(), deals.created_at) as age_days'),
                'pipelines.name as pipeline_name',
                'deal_stages.name as stage_name',
                'owner.name as assigned_to',
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT cu.name SEPARATOR ", ")
                    FROM client_deals cd
                    JOIN users cu ON cd.client_id = cu.id
                    WHERE cd.deal_id = deals.id) as client_names'),
                DB::raw('(SELECT COUNT(*) FROM deal_tasks WHERE deal_id = deals.id) as task_count'),
                DB::raw('(SELECT COUNT(*) FROM deal_calls WHERE deal_id = deals.id) as call_count'),
                DB::raw('(SELECT COUNT(*) FROM deal_emails WHERE deal_id = deals.id) as email_count')
            );

        if (!empty($filters['pipeline_ids'])) {
            $query->whereIn('deals.pipeline_id', $filters['pipeline_ids']);
        }

        if (!empty($filters['stage_ids'])) {
            $query->whereIn('deals.stage_id', $filters['stage_ids']);
        }

        if (!empty($filters['status'])) {
            $query->whereIn('deals.status', $filters['status']);
        }

        if (!empty($filters['assigned_to_ids'])) {
            $query->whereIn('deals.creator_id', $filters['assigned_to_ids']);
        }

        return $query->orderBy('deals.created_at', 'desc')->get();
    }

    public function getSummary($data)
    {
        $won  = $data->where('status', 'Won');
        $lost = $data->where('status', 'Loss');
        $open = $data->whereNotIn('status', ['Won', 'Loss']);
        $total = $data->count();

        return [
            'total_deals'        => $total,
            'total_value'        => round($data->sum('deal_value'), 2),
            'open_deals'         => $open->count(),
            'won_deals'          => $won->count(),
            'lost_deals'         => $lost->count(),
            'open_value'         => round($open->sum('deal_value'), 2),
            'won_value'          => round($won->sum('deal_value'), 2),
            'lost_value'         => round($lost->sum('deal_value'), 2),
            'win_rate'           => $total > 0 ? round($won->count() / $total * 100, 2) : 0,
            'average_deal_value' => $total > 0 ? round($data->sum('deal_value') / $total, 2) : 0,
            'average_age_days'   => $total > 0 ? round($data->avg('age_days'), 0) : 0,
        ];
    }

    public function getFilterOptions()
    {
        $creatorId = creatorId();

        $pipelines = Pipeline::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($p) => ['value' => $p->id, 'label' => $p->name]);

        $stages = DealStage::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name', 'pipeline_id'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name, 'pipeline_id' => $s->pipeline_id]);

        $users = User::whereIn('id', Deal::where('created_by', $creatorId)->pluck('creator_id')->unique()->filter())
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($u) => ['value' => $u->id, 'label' => $u->name]);

        

         
        return [
            'pipelines' => $pipelines,
            'stages'    => $stages,
        ];
    }
}
