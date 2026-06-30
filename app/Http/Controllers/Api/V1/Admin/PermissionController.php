<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class PermissionController extends Controller
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
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $data = Permission::all();
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
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'parent_id' => 'required|integer'
        ]);
        $data = Permission::create($request->only(['name', 'title', 'parent_id']));
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
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $item = Permission::query()->findOrFail($id);
        return resp(1, 'Successful!', $item, Response::HTTP_OK);
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
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $item = Permission::query()->findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'parent_id' => 'required|integer'
        ]);
        $item->update($request->only(['name', 'title', 'parent_id']));
        return resp(1, 'Successful!', $item->refresh(), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $item = \App\Models\Admin\Permission::query()->findOrFail($id);
        $item->delete();
        return resp(1, 'Successful!', $item, Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['roles'] = Role::all();
        $data['permissions'] = Permission::all();
        $data['users'] = User::query()->with('roles')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function permList()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $permissions = Permission::all();

        $groupedPermissions = $permissions->groupBy('parent_id');

        // Build the hierarchy starting from the root (parent_id = 0)
        $hierarchy = self::buildHierarchy($groupedPermissions);

        return resp(1, 'Successful!', $hierarchy, Response::HTTP_OK);
    }
    public function getRolePermList()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_permissions', 'manage_audit_permissions']);
        }

        $permissions = Permission::all();

        $groupedPermissions = $permissions->groupBy('parent_id');

        // Build the hierarchy starting from the root (parent_id = 0)
        $data['perm_list'] = self::buildHierarchy($groupedPermissions);
        $data['roles'] = Role::all();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }


    // Define a function to recursively build the hierarchy
    public static function buildHierarchy($permissions, $parentId = 0) {
        $tree = [];

        // Check if the given parent_id exists in the group
        if (isset($permissions[$parentId])) {
            // Loop through children with the given parent_id
            foreach ($permissions[$parentId] as $permission) {
                // Recursively build the hierarchy for children
                $children = self::buildHierarchy($permissions, $permission->id);

                // Add children to the current permission
                if ($children->isNotEmpty()) {
                    $permission->children = $children;
                }

                // Add the permission to the tree
                $tree[] = $permission;
            }
        }

        return collect($tree);
    }

    public function getUsersWithPermissions()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            // Allow access since the user is a Super Admin
        } else {
            // Check if the user has the required permissions
            $this->authorizeAny(['manage_assign_role','manage_audit_assign_role']);
        }

        // Retrieve all users with their roles and permissions
        $users = User::with(['roles.permissions'])->get()->map(function ($user) {
            // Collect all unique permissions for the user
            $permissions = $user->roles->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })->unique();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'permissions' => $permissions->values(),
            ];
        });

        return resp(1, 'Successful!', $users, Response::HTTP_OK);
    }
    public function getUsersWithRoles()
    {
        $data['users_list'] = User::with('roles:id,name', 'employeeDetail.department', 'userdesignation')->get()->map(function ($user) {
            $roles = $user->roles;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $roles,
                'employee_detail' => $user->employeeDetail,
                'designation' => $user->userdesignation,
            ];
        });

        $data['roles_list'] = Role::all();

        //$users = User::with('roles:name')->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }



}
