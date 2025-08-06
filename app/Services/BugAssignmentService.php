<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BugAssignmentService
{
    /**
     * Verificar el número de actividades activas de un usuario
     */
    private function getActiveActivitiesCount(string $userId): int
    {
        // Contar tareas activas
        $activeTasks = DB::table('tasks')
            ->where('user_id', $userId)
            ->whereIn('status', ['to do', 'in progress'])
            ->count();
            
        // Contar bugs activos
        $activeBugs = DB::table('bugs')
            ->where('user_id', $userId)
            ->whereIn('status', ['new', 'assigned', 'in progress'])
            ->count();
            
        return $activeTasks + $activeBugs;
    }
    
    /**
     * Asignar un bug a un usuario
     */
    public function assignBug(string $bugId, string $userId, string $assignedBy): JsonResponse
    {
        return DB::transaction(function () use ($bugId, $userId, $assignedBy) {
            $bug = Bug::findOrFail($bugId);
            $user = User::findOrFail($userId);
            
            // Verificar que el usuario tenga rol de developer o team_leader
            $userRole = $user->roles->first();
            if (!$userRole || !in_array($userRole->name, ['developer', 'team_leader'])) {
                return response()->json([
                    'error' => 'El usuario debe tener rol de desarrollador o team leader.',
                ], 400);
            }
            
            // Verificar límite de actividades activas (máximo 3)
            $activeActivities = $this->getActiveActivitiesCount($userId);
            if ($activeActivities >= 3) {
                return response()->json([
                    'error' => 'El usuario ya tiene el máximo de 3 actividades activas (tareas o bugs). Debe completar o pausar alguna actividad antes de asignar una nueva.',
                ], 400);
            }
            
            // Verificar que el bug no esté siendo trabajado actualmente
            if ($bug->is_working) {
                return response()->json([
                    'error' => 'No se puede reasignar un bug que está siendo trabajado actualmente.',
                ], 400);
            }
            
            // Actualizar bug
            $bug->update([
                'user_id' => $userId,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'status' => $bug->status === 'new' ? 'assigned' : $bug->status,
            ]);
            
            return response()->json([
                'message' => 'Bug asignado exitosamente.',
                'bug' => $bug->load(['user', 'assignedBy']),
            ]);
        });
    }
    
    /**
     * Auto-asignar un bug (desarrollador se asigna a sí mismo)
     */
    public function selfAssignBug(string $bugId, string $userId): JsonResponse
    {
        return DB::transaction(function () use ($bugId, $userId) {
            $bug = Bug::findOrFail($bugId);
            $user = User::findOrFail($userId);
            
            // Verificar que el usuario tenga rol de developer o team_leader
            $userRole = $user->roles->first();
            if (!$userRole || !in_array($userRole->name, ['developer', 'team_leader'])) {
                return response()->json([
                    'error' => 'Solo los desarrolladores y team leaders pueden auto-asignarse bugs.',
                ], 400);
            }
            
            // Verificar límite de actividades activas (máximo 3)
            $activeActivities = $this->getActiveActivitiesCount($userId);
            if ($activeActivities >= 3) {
                return response()->json([
                    'error' => 'Ya tienes el máximo de 3 actividades activas (tareas o bugs). Debes completar o pausar alguna actividad antes de asignarte una nueva.',
                ], 400);
            }
            
            // Verificar que el bug no esté asignado
            if ($bug->user_id) {
                return response()->json([
                    'error' => 'Este bug ya está asignado a otro usuario.',
                ], 400);
            }
            
            // Verificar que el bug no esté siendo trabajado
            if ($bug->is_working) {
                return response()->json([
                    'error' => 'Este bug ya está siendo trabajado por otro usuario.',
                ], 400);
            }
            
            // Actualizar bug
            $bug->update([
                'user_id' => $userId,
                'assigned_by' => $userId,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);
            
            return response()->json([
                'message' => 'Bug auto-asignado exitosamente.',
                'bug' => $bug->load(['user', 'assignedBy']),
            ]);
        });
    }
    
    /**
     * Desasignar un bug
     */
    public function unassignBug(string $bugId): JsonResponse
    {
        return DB::transaction(function () use ($bugId) {
            $bug = Bug::findOrFail($bugId);
            
            // Verificar que el bug no esté siendo trabajado
            if ($bug->is_working) {
                return response()->json([
                    'error' => 'No se puede desasignar un bug que está siendo trabajado actualmente.',
                ], 400);
            }
            
            // Actualizar bug
            $bug->update([
                'user_id' => null,
                'assigned_by' => null,
                'assigned_at' => null,
                'status' => 'new',
            ]);
            
            return response()->json([
                'message' => 'Bug desasignado exitosamente.',
                'bug' => $bug,
            ]);
        });
    }
    
    /**
     * Obtener bugs disponibles para asignar
     */
    public function getAvailableBugs(): JsonResponse
    {
        $bugs = Bug::whereNull('user_id')
            ->where('status', 'new')
            ->with(['sprint', 'project'])
            ->orderBy('priority_score', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json([
            'bugs' => $bugs,
        ]);
    }
    
    /**
     * Obtener bugs asignados a un usuario
     */
    public function getAssignedBugs(string $userId): JsonResponse
    {
        $bugs = Bug::where('user_id', $userId)
            ->with(['sprint', 'project', 'assignedBy'])
            ->orderBy('priority_score', 'desc')
            ->orderBy('assigned_at', 'desc')
            ->get();
            
        return response()->json([
            'bugs' => $bugs,
        ]);
    }
    
    /**
     * Obtener bugs que requieren atención (alta prioridad sin asignar)
     */
    public function getBugsRequiringAttention(): JsonResponse
    {
        $bugs = Bug::whereNull('user_id')
            ->whereIn('importance', ['high', 'critical'])
            ->whereIn('status', ['new', 'assigned'])
            ->with(['sprint', 'project'])
            ->orderBy('priority_score', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json([
            'bugs' => $bugs,
        ]);
    }
    
    /**
     * Obtener estadísticas de asignación
     */
    public function getAssignmentStats(): JsonResponse
    {
        $stats = [
            'total_bugs' => Bug::count(),
            'unassigned_bugs' => Bug::whereNull('user_id')->count(),
            'assigned_bugs' => Bug::whereNotNull('user_id')->count(),
            'high_priority_unassigned' => Bug::whereNull('user_id')
                ->whereIn('importance', ['high', 'critical'])
                ->count(),
            'bugs_by_status' => [
                'new' => Bug::where('status', 'new')->count(),
                'assigned' => Bug::where('status', 'assigned')->count(),
                'in_progress' => Bug::where('status', 'in progress')->count(),
                'resolved' => Bug::where('status', 'resolved')->count(),
                'verified' => Bug::where('status', 'verified')->count(),
                'closed' => Bug::where('status', 'closed')->count(),
                'reopened' => Bug::where('status', 'reopened')->count(),
            ],
            'bugs_by_importance' => [
                'low' => Bug::where('importance', 'low')->count(),
                'medium' => Bug::where('importance', 'medium')->count(),
                'high' => Bug::where('importance', 'high')->count(),
                'critical' => Bug::where('importance', 'critical')->count(),
            ],
        ];
        
        return response()->json([
            'stats' => $stats,
        ]);
    }
} 