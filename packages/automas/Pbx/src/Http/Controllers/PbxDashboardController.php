<?php

namespace Automas\Pbx\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\Pbx\Models\PbxExtension;
use Automas\Pbx\Models\PbxSetting;
use Automas\Pbx\Services\PbxLiveStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PbxDashboardController extends Controller
{
    public function __construct(
        protected PbxLiveStatusService $liveStatusService
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $canViewAll = $user->can('view all extensions') || $user->can('manage extensions') || $user->can('manage pbx') || $user->type === 'company' || $user->type === 'superadmin';
        $canViewOwn = $user->can('view own extensions') || $user->can('view own call logs');

        $canCreate = $user->can('create extensions') || $user->can('manage extensions') || $user->can('manage pbx') || $user->type === 'company' || $user->type === 'superadmin';
        $canEdit = $user->can('edit extensions') || $user->can('manage extensions') || $user->can('manage pbx') || $user->type === 'company' || $user->type === 'superadmin';
        $canManageSettings = $user->can('manage settings') || $user->can('manage pbx') || $user->type === 'company' || $user->type === 'superadmin';

        abort_unless(
            $canViewAll || $canViewOwn,
            403,
            'You do not have permission to view PBX Dashboard.'
        );

        $creatorId = (int) creatorId();

        $setting = PbxSetting::query()
            ->where('created_by', $creatorId)
            ->first();

        // ── All Extensions for the workspace ────────────────────────────────
        $query = PbxExtension::query()
            ->with('user:id,name,avatar,email,type')
            ->where('created_by', $creatorId);

        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }

        $allExtensions = $query->orderBy('extension')->get();

        $totalExtensionsCount = $allExtensions->count();
        $activeExtensionsCount = $allExtensions->where('is_active', true)->count();
        $inactiveExtensionsCount = $totalExtensionsCount - $activeExtensionsCount;

        $assignedUserCount = $allExtensions->pluck('user_id')->filter()->unique()->count();
        $unassignedCount = $totalExtensionsCount - $assignedUserCount;

        // Map extensions for frontend
        $extensionsMapped = $allExtensions->map(fn($ext) => [
            'id' => $ext->id,
            'extension' => (string) $ext->extension,
            'user_id' => $ext->user_id,
            'user_name' => $ext->user?->name,
            'user_avatar' => $ext->user?->avatar,
            'user_email' => $ext->user?->email,
            'user_type' => $ext->user?->type,
            'caller_id' => $ext->caller_id,
            'is_active' => (bool) $ext->is_active,
            'created_at' => $ext->created_at?->format('M d, Y'),
        ])->values();

        // Fetch live statuses for all extensions
        $liveStatuses = $this->liveStatusService->getStatuses($allExtensions);

        $canCreateExtension = $setting !== null
            && $totalExtensionsCount < (int) $setting->max_extensions;

        return Inertia::render('Pbx/Dashboard/Index', [
            'isConfigured' => $setting !== null && (bool) $setting->is_enabled,
            'setting' => $setting ? [
                'max_extensions' => (int) $setting->max_extensions,
                'extension_start' => (int) $setting->extension_start,
                'extension_end' => (int) $setting->extension_end,
                'pbx_name' => $setting->pbx_name ?? 'Asterisk / amarSIP PBX',
                'ami_host' => $setting->ami_host ?? '',
                'ami_port' => $setting->ami_port ?? '',
                'is_enabled' => (bool) $setting->is_enabled,
            ] : null,
            'extensions' => $extensionsMapped,
            'liveStatuses' => $liveStatuses,
            'stats' => [
                'total_extensions' => $totalExtensionsCount,
                'active_extensions' => $activeExtensionsCount,
                'inactive_extensions' => $inactiveExtensionsCount,
                'assigned_users' => $assignedUserCount,
                'unassigned_extensions' => $unassignedCount,
            ],
            'canCreateExtension' => $canCreateExtension,
            'permissions' => [
                'canViewAllExtensions' => $canViewAll,
                'canCreateExtension' => $canCreateExtension,
                'canEditExtension' => $canEdit,
                'canManageSettings' => $canManageSettings,
            ],
        ]);
    }
}

