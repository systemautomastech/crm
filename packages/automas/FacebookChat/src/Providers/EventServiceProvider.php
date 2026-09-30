<?php

namespace Automas\FacebookChat\Providers;

use App\Events\CreateMetaWebhook;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Automas\FacebookChat\Listeners\CreateMetaWebhookLis;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CreateMetaWebhook::class => [
            CreateMetaWebhookLis::class,
        ]
    ];
}