<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadImport extends Model
{
    protected $fillable = [
        'uuid',
        'creator_id',
        'created_by',
        'original_filename',
        'stored_path',
        'file_size',
        'mode',
        'status',
        'duplicate_strategy',
        'column_mapping',
        'default_values',
        'options',
        'total_rows',
        'total_chunks',
        'completed_chunks',
        'processed_rows',
        'inserted_rows',
        'updated_rows',
        'duplicate_rows',
        'skipped_rows',
        'skipped_unassigned_rows',
        'failed_rows',
        'error_file_path',
        'failure_message',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'column_mapping' => 'array',
            'default_values' => 'array',
            'options' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'creator_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(LeadImportChunk::class);
    }

    public function failures(): HasMany
    {
        return $this->hasMany(LeadImportFailure::class);
    }
}