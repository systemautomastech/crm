<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalDefaultPage extends Model
{
    protected $fillable = [
        'title',
        'content',
        'page_type',
        'background_image',
        'sort_order',
        'is_active',
        'creator_id',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function authorUser()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
