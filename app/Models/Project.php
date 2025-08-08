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
        'status',
        'created_by',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'status' => 'string',
        'created_by' => 'string',
    ];



    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
