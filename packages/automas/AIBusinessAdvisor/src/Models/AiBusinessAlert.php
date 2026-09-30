<?php

namespace Automas\AIBusinessAdvisor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiBusinessAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_score_id',
        'title',
        'message',
        'severity',
        'module',
        'is_resolved',
        'resolved_at',
        'analysis_date',
        'created_by',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'analysis_date' => 'date',
    ];

    public function healthScore(): BelongsTo
    {
        return $this->belongsTo(AiBusinessHealthScore::class, 'health_score_id');
    }
}
