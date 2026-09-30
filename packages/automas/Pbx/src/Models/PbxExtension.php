<?php

namespace Automas\Pbx\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbxExtension extends Model
{
    use HasFactory;

    protected $table = 'pbx_extensions';

    protected $fillable = [
        'user_id',
        'extension',
        'sip_secret',
        'caller_id',
        'is_active',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sip_secret' => 'encrypted',
    ];

    protected $hidden = [
        'sip_secret',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForCreator($query, int $creatorId)
    {
        return $query->where('creator_id', $creatorId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
