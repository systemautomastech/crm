<?php

namespace Automas\LandingPage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use App\Models\AddOn;
use App\Models\Plan;
use App\Models\User;
use Automas\LandingPage\Models\LandingPageSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Automas\LandingPage\Models\CustomPage;
use Illuminate\Support\Facades\Auth;
use Automas\LandingPage\Http\Requests\StoreLandingPageRequest;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        $settings = Cache::remember('landing_page_settings', 3600, function () {
            return LandingPageSetting::first();
        });

        if (!isLandingPageEnabled()) {
            $enableRegistration = admin_setting('enableRegistration');

            return Inertia::render('auth/login', [
                'canResetPassword' => Route::has('password.request'),
                'status' => session('status'),
                'enableRegistration' => $enableRegistration === 'on',
            ]);
        }

        $enableRegistration = admin_setting('enableRegistration');

        $settingsData = $settings ? $settings->toArray() : [];
        $settingsData['enable_registration'] = $enableRegistration === 'on';
        $settingsData['is_authenticated'] = $request->user() !== null;

        // Get active plans from the main app ordered by sort_order
        $plans = Plan::where('status', true)
            ->where('custom_plan', false)
            ->withCount('orders')
            ->orderBy('sort_order', 'asc')
            ->get();

        // Get active modules/addons
        $activeModules = AddOn::where('is_enable', true)
            ->whereNotIn('module', User::$superadmin_activated_module)
            ->select('module', 'name as alias', 'image', 'monthly_price', 'yearly_price')
            ->get();

        return Inertia::render('LandingPage/Landing', [
            'auth' => [
                'user' => $request->user(),
                'lang' => app()->getLocale()
            ],
            'settings' => $settingsData,
            'plans' => $plans->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'package_price_monthly' => $plan->package_price_monthly,
                    'package_price_yearly' => $plan->package_price_yearly,
                    'number_of_users' => $plan->number_of_users,
                    'storage_limit' => $plan->storage_limit,
                    'modules' => $plan->modules ?? [],
                    'free_plan' => $plan->free_plan,
                    'trial' => $plan->trial,
                    'trial_days' => $plan->trial_days,
                    'is_most_popular' => (bool)$plan->is_most_popular,
                    'sort_order' => $plan->sort_order,
                    'orders_count' => $plan->orders_count
                ];
            }),
            'activeModules' => $activeModules,
        ]);
    }

    public function addons(Request $request)
    {
        $landingPageSettings = LandingPageSetting::first();
        $query = AddOn::where('is_enable', true)->whereNotIn('module', User::$superadmin_activated_module);
        
        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Category filter (if you have categories)
        if ($request->filled('category')) {
            $query->where('module', $request->category);
        }
        
        // Price filter
        if ($request->filled('price')) {
            $priceColumn = $request->get('price_type', 'monthly') === 'yearly' ? 'yearly_price' : 'monthly_price';
            
            switch ($request->price) {
                case 'free':
                    $query->where(function($q) use ($priceColumn) {
                        $q->whereNull($priceColumn)->orWhere($priceColumn, 0);
                    });
                    break;
                case '0-50':
                    $query->whereBetween($priceColumn, [0.00, 50]);
                    break;
                case '50-100':
                    $query->whereBetween($priceColumn, [50.00, 100]);
                    break;
                case '100+':
                    $query->where($priceColumn, '>', 100);
                    break;
            }
        }
        
        // Sorting
        $priceColumn = $request->get('price_type', 'monthly') === 'yearly' ? 'yearly_price' : 'monthly_price';
        
        switch ($request->get('sort', 'name')) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'price_low':
                $query->orderBy($priceColumn, 'asc');
                break;
            case 'price_high':
                $query->orderBy($priceColumn, 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
                break;
        }
        
        $addons = $query->paginate($landingPageSettings->config_sections['sections']['addons']['per_page'] ?? 20);
        $categories = AddOn::where('is_enable', true)
            ->distinct()
            ->pluck('module')
            ->filter()
            ->values();
        
        

        return Inertia::render('LandingPage/Addons', [
            'addons' => $addons,
            'settings' => $landingPageSettings,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'price', 'price_type', 'sort'])
        ]);
    }

    public function pricing(Request $request)
    {
        // Get active plans from the main app
        $plans = Plan::where('status', true)
            ->where('custom_plan', false)
            ->withCount('orders')
            ->orderBy('sort_order', 'asc')
            ->get();

        // Get active modules/addons
        $activeModules = AddOn::where('is_enable', true)
            ->whereNotIn('module', User::$superadmin_activated_module)
            ->select('module', 'name as alias', 'image', 'monthly_price', 'yearly_price')
            ->get();

        $landingPageSettings = LandingPageSetting::first();
        $enableRegistration = admin_setting('enableRegistration');

        $settingsData = $landingPageSettings ? $landingPageSettings->toArray() : [];
        $settingsData['enable_registration'] = $enableRegistration === 'on';
        $settingsData['is_authenticated'] = $request->user() !== null;

        return Inertia::render('LandingPage/Pricing', [
            'plans' => $plans->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'package_price_monthly' => $plan->package_price_monthly,
                    'package_price_yearly' => $plan->package_price_yearly,
                    'number_of_users' => $plan->number_of_users,
                    'storage_limit' => $plan->storage_limit,
                    'modules' => $plan->modules ?? [],
                    'free_plan' => $plan->free_plan,
                    'trial' => $plan->trial,
                    'trial_days' => $plan->trial_days,
                    'orders_count' => $plan->orders_count,
                    'position' => $plan->position ?? $plan->sort_order ?? 0,
                    'is_most_popular' => (bool) ($plan->is_most_popular ?? $plan->is_popular ?? false),
                ];
            }),
            'activeModules' => $activeModules,
            'settings' => $settingsData,

        ]);
    }

    public function settings()
    {
        if(Auth::user()->can('manage-landing-page')){
            $settings = LandingPageSetting::first();
            $customPages = CustomPage::where('is_active', true)->select('id', 'title', 'slug')->get();
            return Inertia::render('LandingPage/Settings', [
                'settings' => $settings ?: [
                    'company_name' => '',
                    'contact_email' => '',
                    'contact_phone' => '',
                    'contact_address' => '',
                    'config_sections' => [
                        'sections' => [],
                        'section_visibility' => [
                            'header' => true,
                            'hero' => true,
                            'stats' => true,
                            'features' => true,
                            'modules' => true,
                            'benefits' => true,
                            'gallery' => true,
                            'cta' => true,
                            'footer' => true
                        ],
                        'section_order' => ['header', 'hero', 'stats', 'features', 'modules', 'benefits', 'gallery', 'cta', 'footer']
                    ]
                ],
                'customPages' => $customPages
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreLandingPageRequest $request)
    {
        if(Auth::user()->can('edit-landing-page')){
            $validated = $request->validated();

            // Handle image paths - store only filename
            if (isset($validated['config_sections']['sections'])) {
                $this->processImagePaths($validated['config_sections']['sections']);
            }

            LandingPageSetting::updateOrCreate(['id' => 1], $validated);

            return back()->with('success', __('Settings saved successfully'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    private function processImagePaths(&$sections)
    {
        foreach ($sections as $sectionKey => &$sectionData) {
            if (is_array($sectionData)) {
                // Handle single images (hero, cta)
                if (isset($sectionData['image'])) {
                    $sectionData['image'] = $this->processImagePath($sectionData['image']);
                }
                
                // Handle gallery images array
                if (isset($sectionData['images']) && is_array($sectionData['images'])) {
                    $sectionData['images'] = array_map([$this, 'processImagePath'], $sectionData['images']);
                }
            }
        }
    }

    private function processImagePath($imagePath)
    {
        if (strpos($imagePath, 'packages/automas') !== false) {
            return $imagePath;
        }
        return basename($imagePath);
    }
}