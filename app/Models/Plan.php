<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Classes\Module;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'number_of_users',
        'custom_plan',
        'status',
        'free_plan',
        'modules',
        'package_price_yearly',
        'package_price_monthly',
        'price_per_user_monthly',
        'price_per_user_yearly',
        'storage_limit',
        'price_per_storage_monthly',
        'price_per_storage_yearly',
        'trial',
        'is_most_popular',
        'sort_order',
        'trial_days',
        'created_by',
    ];

    protected $casts = [
        'custom_plan' => 'boolean',
        'status' => 'boolean',
        'free_plan' => 'boolean',
        'trial' => 'boolean',
        'modules' => 'array',
        'package_price_yearly' => 'decimal:2',
        'package_price_monthly' => 'decimal:2',
        'price_per_user_monthly' => 'decimal:2',
        'price_per_user_yearly' => 'decimal:2',
        'storage_limit' => 'integer',
        'price_per_storage_monthly' => 'decimal:2',
        'price_per_storage_yearly' => 'decimal:2',
        'is_most_popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('custom_plan', false);
    }

    public function scopeCustom($query)
    {
        return $query->where('custom_plan', true);
    }

    public function getAvailableModulesForUser($userId)
    {
        return self::getUserSubscriptionModules($userId);
    }

    public static function getUserSubscriptionModules($userId = null)
    {
        $user = $userId ? User::find($userId) : auth()->user();

        if (!$user) {
            return [];
        }

        // Super admin has access to all modules
        if ($user->hasRole('superadmin')) {
            return (new Module())->allEnabled();
        }

        $availableModules = [];

        // Get modules from user's active plan
        if ($user->active_plan) {
            $plan = self::find($user->active_plan);
            if ($plan && $plan->modules) {
                $availableModules = array_merge($availableModules, $plan->modules);
            }
        }

        // Get user's individually activated modules
        $userActiveModules = UserActiveModule::where('user_id', $user->id)
            ->pluck('module')
            ->toArray();

        $availableModules = array_merge($availableModules, $userActiveModules);

        // Remove duplicates and ensure modules are actually enabled
        $enabledModules = (new Module())->allEnabled();
        $availableModules = array_unique(array_intersect($availableModules, $enabledModules));

        return array_values($availableModules);
    }
}