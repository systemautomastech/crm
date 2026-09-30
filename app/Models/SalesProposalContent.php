<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesProposalContent extends Model
{
    protected $table = 'sales_proposal_contents';

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
        return $this->belongsTo(SalesProposal::class, 'proposal_id');
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
