<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'holiday_type_id',
        'description',
        'is_paid',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'holiday_type_id' => 'integer',
            'is_paid' => 'boolean',
        ];
    }

    public function holidayType()
    {
        return $this->belongsTo(HolidayType::class, 'holiday_type_id');
    }
}