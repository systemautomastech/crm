<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalContent extends Model
{
    protected $table = 'proposal_contents';

    protected $fillable = [
        'proposal_id',
        'title',
        'content',
        'page_type',
        'background_image',
        'order',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'proposal_id' => 'integer',
        'order' => 'integer',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
