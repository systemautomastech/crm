<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class HrmDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'document_category_id',
        'document',
        'effective_date',
        'status',
        'uploaded_by',
        'approved_by',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'document_category_id' => 'integer',
            'effective_date' => 'date',
            'uploaded_by' => 'integer',
            'approved_by' => 'integer'
        ];
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by','id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}