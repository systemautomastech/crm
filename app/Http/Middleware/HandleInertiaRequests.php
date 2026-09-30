<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\File;
use App\Classes\Module;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if (!$this->isInstalled()) {
            return [];
        }

        // Get locale and layout direction
        $locale = $request->user() ? ($request->user()->lang ?? $this->getSuperAdminLang()) : $this->getSuperAdminLang();
        app()->setLocale($locale);

        // Get layout direction from user or auto-detect for guests
        $rtlLanguages = ['ar', 'he'];
        $layoutDirection = $request->user() && $request->user()->layout_direction
            ? $request->user()->layout_direction
            : (in_array($locale, $rtlLanguages) ? 'rtl' : 'ltr');

        // language file
        $languageFile = resource_path('lang/language.json');
        $availableLanguages = [];
        if (file_exists($languageFile)) {
            $languages = json_decode(file_get_contents($languageFile), true) ?? [];
            $availableLanguages = array_values($languages);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? array_merge(
                        $request->user()->loadMissing('employee')->toArray(),
                        [
                            'permissions' => $this->getUserPermissions($request->user()),
                            'roles' => $this->getUserRoles($request->user()),
                            'activatedPackages' => ActivatedModule(),
                        ]
                    )
                    : ['activatedPackages' => ActivatedModule()],
                'impersonating' => $request->session()->has('impersonator_id'),
                'lang' => $locale,
                'layout_direction' => $layoutDirection,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'packages' => (new Module())->allModules(),
            'adminAllSetting' => $request->user() ? getAdminAllSetting() : getAdminAllSetting(true),
            'companyAllSetting' => $request->user() ? getCompanyAllSetting($request->user()->id) : [],
            'imageUrlPrefix' => getImageUrlPrefix(),
            'baseUrl' => url('/'),
            'currencies' => config('default_currency.currencies', []),
            'availableLanguages' => $availableLanguages,
        ];
    }

    public function onException($request, $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return redirect()->route('users.index')->with('error', 'Permission denied');
        }

        return parent::onException($request, $exception);
    }

    /**
     * Get user permissions (placeholder - implement based on your permission system)
     */
    private function getUserPermissions($user): array
    {
        if (method_exists($user, 'getAllPermissions')) {
            return $user->getAllPermissions()->pluck('name')->toArray();
        }
        return [];
    }

    private function getUserRoles($user): array
    {
        if (method_exists($user, 'getRoleNames')) {
            return $user->getRoleNames()->toArray();
        }
        return [];
    }

    /**
     * Get superadmin language if user lang is not set
     */
    private function getSuperAdminLang(): string
    {
        return admin_setting('defaultLanguage') ? admin_setting('defaultLanguage') : 'en';
    }

    private function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }
}
