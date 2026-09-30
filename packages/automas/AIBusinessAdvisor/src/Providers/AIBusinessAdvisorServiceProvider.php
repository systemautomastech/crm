<?php

namespace Automas\AIBusinessAdvisor\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Automas\AIBusinessAdvisor\Console\Commands\GenerateAIInsightsCommand;
use Automas\AIBusinessAdvisor\Models\AiBusinessHealthScore;

class AIBusinessAdvisorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerTranslations();
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
        $this->registerCommands();
        $this->registerCommandSchedules();
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    protected function registerCommands(): void
    {
        $this->commands([
            GenerateAIInsightsCommand::class,
        ]);
    }

    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('ai-advisor:generate')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->runInBackground();

            $schedule->call(function () {
                $retentionDays = (int) (getAdminAllSetting()['ai_advisor_retention_days'] ?? 90);
                $cutoff = now()->subDays($retentionDays);
                AiBusinessHealthScore::where('analysis_date', '<', $cutoff)->delete();
            })->weekly();
        });
    }

    protected function registerTranslations()
    {
        $mainLangPath = resource_path('lang');
        if (is_dir($mainLangPath)) {
            $this->loadJsonTranslationsFrom($mainLangPath);
        }
        
        $packageLangPath = __DIR__.'/../Resources/lang';
        if (is_dir($packageLangPath)) {
            $this->loadJsonTranslationsFrom($packageLangPath);
        }
    }
}
