<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sprint extends Model
{
    protected $table = 'sprints';

    use softDeletes, HasUuid;
    protected $fillable = [
      'name',
      'goal',
      'start_date',
      'end_date',
      'project_id',
    ];

    protected $casts = [
          'name' => 'string',
          'goal' => 'string',
          'start_date' => 'date',
          'end_date' => 'date',
          'project_id' => 'string',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }

    /**
     * Calcular el progreso total del sprint incluyendo tareas y bugs
     */
    public function getTotalProgress(): float
    {
        $totalTasks = $this->tasks->count();
        $completedTasks = $this->tasks->where('status', 'done')->count();
        
        $totalBugs = $this->bugs->count();
        $completedBugs = $this->bugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();

        $totalItems = $totalTasks + $totalBugs;
        if ($totalItems === 0) {
            return 0;
        }

        $completedItems = $completedTasks + $completedBugs;
        return round(($completedItems / $totalItems) * 100, 2);
    }

    /**
     * Obtener estadísticas del sprint
     */
    public function getSprintStats(): array
    {
        $totalTasks = $this->tasks->count();
        $completedTasks = $this->tasks->where('status', 'done')->count();
        $inProgressTasks = $this->tasks->where('status', 'in progress')->count();
        
        $totalBugs = $this->bugs->count();
        $completedBugs = $this->bugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();
        $inProgressBugs = $this->bugs->whereIn('status', ['assigned', 'in progress'])->count();

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

    /**
     * Calcular días restantes hasta el final del sprint
     */
    public function getDaysRemaining(): int
    {
        $endDate = \Carbon\Carbon::parse($this->end_date);
        $today = \Carbon\Carbon::now();
        
        return max(0, $today->diffInDays($endDate, false));
    }

    /**
     * Verificar si el sprint está atrasado
     */
    public function isOverdue(): bool
    {
        $endDate = \Carbon\Carbon::parse($this->end_date);
        $today = \Carbon\Carbon::now();
        
        return $today->isAfter($endDate) && $this->getTotalProgress() < 100;
    }

    /**
     * Calcular prioridad del sprint basada en progreso y tiempo restante
     */
    public function getPriorityScore(): int
    {
        $progress = $this->getTotalProgress();
        $daysRemaining = $this->getDaysRemaining();
        
        // Si está atrasado, alta prioridad
        if ($this->isOverdue()) {
            return 100;
        }
        
        // Si falta poco tiempo y poco progreso, alta prioridad
        if ($daysRemaining <= 3 && $progress < 50) {
            return 90;
        }
        
        // Si falta tiempo medio y progreso bajo, prioridad media
        if ($daysRemaining <= 7 && $progress < 70) {
            return 70;
        }
        
        // Prioridad normal
        return 50;
    }
}
