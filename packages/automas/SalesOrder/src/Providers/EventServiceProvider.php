<?php

namespace Automas\SalesOrder\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\GivePermissionToRole;
use App\Events\DefaultData;
use Automas\SalesOrder\Listeners\GiveRoleToPermission;
use Automas\SalesOrder\Listeners\DataDefault;
use Automas\SalesOrder\Events\CreateSalesOrder;
use Automas\SalesOrder\Events\UpdateSalesOrder;
use Automas\SalesOrder\Events\DestroySalesOrder;
use Automas\SalesOrder\Events\CreateSalesOrderDelivery;
use Automas\SalesOrder\Events\CancelSalesOrderDelivery;

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