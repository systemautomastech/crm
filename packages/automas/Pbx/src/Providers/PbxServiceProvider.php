<?php

namespace Automas\Pbx\Providers;

use Illuminate\Support\ServiceProvider;

class PbxServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Pbx';

    protected $moduleNameLower = 'pbx';

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(ViewComposer::class);

        $this->commands([]);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(
            __DIR__ . '/../Routes/web.php'
        );

        $viewsPath = __DIR__ . '/../Resources/views';

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'pbx');
        }

        $this->loadMigrationsFrom(
            __DIR__ . '/../Database/Migrations'
        );

        $this->registerTranslations();
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path(
            'lang/modules/' . $this->moduleNameLower
        );

        $packageLangPath =
            __DIR__ . '/../Resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom(
                $langPath,
                $this->moduleNameLower
            );

            $this->loadJsonTranslationsFrom(
                $langPath
            );

            return;
        }

        if (is_dir($packageLangPath)) {
            $this->loadTranslationsFrom(
                $packageLangPath,
                $this->moduleNameLower
            );

            $this->loadJsonTranslationsFrom(
                $packageLangPath
            );
        }
    }
}
