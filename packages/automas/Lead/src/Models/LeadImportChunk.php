<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadImportChunk extends Model
{
    protected $fillable = [
        'lead_import_id',
        'chunk_number',
        'stored_path',
        'status',
        'first_row_number',
        'last_row_number',
        'total_rows',
        'processed_rows',
        'inserted_rows',
        'updated_rows',
        'duplicate_rows',
        'skipped_rows',
        'failed_rows',
        'attempts',
        'failure_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(LeadImport::class, 'lead_import_id');
    }

    public function failures(): HasMany
    {
        return $this->hasMany(LeadImportFailure::class);
    }
}