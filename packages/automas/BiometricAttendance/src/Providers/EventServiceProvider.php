<?php

namespace Automas\BiometricAttendance\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Automas\BiometricAttendance\Listeners\SaveBiometricFieldValues;
use Automas\Hrm\Events\CreateEmployee;
use Automas\Hrm\Events\UpdateEmployee;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CreateEmployee::class => [
            SaveBiometricFieldValues::class,
        ],
        UpdateEmployee::class => [
            SaveBiometricFieldValues::class,
        ],
    ];
}