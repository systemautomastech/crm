<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadImportFailure extends Model
{
    protected $fillable = [
        'lead_import_id',
        'lead_import_chunk_id',
        'row_number',
        'category',
        'row_data',
        'errors',
    ];

    protected function casts(): array
    {
        return [
            'row_data' => 'array',
            'errors' => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(LeadImport::class, 'lead_import_id');
    }

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(
            LeadImportChunk::class,
            'lead_import_chunk_id'
        );
    }
}