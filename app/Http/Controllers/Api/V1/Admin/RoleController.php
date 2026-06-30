<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_permissions','manage_roles']);
        }

        $data = Role::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_permissions','manage_roles']);
        }

        $request->validate([
           'name' => 'required|string|max:255',
        ]);
        $data = Role::create($request->only(['name']));
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_roles']);
        }

        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy('parent_id');
        $permissions_tree = PermissionController::buildHierarchy($groupedPermissions);

        $role = Role::query()->findOrFail($id);
        $role->perms = $role->permissions->pluck('name');

        return resp(1, 'Successful!', ['role' => $role, 'permissions_tree' => $permissions_tree], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_roles']);
        }

        $item = Role::query()->findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $item->update($request->only(['name']));
        return resp(1, 'Successful!', $item->refresh(), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
//        $item = Role::query()->findOrFail($id)->delete();
//        return resp(1, 'Successful!', $item, Response::HTTP_OK);
    }

    public function attachPermission(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_assign_role']);
        }

        $request->validate([
            'role_id' => 'required',
            'perm_name' => 'required'
        ]);
        $role = Role::query()->findOrFail($request->role_id);

        $role->givePermissionTo($request->perm_name);

        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
    public function revokePermission(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_assign_role']);
        }

        $request->validate([
            'role_id' => 'required',
            'perm_name' => 'required'
        ]);
        $role = Role::query()->findOrFail($request->role_id);

        $role->revokePermissionTo($request->perm_name);

        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function syncPermission(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_assign_role']);
        }

        $request->validate([
            'role_id' => 'required',
            'permissions' => 'required|array'
        ]);

        $role = Role::query()->findOrFail($request->role_id);

        $role->syncPermissions($request->permissions);

        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function syncUserRoles(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_assign_role']);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_ids' => 'required|array|exists:roles,id',
        ]);

        $user = User::query()->findOrFail($request->user_id);

        $user->syncRoles($request->role_ids);

        Artisan::call('permission:cache-reset');

        return resp(1, 'Successful!', $user->load('roles'), Response::HTTP_OK);
    }
    
    public function syncMultipleUserRoles(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_assign_role']);
        }

        $request->validate([
            'user_ids' => 'required|array|exists:users,id',
            'role_ids' => 'required|array|exists:roles,id',
        ]);

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            $user->syncRoles($request->role_ids);
        }

        Artisan::call('permission:cache-reset');

        return resp(1, 'Roles synchronized successfully!', [], Response::HTTP_OK);
    }

}
