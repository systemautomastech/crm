<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Automas\Lead\Models\Pipeline;

class LeadStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'pipeline_id',
        'is_final_accepted',
        'is_final_rejected',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'is_final_accepted' => 'boolean',
        'is_final_rejected' => 'boolean',
        'order' => 'integer',
        'pipeline_id' => 'integer',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }
}