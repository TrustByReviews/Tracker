<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Task Assignment Service Class
 * 
 * This service handles all task assignment operations including team leader assignments,
 * developer self-assignments, and validation of assignment rules and permissions.
 * 
 * Features:
 * - Team leader task assignments to developers
 * - Developer self-assignment capabilities
 * - Activity limit validation (max 3 concurrent activities)
 * - Project permission validation
 * - Assignment history tracking
 * - Available tasks and developers queries
 * 
 * @package App\Services
 * @author System
 * @version 1.0
 */
class TaskAssignmentService
{
    /**
     * Get the count of active activities for a user
     * 
     * Counts both active tasks and bugs assigned to a user.
     * Used to enforce the maximum 3 concurrent activities rule.
     * 
     * @param string $userId The user ID to check
     * @return int Number of active activities (tasks + bugs)
     */
    private function getActiveActivitiesCount(string $userId): int
    {
        // Count active tasks
        $activeTasks = DB::table('tasks')
            ->where('user_id', $userId)
            ->whereIn('status', ['to do', 'in progress'])
            ->count();
            
        // Count active bugs
        $activeBugs = DB::table('bugs')
            ->where('user_id', $userId)
            ->whereIn('status', ['new', 'assigned', 'in progress'])
            ->count();
            
        return $activeTasks + $activeBugs;
    }
    
    /**
     * Assign a task to a developer by a team leader
     * 
     * This method handles task assignments from team leaders to developers.
     * It includes comprehensive validation of permissions, project membership,
     * and activity limits before allowing the assignment.
     * 
     * @param Task $task The task to be assigned
     * @param User $developer The developer to assign the task to
     * @param User $teamLeader The team leader making the assignment
     * @return bool True if assignment was successful
     * @throws \Exception If assignment validation fails
     */
    public function assignTaskByTeamLeader(Task $task, User $developer, User $teamLeader): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify team leader has permissions for the project
            if (!$this->canTeamLeaderAssignToProject($teamLeader, $task->project)) {
                throw new \Exception('Team leader does not have permissions to assign tasks in this project');
            }
            
            // Verify developer is assigned to the project
            if (!$task->project->users()->where('users.id', $developer->id)->exists()) {
                throw new \Exception('The developer is not assigned to this project');
            }
            
            // Verify activity limit (maximum 3 concurrent activities)
            $activeActivities = $this->getActiveActivitiesCount($developer->id);
            if ($activeActivities >= 3) {
                throw new \Exception('The developer already has the maximum of 3 active activities (tasks or bugs). They must complete or pause some activity before assigning a new one.');
            }
            
            // Verify task is not already assigned
            if ($task->user_id) {
                throw new \Exception('The task is already assigned to another developer');
            }
            
            // Assign the task
            $task->update([
                'user_id' => $developer->id,
                'assigned_by' => $teamLeader->id,
                'assigned_at' => now(),
                'status' => 'to do'
            ]);
            
            Log::info('Task assigned by team leader', [
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
            Log::error('Error assigning task by team leader', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'developer_id' => $developer->id,
                'team_leader_id' => $teamLeader->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Self-assign a task by a developer
     * 
     * Allows developers to assign tasks to themselves under specific conditions:
     * - Task must be unassigned or have high priority
     * - Developer must be assigned to the project
     * - Developer must not exceed activity limits
     * 
     * @param Task $task The task to be self-assigned
     * @param User $developer The developer self-assigning the task
     * @return bool True if self-assignment was successful
     * @throws \Exception If self-assignment validation fails
     */
    public function selfAssignTask(Task $task, User $developer): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify developer is assigned to the project
            if (!$task->project->users()->where('users.id', $developer->id)->exists()) {
                throw new \Exception('You are not assigned to this project');
            }
            
            // Verify activity limit (maximum 3 concurrent activities)
            $activeActivities = $this->getActiveActivitiesCount($developer->id);
            if ($activeActivities >= 3) {
                throw new \Exception('You already have the maximum of 3 active activities (tasks or bugs). You must complete or pause some activity before assigning a new one.');
            }
            
            // Verify task is not already assigned
            if ($task->user_id) {
                throw new \Exception('The task is already assigned to another developer');
            }
            
            // Verify task is available for self-assignment (unassigned or high priority)
            if ($task->user_id && $task->priority !== 'high') {
                throw new \Exception('You can only self-assign unassigned tasks or high priority tasks');
            }
            
            // Self-assign the task
            $task->update([
                'user_id' => $developer->id,
                'assigned_by' => $developer->id,
                'assigned_at' => now(),
                'status' => 'to do'
            ]);
            
            Log::info('Task self-assigned by developer', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $developer->id,
                'developer_name' => $developer->name
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error self-assigning task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'developer_id' => $developer->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Get available tasks for a developer to self-assign
     * 
     * Returns tasks that a developer can assign to themselves.
     * Includes unassigned tasks and high priority tasks from projects
     * the developer is assigned to.
     * 
     * @param User $developer The developer to get available tasks for
     * @return \Illuminate\Database\Eloquent\Collection Collection of available tasks
     */
    public function getAvailableTasksForDeveloper(User $developer): \Illuminate\Database\Eloquent\Collection
    {
        return Task::whereHas('project.users', function ($query) use ($developer) {
            $query->where('users.id', $developer->id);
        })
        ->where(function ($query) {
            $query->whereNull('user_id')
                  ->orWhere('priority', 'high');
        })
        ->where('status', 'to do')
        ->with(['project', 'sprint'])
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'asc')
        ->get();
    }
    
    /**
     * Get tasks currently assigned to a developer
     * 
     * Returns all tasks (active and completed) that are assigned to a specific developer.
     * 
     * @param User $developer The developer to get tasks for
     * @return \Illuminate\Database\Eloquent\Collection Collection of assigned tasks
     */
    public function getAssignedTasksForDeveloper(User $developer): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('user_id', $developer->id)
            ->with(['project', 'sprint'])
            ->orderBy('status')
            ->orderBy('priority', 'desc')
            ->get();
    }
    
    /**
     * Check if a team leader can assign tasks to a specific project
     * 
     * Validates that a team leader has the necessary permissions
     * to assign tasks within a project.
     * 
     * @param User $teamLeader The team leader to check permissions for
     * @param Project $project The project to check permissions in
     * @return bool True if team leader can assign tasks in the project
     */
    private function canTeamLeaderAssignToProject(User $teamLeader, Project $project): bool
    {
        return $project->users()
            ->where('users.id', $teamLeader->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'team_leader');
            })
            ->exists();
    }
    
    /**
     * Get available developers for a project
     * 
     * Returns all developers assigned to a project who are available
     * for new task assignments (not at activity limit).
     * 
     * @param Project $project The project to get developers for
     * @return \Illuminate\Database\Eloquent\Collection Collection of available developers
     */
    public function getAvailableDevelopersForProject(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return $project->users()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'qa']);
            })
            ->get()
            ->filter(function ($developer) {
                return $this->getActiveActivitiesCount($developer->id) < 3;
            });
    }
    
    /**
     * Get team leaders for a project
     * 
     * Returns all team leaders assigned to a specific project.
     * 
     * @param Project $project The project to get team leaders for
     * @return \Illuminate\Database\Eloquent\Collection Collection of team leaders
     */
    public function getTeamLeadersForProject(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return $project->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'team_leader');
            })
            ->get();
    }
} 