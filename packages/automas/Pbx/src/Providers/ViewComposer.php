<?php

namespace Automas\Pbx\Providers;

use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    public function boot(): void
    {
        // Softphone UI is now loaded from main Vue Dialer.vue
        // PBX package will only provide backend/config/log services.
    }

    public function register(): void
    {
    }
}