<?php

namespace Automas\Quotation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDefaultPage extends Model
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

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}