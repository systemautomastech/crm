<?php

namespace Automas\Hrm\Providers;

use App\Events\DefaultData;
use Automas\Hrm\Listeners\DataDefault;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\GivePermissionToRole;
use Automas\Hrm\Listeners\GiveRoleToPermission;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DefaultData::class => [
            DataDefault::class
        ],
         GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
    ];
}
