<?php

namespace Automas\BiometricAttendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BiometricSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'zkteco_api_url',
        'username', 
        'password',
        'auth_token',
        'is_zkteco_sync',
        'created_by'
    ];

    protected $hidden = [
        'password',
    ];
}