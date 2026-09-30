<?php

namespace Automas\AIBusinessAdvisor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiBusinessInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_score_id',
        'title',
        'description',
        'severity',
        'module',
        'is_read',
        'is_dismissed',
        'analysis_date',
        'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'analysis_date' => 'date',
    ];

    public function healthScore(): BelongsTo
    {
        return $this->belongsTo(AiBusinessHealthScore::class, 'health_score_id');
    }
}
