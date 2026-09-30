<?php

namespace Automas\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_default',
        'creator_id',
        'created_by',
    ];

}