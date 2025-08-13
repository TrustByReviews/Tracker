<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    protected $table = 'projects';
    use SoftDeletes, HasUuid;


    protected $fillable = [
        'name',
        'description',
        'objectives',
        'priority',
        'category',
        'development_type',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'methodology',
        'technologies',
        'programming_languages',
        'frameworks',
        'database_type',
        'architecture',
        'external_integrations',
        'project_owner',
        'product_owner',
        'stakeholders',
        'milestones',
        'estimated_velocity',
        'current_sprint',
        'estimated_budget',
        'used_budget',
        'assigned_resources',
        'progress_percentage',
        'identified_risks',
        'open_issues',
        'documentation_url',
        'repository_url',
        'task_board_url',
        'status',
        'created_by',
        // Campos de finalización
        'achievements',
        'difficulties',
        'lessons_learned',
        'final_documentation',
        'termination_reason',
        'custom_reason',
        'final_attachments',
        'is_finished',
        'finished_at',
        'finished_by',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'objectives' => 'string',
        'priority' => 'string',
        'category' => 'string',
        'development_type' => 'string',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'methodology' => 'string',
        'technologies' => 'array',
        'programming_languages' => 'array',
        'frameworks' => 'array',
        'database_type' => 'string',
        'architecture' => 'string',
        'external_integrations' => 'array',
        'project_owner' => 'string',
        'product_owner' => 'string',
        'stakeholders' => 'array',
        'milestones' => 'array',
        'estimated_velocity' => 'integer',
        'current_sprint' => 'string',
        'estimated_budget' => 'decimal:2',
        'used_budget' => 'decimal:2',
        'assigned_resources' => 'array',
        'progress_percentage' => 'decimal:2',
        'identified_risks' => 'array',
        'open_issues' => 'integer',
        'documentation_url' => 'string',
        'repository_url' => 'string',
        'task_board_url' => 'string',
        'status' => 'string',
        'created_by' => 'string',
        // Campos de finalización
        'achievements' => 'string',
        'difficulties' => 'string',
        'lessons_learned' => 'string',
        'final_documentation' => 'string',
        'termination_reason' => 'string',
        'custom_reason' => 'string',
        'final_attachments' => 'array',
        'is_finished' => 'boolean',
        'finished_at' => 'datetime',
        'finished_by' => 'string',
    ];



    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finished_by');
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class, 'project_id');
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class, 'project_id');
    }

    /**
     * Calcular el progreso total del proyecto incluyendo tareas y bugs
     */
    public function getTotalProgress(): float
    {
        $totalTasks = 0;
        $completedTasks = 0;
        $totalBugs = 0;
        $completedBugs = 0;

        // Calcular progreso de tareas
        foreach ($this->sprints as $sprint) {
            $sprintTasks = $sprint->tasks;
            $totalTasks += $sprintTasks->count();
            $completedTasks += $sprintTasks->where('status', 'done')->count();
        }

        // Calcular progreso de bugs
        $projectBugs = $this->bugs;
        $totalBugs = $projectBugs->count();
        $completedBugs = $projectBugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();

        $totalItems = $totalTasks + $totalBugs;
        if ($totalItems === 0) {
            return 0;
        }

        $completedItems = $completedTasks + $completedBugs;
        return round(($completedItems / $totalItems) * 100, 2);
    }

    /**
     * Obtener estadísticas del proyecto
     */
    public function getProjectStats(): array
    {
        $totalTasks = 0;
        $completedTasks = 0;
        $inProgressTasks = 0;
        $totalBugs = 0;
        $completedBugs = 0;
        $inProgressBugs = 0;

        // Estadísticas de tareas
        foreach ($this->sprints as $sprint) {
            $sprintTasks = $sprint->tasks;
            $totalTasks += $sprintTasks->count();
            $completedTasks += $sprintTasks->where('status', 'done')->count();
            $inProgressTasks += $sprintTasks->where('status', 'in progress')->count();
        }

        // Estadísticas de bugs
        $projectBugs = $this->bugs;
        $totalBugs = $projectBugs->count();
        $completedBugs = $projectBugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();
        $inProgressBugs = $projectBugs->whereIn('status', ['assigned', 'in progress'])->count();

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'total_bugs' => $totalBugs,
            'completed_bugs' => $completedBugs,
            'in_progress_bugs' => $inProgressBugs,
            'total_items' => $totalTasks + $totalBugs,
            'completed_items' => $completedTasks + $completedBugs,
            'progress_percentage' => $this->getTotalProgress(),
        ];
    }
}
