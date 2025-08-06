<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaskAssignmentService
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
     * Asignar tarea a un desarrollador por un team leader
     */
    public function assignTaskByTeamLeader(Task $task, User $developer, User $teamLeader): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que el team leader tiene permisos sobre el proyecto
            if (!$this->canTeamLeaderAssignToProject($teamLeader, $task->project)) {
                throw new \Exception('Team leader no tiene permisos para asignar tareas en este proyecto');
            }
            
            // Verificar que el desarrollador está asignado al proyecto
            if (!$task->project->users()->where('users.id', $developer->id)->exists()) {
                throw new \Exception('El desarrollador no está asignado a este proyecto');
            }
            
            // Verificar límite de actividades activas (máximo 3)
            $activeActivities = $this->getActiveActivitiesCount($developer->id);
            if ($activeActivities >= 3) {
                throw new \Exception('El desarrollador ya tiene el máximo de 3 actividades activas (tareas o bugs). Debe completar o pausar alguna actividad antes de asignar una nueva.');
            }
            
            // Verificar que la tarea no está ya asignada
            if ($task->user_id) {
                throw new \Exception('La tarea ya está asignada a otro desarrollador');
            }
            
            // Asignar la tarea
            $task->update([
                'user_id' => $developer->id,
                'assigned_by' => $teamLeader->id,
                'assigned_at' => now(),
                'status' => 'to do'
            ]);
            
            Log::info('Tarea asignada por team leader', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $developer->id,
                'developer_name' => $developer->name,
                'team_leader_id' => $teamLeader->id,
                'team_leader_name' => $teamLeader->name
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al asignar tarea por team leader', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'developer_id' => $developer->id,
                'team_leader_id' => $teamLeader->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Auto-asignar tarea por un desarrollador
     */
    public function selfAssignTask(Task $task, User $developer): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que el desarrollador está asignado al proyecto
            if (!$task->project->users()->where('users.id', $developer->id)->exists()) {
                throw new \Exception('No tienes acceso a este proyecto');
            }
            
            // Verificar límite de actividades activas (máximo 3)
            $activeActivities = $this->getActiveActivitiesCount($developer->id);
            if ($activeActivities >= 3) {
                throw new \Exception('Ya tienes el máximo de 3 actividades activas (tareas o bugs). Debes completar o pausar alguna actividad antes de asignarte una nueva.');
            }
            
            // Verificar que la tarea no está ya asignada
            if ($task->user_id) {
                throw new \Exception('La tarea ya está asignada a otro desarrollador');
            }
            
            // Verificar que la tarea está disponible (no asignada y con prioridad alta o sin asignar)
            if ($task->user_id && $task->priority !== 'high') {
                throw new \Exception('Solo puedes auto-asignarte tareas de alta prioridad o tareas no asignadas');
            }
            
            // Auto-asignar la tarea
            $task->update([
                'user_id' => $developer->id,
                'assigned_by' => $developer->id, // Se auto-asigna
                'assigned_at' => now(),
                'status' => 'to do'
            ]);
            
            Log::info('Tarea auto-asignada por desarrollador', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $developer->id,
                'developer_name' => $developer->name
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al auto-asignar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'developer_id' => $developer->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Obtener tareas disponibles para auto-asignación
     */
    public function getAvailableTasksForDeveloper(User $developer): \Illuminate\Database\Eloquent\Collection
    {
        return Task::whereHas('sprint.project.users', function ($query) use ($developer) {
            $query->where('users.id', $developer->id);
        })
        ->where(function ($query) {
            $query->whereNull('user_id')
                  ->orWhere('priority', 'high');
        })
        ->where('status', 'to do')
        ->with(['sprint', 'project'])
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'asc')
        ->get();
    }
    
    /**
     * Obtener tareas asignadas a un desarrollador
     */
    public function getAssignedTasksForDeveloper(User $developer): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('user_id', $developer->id)
            ->with(['sprint', 'project', 'assignedBy'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    /**
     * Verificar si un team leader puede asignar tareas en un proyecto
     */
    private function canTeamLeaderAssignToProject(User $teamLeader, Project $project): bool
    {
        // Verificar que el usuario es team leader
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            return false;
        }
        
        // Verificar que el team leader está asignado al proyecto
        return $project->users()->where('users.id', $teamLeader->id)->exists();
    }
    
    /**
     * Obtener desarrolladores disponibles para un proyecto
     */
    public function getAvailableDevelopersForProject(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return $project->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'developer');
            })
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Obtener team leaders de un proyecto
     */
    public function getTeamLeadersForProject(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return $project->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'team_leader');
            })
            ->orderBy('name')
            ->get();
    }
} 