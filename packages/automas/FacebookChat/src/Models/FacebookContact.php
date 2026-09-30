<?php

namespace Automas\FacebookChat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'profile_image',
        'user_name',
        'sender_id',
        'creator_id',
        'created_by',
    ];

    public function chats(): HasMany
    {
        return $this->hasMany(FacebookChat::class);
    }
}