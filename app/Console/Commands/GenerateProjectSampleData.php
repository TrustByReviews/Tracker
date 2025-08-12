<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class GenerateProjectSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Examples:
     *  php artisan project:seed --project="Mobile Banking App v2" --sprint="Sprint Demo" --tasks=8 --assign=developer4@example.com
     *  php artisan project:seed --project-id=UUID --tasks=5
     */
    protected $signature = 'project:seed
        {--project= : Nombre del proyecto}
        {--project-id= : ID (UUID) del proyecto}
        {--sprint= : Nombre del sprint a crear/usar}
        {--tasks=5 : Número de tareas a crear}
        {--assign= : Email del desarrollador al que asignar las tareas (opcional)}';

    /**
     * The console command description.
     */
    protected $description = 'Genera datos de ejemplo para un proyecto: crea un sprint y N tareas, opcionalmente asignadas a un desarrollador';

    public function handle(): int
    {
        $projectName = $this->option('project');
        $projectId = $this->option('project-id');
        $sprintName = $this->option('sprint') ?: 'Sprint Demo';
        $numTasks = (int) $this->option('tasks');
        $assignEmail = $this->option('assign');

        if (!$projectName && !$projectId) {
            $this->error('Debes indicar --project o --project-id');
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

        $this->info("Proyecto: {$project->name} ({$project->id})");

        // Crear/obtener sprint
        $sprint = Sprint::firstOrCreate(
            [
                'name' => $sprintName,
                'project_id' => $project->id,
            ],
            [
                'goal' => 'Datos de ejemplo para demostración',
                'start_date' => Carbon::now()->subDays(3),
                'end_date' => Carbon::now()->addDays(10),
                'status' => 'active',
            ]
        );
        $this->info("Sprint: {$sprint->name} ({$sprint->id})");

        // Resolver desarrollador (si se solicitó asignación)
        $assignee = null;
        if ($assignEmail) {
            $assignee = User::where('email', $assignEmail)->first();
            if (!$assignee) {
                $this->warn("⚠️ Usuario no encontrado por email: {$assignEmail}. Las tareas se crearán sin asignar.");
            } else {
                // Asegurar que esté asignado al proyecto
                $project->users()->syncWithoutDetaching([$assignee->id]);
                $this->line("✓ Desarrollador vinculado al proyecto: {$assignee->name}");
            }
        }

        // Prepara pools de datos
        $titles = [
            'Implementar autenticación',
            'Diseñar dashboard',
            'Configurar base de datos',
            'Crear API de proyectos',
            'Optimizar rendimiento de tareas',
            'Agregar notificaciones',
            'Refactorizar componentes UI',
            'Implementar testing básico',
            'Mejorar logging',
            'Ajustar permisos y roles',
        ];
        $categories = ['frontend', 'backend', 'full stack', 'design', 'deployment', 'fixes'];
        $priorities = ['low', 'medium', 'high'];

        $created = 0;
        for ($i = 1; $i <= max(1, $numTasks); $i++) {
            $name = $titles[($i - 1) % count($titles)] . " #{$i}";

            $payload = [
                'name' => $name,
                'description' => 'Tarea de ejemplo generada automáticamente',
                'priority' => $priorities[array_rand($priorities)],
                'category' => $categories[array_rand($categories)],
                'story_points' => rand(1, 8),
                'estimated_hours' => rand(2, 12),
                'status' => 'to do',
                'sprint_id' => $sprint->id,
            ];

            // Añadir project_id si existe la columna en la BD
            if (Schema::hasColumn('tasks', 'project_id')) {
                $payload['project_id'] = $project->id;
            }

            // Asignación (opcional)
            if ($assignee) {
                $payload['user_id'] = $assignee->id;
            }

            $task = Task::create($payload);
            $created++;
            $this->line("✓ Tarea creada: {$task->name} (status: {$task->status}, prioridad: {$task->priority})");
        }

        $this->info("✅ Total de tareas creadas: {$created}");
        $this->info('Listo. Puedes verlas en /sprints o /tasks');

        return self::SUCCESS;
    }
}
