<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Automas\Hrm\Models\Branch;
use Automas\Hrm\Models\Department;
use Automas\Hrm\Models\Designation;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date_of_birth',
        'gender',
        'shift_id',
        'date_of_joining',
        'employment_type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
        'bank_name',
        'account_holder_name',
        'account_number',
        'bank_identifier_code',
        'bank_branch',
        'tax_payer_id',
        'basic_salary',
        'hours_per_day',
        'days_per_week',
        'rate_per_hour',
        'user_id',
        'branch_id',
        'department_id',
        'designation_id',
        'creator_id',
        'created_by',
        'biometric_emp_id',
    ];

    public function getEmployeeEmailAttribute()
    {
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        return $this->user->email ?? null;
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_joining' => 'date'
        ];
    }



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift', 'id');
    }

    public static function generateEmployeeId(
        $departmentId = null
    ): string {
        $prefix = 'EMP';

        if (!empty($departmentId)) {
            $departmentPrefix = Department::query()
                ->where('id', $departmentId)
                ->where('created_by', creatorId())
                ->value('emp_id_prefix');

            if (!empty($departmentPrefix)) {
                $prefix = strtoupper($departmentPrefix);
            }
        }

        $year = now()->format('y');
        $idStart = $prefix . $year;

        $lastEmployeeId = self::query()
            ->where('created_by', creatorId())
            ->where('employee_id', 'like', $idStart . '%')
            ->orderByDesc('employee_id')
            ->value('employee_id');

        $lastNumber = $lastEmployeeId
            ? (int) substr($lastEmployeeId, -3)
            : 0;

        return $idStart . str_pad(
            $lastNumber + 1,
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}
