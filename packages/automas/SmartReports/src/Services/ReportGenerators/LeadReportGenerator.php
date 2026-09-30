<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Automas\Lead\Models\Lead;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\LeadStage;
use Automas\Lead\Models\Source;
use Automas\Lead\Models\Label;

class LeadReportGenerator
{
    public static function normalizeLeadStatus(mixed $row): string
    {
        $isConverted = is_array($row)
            ? ($row['is_converted'] ?? null)
            : ($row->is_converted ?? null);

        if (!empty($isConverted) || isset($isConverted) && (int) $isConverted === 1) {
            return 'Converted';
        }

        $isActive = is_array($row)
            ? ($row['is_active'] ?? null)
            : ($row->is_active ?? null);

        if (!empty($isActive)) {
            return 'Active';
        }

        return 'Inactive';
    }

    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);

        return [
            'data' => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = Lead::query()
            ->leftJoin('pipelines', 'leads.pipeline_id', '=', 'pipelines.id')
            ->leftJoin('lead_stages', 'leads.stage_id', '=', 'lead_stages.id')
            ->leftJoin('users as owner', 'leads.user_id', '=', 'owner.id')
            ->leftJoin('users as creator', 'leads.creator_id', '=', 'creator.id')
            ->where('leads.created_by', creatorId())
            ->select(
                'leads.id',
                'leads.name as lead_name',
                'leads.subject',
                'leads.email',
                'leads.phone',
                'leads.date as follow_up_date',
                'leads.notes',
                'leads.is_active',
                'leads.is_converted',
                'leads.created_at',
                DB::raw('DATEDIFF(CURDATE(), leads.created_at) as age_days'),
                'pipelines.name as pipeline_name',
                'lead_stages.name as stage_name',
                'owner.name as assigned_to',
                'creator.name as created_by_name',
                DB::raw('(SELECT COUNT(*) FROM lead_tasks WHERE lead_id = leads.id) as task_count'),
                DB::raw('(SELECT COUNT(*) FROM lead_calls WHERE lead_id = leads.id) as call_count'),
                DB::raw('(SELECT COUNT(*) FROM lead_emails WHERE lead_id = leads.id) as email_count')
            );

        if (!empty($filters['pipeline_ids'])) {
            $query->whereIn('leads.pipeline_id', $filters['pipeline_ids']);
        }

        if (!empty($filters['stage_ids'])) {
            $query->whereIn('leads.stage_id', $filters['stage_ids']);
        }

        if (!empty($filters['user_ids'])) {
            $query->whereIn('leads.user_id', $filters['user_ids']);
        }

        if (!empty($filters['source_ids'])) {
            $query->where(function ($sub) use ($filters) {
                foreach ($filters['source_ids'] as $sourceId) {
                    $sub->orWhereRaw("FIND_IN_SET(?, leads.sources)", [$sourceId]);
                }
            });
        }

        if (!empty($filters['label_ids'])) {
            $query->where(function ($sub) use ($filters) {
                foreach ($filters['label_ids'] as $labelId) {
                    $sub->orWhereRaw("FIND_IN_SET(?, leads.labels)", [$labelId]);
                }
            });
        }

        if (!empty($filters['status'])) {
            $query->where(function ($sub) use ($filters) {
                foreach ($filters['status'] as $status) {
                    if ($status === 'Converted') {
                        $sub->orWhere('leads.is_converted', 1);
                    } elseif ($status === 'Active') {
                        $sub->orWhere(function ($nested) {
                            $nested->where('leads.is_converted', 0)->where('leads.is_active', 1);
                        });
                    } elseif ($status === 'Inactive') {
                        $sub->orWhere(function ($nested) {
                            $nested->where('leads.is_converted', 0)->where('leads.is_active', 0);
                        });
                    }
                }
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('leads.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('leads.created_at', '<=', $filters['date_to']);
        }

        $rows = $query->orderBy('leads.created_at', 'desc')->get();

        return $rows->map(function ($row) {
            $row->status = self::normalizeLeadStatus((array) $row);
            return $row;
        });
    }

    public function getSummary($data)
    {
        $active = $data->where('status', 'Active');
        $converted = $data->where('status', 'Converted');
        $inactive = $data->where('status', 'Inactive');
        $total = $data->count();

        return [
            'total_leads' => $total,
            'active_leads' => $active->count(),
            'converted_leads' => $converted->count(),
            'inactive_leads' => $inactive->count(),
            'conversion_rate' => $total > 0 ? round($converted->count() / $total * 100, 2) : 0,
            'average_age_days' => $total > 0 ? round($data->avg('age_days'), 0) : 0,
        ];
    }

    public function getFilterOptions()
    {
        $creatorId = creatorId();

        $pipelines = Pipeline::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($p) => ['value' => $p->id, 'label' => $p->name]);

        $stages = LeadStage::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name', 'pipeline_id'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name, 'pipeline_id' => $s->pipeline_id]);

        $users = User::where('created_by', '=', $creatorId)->emp(['vendor', 'client'])
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($u) => ['value' => $u->id, 'label' => $u->name]);

        $sources = Source::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name]);

        $labels = Label::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($l) => ['value' => $l->id, 'label' => $l->name]);

        return [
            'pipelines' => $pipelines,
            'stages' => $stages,
            'users' => $users,
            'sources' => $sources,
            'labels' => $labels,
        ];
    }
}
