<?php

namespace Automas\BiometricAttendance\Providers;

use Illuminate\Support\ServiceProvider;
use Automas\Hrm\Models\Attendance;
use Automas\Hrm\Models\Employee;

class BiometricAttendanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerTranslations();
        $routesPath = __DIR__.'/../Routes/web.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
        
        $migrationsPath = __DIR__.'/../Database/Migrations';
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        // Extend HRM Attendance model to add biometric_id to fillable
        $this->extendAttendanceModel();

        // Extend HRM Employee model to add biometric_id to fillable
        $this->extendEmployeeModel();
    }

    protected function extendAttendanceModel(): void
    {
        if (class_exists('\Automas\Hrm\Models\Attendance')) {
            $attendanceModel = new Attendance();
            $fillable = $attendanceModel->getFillable();
            
            if (!in_array('biometric_id', $fillable)) {
                $fillable[] = 'biometric_id';
                $attendanceModel->fillable($fillable);
            }
        }
    }

    protected function extendEmployeeModel(): void
    {
        if (class_exists('\Automas\Hrm\Models\Employee')) {
            $employeeModel = new Employee();
            $fillable = $employeeModel->getFillable();
            
            if (!in_array('biometric_emp_id', $fillable)) {
                $fillable[] = 'biometric_emp_id';
                $employeeModel->fillable($fillable);
            }
        }
    }



    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }
    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        // Load from main app lang folder (all languages)
        $mainLangPath = resource_path('lang');
        if (is_dir($mainLangPath)) {
            $this->loadJsonTranslationsFrom($mainLangPath);
        }

        // Load from package lang folder (fallback)
        $packageLangPath = __DIR__.'/../Resources/lang';
        if (is_dir($packageLangPath)) {
            $this->loadJsonTranslationsFrom($packageLangPath);
        }
    }
}