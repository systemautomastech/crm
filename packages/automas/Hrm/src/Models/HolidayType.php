<?php

namespace Automas\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class HolidayType extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_type',
        'creator_id',
        'created_by',
    ];
}