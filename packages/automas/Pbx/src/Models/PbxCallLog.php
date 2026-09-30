<?php

namespace Automas\Pbx\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbxCallLog extends Model
{
    use HasFactory;

    protected $table = 'pbx_call_logs';

    protected $fillable = [
        'user_id',
        'extension',
        'direction',
        'from_number',
        'to_number',
        'status',
        'uniqueid',
        'linkedid',
        'started_at',
        'ended_at',
        'duration',
        'recording_url',
        'raw_payload',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
        'raw_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForCreator($query, int $creatorId)
    {
        return $query->where('creator_id', $creatorId);
    }

    /**
     * Calculate total duration (time from started_at to ended_at)
     */
    public function getTotalDuration(): int
    {
        if (!$this->started_at || !$this->ended_at) {
            return 0;
        }
        return max(0, $this->ended_at->diffInSeconds($this->started_at));
    }

    /**
     * Format duration as HH:MM:SS
     */
    public function formatDuration($seconds): string
    {
        if (!$seconds) {
            return '00:00:00';
        }
        return gmdate('H:i:s', $seconds);
    }
}
