<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->withCount('partners')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $managers = User::orderBy('name')->get();
        $roles = Role::internal()->orderBy('sort_order')->get();

        return view('users.create', compact('managers', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,slug,type,internal',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'exists:roles,id',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $role = Role::where('slug', $request->role)->where('type', 'internal')->first();

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $role->id,
            'password' => Hash::make($request->password),
            'avatar' => $avatarPath,
            'manager_id' => $request->manager_id,
        ]);

        $this->syncRoles($user, $role->id, $request->input('additional_roles', []));

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $managers = User::where('id', '!=', $user->id)->orderBy('name')->get();
        $roles = Role::internal()->orderBy('sort_order')->get();
        $additionalRoleIds = $user->roles->pluck('id')
            ->reject(fn ($id) => $id === $user->role_id)->values()->all();

        return view('users.edit', compact('user', 'managers', 'roles', 'additionalRoleIds'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|exists:roles,slug,type,internal',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'exists:roles,id',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $role = Role::where('slug', $request->role)->where('type', 'internal')->first();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $role->id,
            'manager_id' => $request->manager_id,
        ];

        // Strip directory-synced fields to prevent accidental overwrites
        if ($user->isSyncedFromDirectory()) {
            foreach (config('team.directory.synced_fields', []) as $field) {
                unset($data[$field]);
            }
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        $this->syncRoles($user, $role->id, $request->input('additional_roles', []));

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Sync a user's role_user pivot to the primary role plus any additional
     * roles (internal only). The primary role_id remains the landing/back-compat
     * role; permission checks union across all of them.
     *
     * @param  array<int|string>  $additional  role ids
     */
    private function syncRoles(User $user, int $primaryRoleId, array $additional): void
    {
        $ids = collect($additional)->map(fn ($v) => (int) $v)->push($primaryRoleId)->unique();
        $valid = Role::internal()->whereIn('id', $ids)->pluck('id')->all();
        $user->roles()->sync($valid);
    }

    public function destroy(User $user)
    {
        // Check if user has partners assigned
        if ($user->partners()->count() > 0) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user with assigned partners. Please reassign partners first.');
        }

        // Delete avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
