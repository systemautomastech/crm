<?php

namespace Automas\Hrm\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'employee_id',
        'loan_type_id',
        'type',
        'amount',
        'start_date',
        'end_date',
        'reason',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}