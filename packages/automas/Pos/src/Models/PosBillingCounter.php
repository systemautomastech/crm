<?php

namespace Automas\Pos\Models;

use Illuminate\Database\Eloquent\Model;

class PosBillingCounter extends Model
{
    protected $fillable = [
        'name',
        'code',
        'status',
        'description',
        'bank_account_id',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
