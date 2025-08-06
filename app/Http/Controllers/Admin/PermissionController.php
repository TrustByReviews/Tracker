<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    /**
     * Mostrar la página de gestión de permisos
     */
    public function index(): Response
    {
        // Obtener usuarios con sus roles y permisos
        $users = User::with(['roles', 'projects'])
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'team_leader']);
            })
            ->get()
            ->map(function ($user) {
                $user->has_unlimited_tasks = $user->hasPermission('unlimited_simultaneous_tasks');
                $user->active_tasks_count = $user->tasks()->where('is_working', true)->count();
                return $user;
            });

        // Obtener proyectos para asignación por equipo
        $projects = Project::with(['users'])->get();

        // Obtener estadísticas
        $stats = [
            'total_users' => $users->count(),
            'users_with_permission' => $users->where('has_unlimited_tasks', true)->count(),
            'users_without_permission' => $users->where('has_unlimited_tasks', false)->count(),
            'total_projects' => $projects->count(),
        ];

        return Inertia::render('Admin/Permissions/Index', [
            'users' => $users,
            'projects' => $projects,
            'stats' => $stats,
        ]);
    }

    /**
     * Otorgar permiso de tareas simultáneas a un usuario
     */
    public function grantPermission(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|string|exists:users,id',
                'reason' => 'nullable|string|max:500',
                'expires_at' => 'nullable|date|after:now',
            ]);

            $user = User::findOrFail($request->user_id);
            
            // Verificar que el usuario no sea admin (ya tiene el permiso)
            if ($user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los administradores ya tienen este permiso por defecto'
                ], 400);
            }

            // Otorgar permiso
            $result = $user->grantPermission(
                'unlimited_simultaneous_tasks',
                'temporary',
                $request->reason,
                $request->expires_at
            );

            if ($result) {
                Log::info('Permiso de tareas simultáneas otorgado', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'granted_by' => auth()->id(),
                    'reason' => $request->reason,
                    'expires_at' => $request->expires_at
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Permiso otorgado exitosamente a {$user->name}",
                    'user' => $user->fresh(['roles', 'projects'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al otorgar el permiso'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error al otorgar permiso de tareas simultáneas', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Revocar permiso de tareas simultáneas a un usuario
     */
    public function revokePermission(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|string|exists:users,id',
            ]);

            $user = User::findOrFail($request->user_id);
            
            // Verificar que el usuario no sea admin
            if ($user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede revocar el permiso a un administrador'
                ], 400);
            }

            // Revocar permiso
            $result = $user->revokePermission('unlimited_simultaneous_tasks');

            if ($result) {
                Log::info('Permiso de tareas simultáneas revocado', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'revoked_by' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Permiso revocado exitosamente a {$user->name}",
                    'user' => $user->fresh(['roles', 'projects'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al revocar el permiso'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error al revocar permiso de tareas simultáneas', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Otorgar permiso a todo un equipo (proyecto)
     */
    public function grantPermissionToTeam(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|string|exists:projects,id',
                'reason' => 'nullable|string|max:500',
                'expires_at' => 'nullable|date|after:now',
            ]);

            $project = Project::with(['users'])->findOrFail($request->project_id);
            $users = $project->users()->whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'team_leader']);
            })->get();

            $grantedCount = 0;
            $errors = [];

            foreach ($users as $user) {
                try {
                    $result = $user->grantPermission(
                        'unlimited_simultaneous_tasks',
                        'temporary',
                        $request->reason,
                        $request->expires_at
                    );

                    if ($result) {
                        $grantedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error con {$user->name}: " . $e->getMessage();
                }
            }

            Log::info('Permiso de tareas simultáneas otorgado a equipo', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'users_count' => $users->count(),
                'granted_count' => $grantedCount,
                'granted_by' => auth()->id(),
                'reason' => $request->reason,
                'expires_at' => $request->expires_at
            ]);

            return response()->json([
                'success' => true,
                'message' => "Permiso otorgado a {$grantedCount} usuarios del equipo {$project->name}",
                'granted_count' => $grantedCount,
                'total_users' => $users->count(),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error al otorgar permiso a equipo', [
                'error' => $e->getMessage(),
                'project_id' => $request->project_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Revocar permiso a todo un equipo (proyecto)
     */
    public function revokePermissionFromTeam(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'project_id' => 'required|string|exists:projects,id',
            ]);

            $project = Project::with(['users'])->findOrFail($request->project_id);
            $users = $project->users()->whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'team_leader']);
            })->get();

            $revokedCount = 0;
            $errors = [];

            foreach ($users as $user) {
                try {
                    $result = $user->revokePermission('unlimited_simultaneous_tasks');

                    if ($result) {
                        $revokedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error con {$user->name}: " . $e->getMessage();
                }
            }

            Log::info('Permiso de tareas simultáneas revocado a equipo', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'users_count' => $users->count(),
                'revoked_count' => $revokedCount,
                'revoked_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Permiso revocado a {$revokedCount} usuarios del equipo {$project->name}",
                'revoked_count' => $revokedCount,
                'total_users' => $users->count(),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error al revocar permiso a equipo', [
                'error' => $e->getMessage(),
                'project_id' => $request->project_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener historial de permisos de un usuario
     */
    public function getUserPermissionHistory(Request $request, string $userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            
            $permissions = DB::table('user_permissions')
                ->where('user_id', $userId)
                ->where('permission_name', 'unlimited_simultaneous_tasks')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'permissions' => $permissions,
                'user' => $user->only(['id', 'name', 'email'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener historial de permisos', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
