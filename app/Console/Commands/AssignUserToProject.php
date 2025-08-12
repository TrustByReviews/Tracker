<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class AssignUserToProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage examples:
     *  php artisan user:assign-to-project --email=dev@example.com --project="Mobile Banking App v2" --tasks=3 --only-unassigned=1
     *  php artisan user:assign-to-project --email=dev@example.com --project-id=... --tasks=0
     */
    protected $signature = 'user:assign-to-project
        {--email= : Email del usuario a asignar}
        {--project= : Nombre del proyecto}
        {--project-id= : ID (UUID) del proyecto}
        {--tasks=0 : Cantidad de tareas a asignar (0 = no asignar tareas)}
        {--only-unassigned=1 : 1 para asignar solo tareas sin usuario}' ;

    /**
     * The console command description.
     */
    protected $description = 'Asigna un usuario a un proyecto y opcionalmente le asigna N tareas de ese proyecto';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $projectName = $this->option('project');
        $projectId = $this->option('project-id');
        $numTasks = (int) $this->option('tasks');
        $onlyUnassigned = (string) $this->option('only-unassigned') === '1';

        if (!$email || (!$projectName && !$projectId)) {
            $this->error('Parámetros inválidos. Requiere --email y (--project o --project-id).');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("Usuario no encontrado por email: {$email}");
            return self::FAILURE;
        }

        $projectQuery = Project::query();
        if ($projectId) {
            $projectQuery->where('id', $projectId);
        } else {
            $projectQuery->where('name', $projectName);
        }
        $project = $projectQuery->first();
        if (!$project) {
            $this->error($projectId
                ? "Proyecto no encontrado por ID: {$projectId}"
                : "Proyecto no encontrado por nombre: {$projectName}");
            return self::FAILURE;
        }

        // Asignar usuario al proyecto (pivot project_user)
        $project->users()->syncWithoutDetaching([$user->id]);
        $this->info("✅ Usuario '{$user->name}' asignado al proyecto '{$project->name}'");

        if ($numTasks > 0) {
            // Obtener tareas del proyecto a través de sus sprints
            $project->load(['sprints.tasks']);
            $tasks = $project->sprints->flatMap(fn ($s) => $s->tasks);

            if ($onlyUnassigned) {
                $tasks = $tasks->whereNull('user_id');
            }

            $tasksToAssign = $tasks->take($numTasks);

            if ($tasksToAssign->isEmpty()) {
                $this->warn('⚠️ No hay tareas disponibles para asignar con los criterios dados.');
            } else {
                foreach ($tasksToAssign as $task) {
                    /** @var Task $task */
                    $task->user_id = $user->id;
                    if (empty($task->status)) {
                        $task->status = 'to do';
                    }
                    $task->save();
                    $this->line("✓ Tarea asignada: {$task->name} ({$task->id})");
                }
                $this->info("✅ Total de tareas asignadas: " . $tasksToAssign->count());
            }
        }

        return self::SUCCESS;
    }
}
