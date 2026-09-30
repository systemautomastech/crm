<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserGroupController extends Controller
{
    // ─── Index ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }

        $groups = UserGroup::where('created_by', creatorId())
            ->when($request->name, fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
            ->when($request->has('is_active') && $request->is_active !== '', fn($q) => $q->where('is_active', $request->is_active))
            ->when($request->sort, fn($q) => $q->orderBy($request->sort, $request->direction ?? 'asc'), fn($q) => $q->latest())
            ->withCount('users')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('UserGroups/Index', [
            'groups'  => $groups,
            'filters' => $request->only(['name', 'is_active']),
        ]);
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function create()
    {
        if (!Auth::user()->can('create-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }

        return Inertia::render('UserGroups/Create', [
            'users' => $this->getWorkspaceUsers(),
        ]);
    }

    // ─── Store ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if (!Auth::user()->can('create-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'integer|exists:users,id',
        ]);

        $group = UserGroup::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $validated['is_active'] ?? true,
            'creator_id'  => Auth::id(),
            'created_by'  => creatorId(),
        ]);

        if (!empty($validated['user_ids'])) {
            $validUserIds = $this->validateWorkspaceUsers($validated['user_ids']);
            $group->users()->sync($validUserIds);
        }

        return redirect()
            ->route('user-groups.index')
            ->with('success', __('User group created successfully.'));
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function show(UserGroup $userGroup)
    {
        if (!Auth::user()->can('manage-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }
        if (!$this->canAccess($userGroup)) {
            return redirect()->route('user-groups.index')->with('error', __('Access denied'));
        }

        $userGroup->load(['users:id,name,email,type', 'creator:id,name']);

        return Inertia::render('UserGroups/Show', [
            'group' => $userGroup,
        ]);
    }

    // ─── Edit ────────────────────────────────────────────────────────────────

    public function edit(UserGroup $userGroup)
    {
        if (!Auth::user()->can('edit-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }
        if (!$this->canAccess($userGroup)) {
            return back()->with('error', __('Access denied'));
        }

        $userGroup->load('users:id,name,email');

        return Inertia::render('UserGroups/Edit', [
            'group' => $userGroup,
            'users' => $this->getWorkspaceUsers(),
        ]);
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function update(Request $request, UserGroup $userGroup)
    {
        if (!Auth::user()->can('edit-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }
        if (!$this->canAccess($userGroup)) {
            return back()->with('error', __('Access denied'));
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'integer|exists:users,id',
        ]);

        $userGroup->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $validated['is_active'] ?? true,
        ]);

        $validUserIds = !empty($validated['user_ids'])
            ? $this->validateWorkspaceUsers($validated['user_ids'])
            : [];

        $userGroup->users()->sync($validUserIds);

        return redirect()
            ->route('user-groups.index')
            ->with('success', __('User group updated successfully.'));
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(UserGroup $userGroup)
    {
        if (!Auth::user()->can('delete-user-groups')) {
            return back()->with('error', __('Permission denied'));
        }
        if (!$this->canAccess($userGroup)) {
            return back()->with('error', __('Access denied'));
        }

        $userGroup->users()->detach();
        $userGroup->delete();

        return redirect()
            ->route('user-groups.index')
            ->with('success', __('User group deleted successfully.'));
    }

    // ─── API: list active groups for dropdowns ────────────────────────────────

    public function listActive()
    {
        return response()->json(
            UserGroup::where('created_by', creatorId())
                ->active()
                ->select('id', 'name')
                ->withCount('users')
                ->get()
        );
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function canAccess(UserGroup $group): bool
    {
        return $group->created_by == creatorId();
    }

    private function getWorkspaceUsers()
    {
        return User::emp()
            ->where('created_by', creatorId())
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }

    private function validateWorkspaceUsers(array $userIds): array
    {
        return User::whereIn('id', $userIds)
            ->where('created_by', creatorId())
            ->pluck('id')
            ->toArray();
    }
}
