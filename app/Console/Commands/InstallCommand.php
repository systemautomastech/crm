<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\AddOn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class InstallCommand extends Command
{
    protected $signature = 'app:install
                            {--force : Force installation even if already installed}';

    protected $description = 'Install the application';

    public function handle()
    {
        if ($this->isInstalled() && !$this->option('force')) {
            $this->error('Application is already installed. Use --force to reinstall.');
            return 1;
        }

        $this->info('Starting application installation...');

        // Generate app key if not exists
        if (empty(config('app.key'))) {
            $this->info('Generating application key...');
            Artisan::call('key:generate', ['--force' => true]);
        }

       // Handle foreign key constraints based on database type
       $dbType = config('database.default');
       if ($dbType === 'mysql') {
           DB::statement('SET FOREIGN_KEY_CHECKS=0');
       }

       // Drop all tables if they exist
       Artisan::call('migrate:fresh', ['--force' => true]);

       if ($dbType === 'mysql') {
           DB::statement('SET FOREIGN_KEY_CHECKS=1');
       }

       Artisan::call('db:seed', ['--force' => true]);

        // Install packages
        $this->installPackages();

        // Create installed file
        $this->createInstalledFile();

        $this->info('Application installed successfully!');
        return 0;
    }

    private function createInstalledFile()
    {
        File::put(storage_path('installed'), 'install ' . date('Y-m-d H:i:s'));
    }

    private function installPackages()
    {
        $this->info('Installing packages...');

        try {
            $modules = $this->getAllAvailableModules();

            foreach ($modules as $module) {
                $this->info("Installing module: {$module['alias']}");
                try {
                    $this->enableModule($module['name']);
                    $this->info("✓ Module {$module['alias']} installed successfully");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to install {$module['alias']}: " . $e->getMessage());
                }
            }

            $this->info('All packages installed successfully.');
        } catch (\Exception $e) {
            $this->error('Package installation failed: ' . $e->getMessage());
        }
    }

    private function getAllAvailableModules()
    {
        $modules = [];
        $packagesPath = base_path('packages/automas');

        if (!File::exists($packagesPath)) {
            return $modules;
        }

        $directories = File::directories($packagesPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $moduleJsonPath = "{$directory}/module.json";

            if (File::exists($moduleJsonPath)) {
                $moduleData = json_decode(File::get($moduleJsonPath), true);
                if ($moduleData) {
                    $modules[] = [
                        'name' => $moduleData['name'],
                        'alias' => $moduleData['alias'],
                        'description' => $moduleData['description'] ?? '',
                        'priority' => $moduleData['priority'] ?? 10,
                    ];
                }
            }
        }

        usort($modules, function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        return $modules;
    }

    private function ensureAddOnsTableExists()
    {
        if (!Schema::hasTable('add_ons')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                // Ignore migration error
            }

            if (!Schema::hasTable('add_ons')) {
                Schema::create('add_ons', function (Blueprint $table) {
                    $table->id();
                    $table->string('module');
                    $table->string('name');
                    $table->decimal('monthly_price', 8, 2)->default(0);
                    $table->decimal('yearly_price', 8, 2)->default(0);
                    $table->string('image')->nullable();
                    $table->boolean('is_enable')->default(false);
                    $table->boolean('for_admin')->default(false);
                    $table->string('package_name')->nullable();
                    $table->integer('priority')->default(0);
                    $table->timestamps();
                });
            }
        }
    }

    private function enableModule($moduleName)
    {
        // Validate module name to prevent path traversal
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $moduleName)) {
            throw new \Exception('Invalid module name');
        }

        $this->ensureAddOnsTableExists();

        $addon = AddOn::where('module', $moduleName)->first();
        $packageMigrationPath = 'packages/automas/' . $moduleName . '/src/Database/Migrations';

        if (empty($addon)) {
            $filePath = base_path('packages/automas/' . $moduleName . '/module.json');

            if (!file_exists($filePath)) {
                throw new \Exception('Module configuration not found');
            }

            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);

            if (!$data) {
                throw new \Exception('Invalid module configuration');
            }

            if (file_exists(base_path($packageMigrationPath))) {
                Artisan::call('migrate', [
                    '--path' => $packageMigrationPath,
                    '--force' => true,
                ]);
            }
            Artisan::call('package:seed', ['packageName' => $moduleName]);

            $addon = new AddOn;
            $addon->module = $data['name'];
            $addon->name = $data['alias'];
            $addon->monthly_price = $data['monthly_price'] ?? 0;
            $addon->yearly_price = $data['yearly_price'] ?? 0;
            $addon->package_name = $data['package_name'] ?? null;
            $addon->for_admin = $data['for_admin'] ?? false;
            $addon->priority = $data['priority'] ?? 0;
            $addon->is_enable = 1;
            $addon->save();
        } else {
            if (file_exists(base_path($packageMigrationPath))) {
                Artisan::call('migrate', [
                    '--path' => $packageMigrationPath,
                    '--force' => true,
                ]);
            }
            Artisan::call('package:seed', ['packageName' => $moduleName]);

            $addon->is_enable = 1;
            $addon->save();
        }
    }

    private function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }
}