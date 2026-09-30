<?php

namespace Automas\FacebookChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_contact_id',
        'is_send',
        'is_seen',
        'message',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_send' => 'boolean',
            'is_seen' => 'boolean',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(FacebookContact::class, 'facebook_contact_id');
    }
}