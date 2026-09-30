<?php

namespace Automas\DoubleEntry\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Add your event listeners here
        // Example:
        // App\Events\SomeEvent::class => [
        //     Automas\DoubleEntry\Listeners\SomeListener::class,
        // ],
    ];
}