<?php

namespace Automas\Hrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'employee_id',
        'total_days',
        'hours',
        'rate',
        'start_date',
        'end_date',
        'notes',
        'status',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'total_days' => 'integer',
        'hours' => 'decimal:2',
        'rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}