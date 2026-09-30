<?php

namespace Automas\Lead\Providers;

use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Automas\Lead\Listeners\DataDefault;
use Automas\Lead\Listeners\GiveRoleToPermission;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
        DefaultData::class => [
            DataDefault::class,
        ],
    ];
}