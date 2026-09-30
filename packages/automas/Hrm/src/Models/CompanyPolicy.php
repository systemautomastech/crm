<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'title',
        'description',
        'attachment',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'attachment' => 'string',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
