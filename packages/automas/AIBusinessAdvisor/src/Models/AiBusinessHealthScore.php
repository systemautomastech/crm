<?php

namespace Automas\AIBusinessAdvisor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiBusinessHealthScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'score',
        'financial_score',
        'team_score',
        'sales_score',
        'project_score',
        'operations_score',
        'trend',
        'raw_metrics',
        'analysis_date',
        'created_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'financial_score' => 'decimal:2',
        'team_score' => 'decimal:2',
        'sales_score' => 'decimal:2',
        'project_score' => 'decimal:2',
        'operations_score' => 'decimal:2',
        'raw_metrics' => 'array',
        'analysis_date' => 'date',
    ];

    public function insights(): HasMany
    {
        return $this->hasMany(AiBusinessInsight::class, 'health_score_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(AiBusinessRecommendation::class, 'health_score_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(AiBusinessAlert::class, 'health_score_id');
    }
}
