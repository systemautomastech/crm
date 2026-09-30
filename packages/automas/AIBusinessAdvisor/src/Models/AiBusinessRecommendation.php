<?php

namespace Automas\AIBusinessAdvisor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiBusinessRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_score_id',
        'recommendation',
        'reason',
        'priority',
        'status',
        'related_module',
        'analysis_date',
        'actioned_at',
        'created_by',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'actioned_at' => 'datetime',
    ];

    public function healthScore(): BelongsTo
    {
        return $this->belongsTo(AiBusinessHealthScore::class, 'health_score_id');
    }
}
