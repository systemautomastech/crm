<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Automas\Hrm\Models\Branch;
use Automas\Hrm\Models\Department;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation_name',
        'branch_id',
        'department_id',
        'creator_id',
        'created_by',
    ];


    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
