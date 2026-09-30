<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesProposalTariff extends Model
{
    protected $table = 'sales_proposal_tariffs';

    protected $fillable = [
        'proposal_id',
        'particulars',
        'tariff_per_min',
        'brand',
        'qty',
        'pulse_per_min',
        'sort_order',
    ];

    protected $casts = [
        'tariff_per_min' => 'decimal:4',
        'qty' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(SalesProposal::class, 'proposal_id');
    }
}
