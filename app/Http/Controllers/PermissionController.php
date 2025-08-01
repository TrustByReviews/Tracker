<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    /**
     * Display the permissions management page
     */
    public function index(): Response
    {
        $permissions = Permission::active()->orderBy('module')->orderBy('display_name')->get();
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Get all permissions grouped by module
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::active()
            ->orderBy('module')
            ->orderBy('display_name')
            ->get()
            ->groupBy('module');

        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(User $user): JsonResponse
    {
        $directPermissions = $user->directPermissions()
            ->withPivot(['type', 'expires_at', 'reason', 'granted_by'])
            ->get();

        $rolePermissions = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->map(function ($permission) use ($role) {
                    $permission->source = 'role';
                    $permission->role_name = $role->name;
                    return $permission;
                });
            });

        return response()->json([
            'direct_permissions' => $directPermissions,
            'role_permissions' => $rolePermissions,
            'all_permissions' => $user->getAllPermissions(),
        ]);
    }

    /**
     * Grant permission to user
     */
    public function grantPermission(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permission_name' => 'required|string|exists:permissions,name',
            'type' => 'required|in:temporary,permanent',
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $success = $user->grantPermission(
            $request->permission_name,
            $request->type,
            $request->reason,
            $request->expires_at
        );

        if ($success) {
            return response()->json([
                'message' => 'Permission granted successfully',
                'user' => $user->load('directPermissions'),
            ]);
        }

        return response()->json([
            'message' => 'Failed to grant permission',
        ], 400);
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'permission_name' => 'required|string|exists:permissions,name',
        ]);

        $success = $user->revokePermission($request->permission_name);

        if ($success) {
            return response()->json([
                'message' => 'Permission revoked successfully',
                'user' => $user->load('directPermissions'),
            ]);
        }

        return response()->json([
            'message' => 'Failed to revoke permission',
        ], 400);
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions(Role $role): JsonResponse
    {
        $permissions = $role->permissions()->orderBy('module')->orderBy('display_name')->get();

        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permission_ids);

        return response()->json([
            'message' => 'Role permissions updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Get expired permissions
     */
    public function getExpiredPermissions(): JsonResponse
    {
        $expiredPermissions = \App\Models\UserPermission::expired()
            ->with(['user', 'permission', 'grantedBy'])
            ->get();

        return response()->json([
            'expired_permissions' => $expiredPermissions,
        ]);
    }

    /**
     * Clean up expired permissions
     */
    public function cleanupExpiredPermissions(): JsonResponse
    {
        $deletedCount = \App\Models\UserPermission::expired()->delete();

        return response()->json([
            'message' => "Cleaned up {$deletedCount} expired permissions",
        ]);
    }
} 