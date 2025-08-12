<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Sprint extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'goal',
        'start_date',
        'end_date',
        'project_id',
        // Fase 1: Campos esenciales
        'description',
        'sprint_type',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'duration_days',
        'sprint_objective',
        'user_stories_included',
        'assigned_tasks',
        'acceptance_criteria',
        
        // Fase 2: Campos de seguimiento avanzado
        'planned_velocity',
        'actual_velocity',
        'velocity_deviation',
        'progress_percentage',
        'blockers',
        'risks',
        'blocker_resolution_notes',
        'detailed_acceptance_criteria',
        'definition_of_done',
        'quality_gates',
        'bugs_found',
        'bugs_resolved',
        'bug_resolution_rate',
        'code_reviews_completed',
        'code_reviews_pending',
        'daily_scrums_held',
        'daily_scrums_missed',
        'daily_scrum_attendance_rate',
        
        // Fase 3: Retrospectiva y Mejoras
        'achievements',
        'problems',
        'actions_to_take',
        'retrospective_notes',
        'lessons_learned',
        'improvement_areas',
        'team_feedback',
        'stakeholder_feedback',
        'team_satisfaction_score',
        'stakeholder_satisfaction_score',
        'process_improvements',
        'tool_improvements',
        'communication_improvements',
        'technical_debt_added',
        'technical_debt_resolved',
        'technical_debt_ratio',
        'knowledge_shared',
        'skills_developed',
        'mentoring_sessions',
        'team_velocity_trend',
        'sprint_efficiency_score',
        'sprint_goals_achieved',
        'sprint_goals_partially_achieved',
        'sprint_goals_not_achieved',
        'goal_achievement_rate',
        'next_sprint_recommendations',
        'sprint_ceremony_effectiveness',
        'overall_sprint_rating'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'duration_days' => 'integer',
        'planned_velocity' => 'integer',
        'actual_velocity' => 'integer',
        'velocity_deviation' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'blockers' => 'array',
        'risks' => 'array',
        'detailed_acceptance_criteria' => 'array',
        'definition_of_done' => 'array',
        'quality_gates' => 'array',
        'user_stories_included' => 'array',
        'assigned_tasks' => 'array',
        'bugs_found' => 'integer',
        'bugs_resolved' => 'integer',
        'bug_resolution_rate' => 'decimal:2',
        'code_reviews_completed' => 'integer',
        'code_reviews_pending' => 'integer',
        'daily_scrums_held' => 'integer',
        'daily_scrums_missed' => 'integer',
        'daily_scrum_attendance_rate' => 'decimal:2',
        
        // Fase 3: Retrospectiva y Mejoras
        'achievements' => 'array',
        'problems' => 'array',
        'actions_to_take' => 'array',
        'lessons_learned' => 'array',
        'improvement_areas' => 'array',
        'team_feedback' => 'array',
        'stakeholder_feedback' => 'array',
        'team_satisfaction_score' => 'decimal:1',
        'stakeholder_satisfaction_score' => 'decimal:1',
        'process_improvements' => 'array',
        'tool_improvements' => 'array',
        'communication_improvements' => 'array',
        'technical_debt_added' => 'array',
        'technical_debt_resolved' => 'array',
        'technical_debt_ratio' => 'decimal:2',
        'knowledge_shared' => 'array',
        'skills_developed' => 'array',
        'mentoring_sessions' => 'array',
        'team_velocity_trend' => 'integer',
        'sprint_efficiency_score' => 'decimal:2',
        'sprint_goals_achieved' => 'array',
        'sprint_goals_partially_achieved' => 'array',
        'sprint_goals_not_achieved' => 'array',
        'goal_achievement_rate' => 'decimal:2',
        'sprint_ceremony_effectiveness' => 'array',
        'overall_sprint_rating' => 'decimal:1'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-calculate metrics when sprint is updated
        static::updating(function ($sprint) {
            // Calculate velocity deviation
            if (isset($sprint->planned_velocity) && isset($sprint->actual_velocity) && $sprint->planned_velocity > 0) {
                $sprint->velocity_deviation = round((($sprint->actual_velocity - $sprint->planned_velocity) / $sprint->planned_velocity) * 100, 2);
            }

            // Calculate bug resolution rate
            if (isset($sprint->bugs_found) && isset($sprint->bugs_resolved) && $sprint->bugs_found > 0) {
                $sprint->bug_resolution_rate = round(($sprint->bugs_resolved / $sprint->bugs_found) * 100, 2);
            }

            // Calculate daily scrum attendance rate
            if (isset($sprint->daily_scrums_held) && isset($sprint->daily_scrums_missed)) {
                $totalScrums = $sprint->daily_scrums_held + $sprint->daily_scrums_missed;
                if ($totalScrums > 0) {
                    $sprint->daily_scrum_attendance_rate = round(($sprint->daily_scrums_held / $totalScrums) * 100, 2);
                }
            }
        });
    }

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

    // Métodos originales
    public function getTotalProgress(): int
    {
        if (!$this->tasks || $this->tasks->isEmpty()) {
            return 0;
        }

        $completedTasks = $this->tasks->where('status', 'done')->count();
        return (int) round(($completedTasks / $this->tasks->count()) * 100);
    }

    public function getSprintStats(): array
    {
        $tasks = $this->tasks ?? collect();
        $bugs = $this->bugs ?? collect();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $inProgressTasks = $tasks->where('status', 'in progress')->count();

        $totalBugs = $bugs->count();
        $completedBugs = $bugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();
        $inProgressBugs = $bugs->whereIn('status', ['assigned', 'in progress'])->count();

        $totalItems = $totalTasks + $totalBugs;
        $completedItems = $completedTasks + $completedBugs;
        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'total_bugs' => $totalBugs,
            'completed_bugs' => $completedBugs,
            'in_progress_bugs' => $inProgressBugs,
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'progress_percentage' => $progressPercentage
        ];
    }

    public function getDaysRemaining(): int
    {
        $endDate = $this->end_date;
        $today = now();
        
        if ($endDate->isPast()) {
            return 0;
        }
        
        return $endDate->diffInDays($today);
    }

    public function isOverdue(): bool
    {
        return $this->end_date->isPast() && $this->getTotalProgress() < 100;
    }

    public function getPriorityScore(): int
    {
        $daysRemaining = $this->getDaysRemaining();
        $progress = $this->getTotalProgress();
        
        if ($daysRemaining === 0 && $progress < 100) {
            return 100; // Overdue
        }
        
        if ($daysRemaining <= 3 && $progress < 50) {
            return 90; // High priority
        }
        
        if ($daysRemaining <= 7 && $progress < 70) {
            return 70; // Medium priority
        }
        
        return 50; // Normal priority
    }

    // Métodos de Fase 2
    public function calculateVelocityDeviation(): float
    {
        if (!$this->planned_velocity || $this->planned_velocity == 0) {
            return 0.0;
        }
        
        return round((($this->actual_velocity - $this->planned_velocity) / $this->planned_velocity) * 100, 2);
    }

    public function calculateBugResolutionRate(): float
    {
        if (!$this->bugs_found || $this->bugs_found == 0) {
            return 0.0;
        }
        
        return round(($this->bugs_resolved / $this->bugs_found) * 100, 2);
    }

    public function calculateDailyScrumAttendanceRate(): float
    {
        $totalScrums = $this->daily_scrums_held + $this->daily_scrums_missed;
        
        if (!$totalScrums || $totalScrums == 0) {
            return 0.0;
        }
        
        return round(($this->daily_scrums_held / $totalScrums) * 100, 2);
    }

    public function getAdvancedMetrics(): array
    {
        return [
            'velocity_deviation' => $this->calculateVelocityDeviation(),
            'bug_resolution_rate' => $this->calculateBugResolutionRate(),
            'daily_scrum_attendance_rate' => $this->calculateDailyScrumAttendanceRate(),
            'code_review_completion_rate' => $this->calculateCodeReviewCompletionRate(),
            'total_blockers' => count($this->blockers ?? []),
            'total_risks' => count($this->risks ?? []),
            'quality_score' => $this->calculateQualityScore()
        ];
    }

    public function calculateQualityScore(): float
    {
        $scores = [];
        
        // Bug resolution rate (30% weight)
        $scores[] = $this->calculateBugResolutionRate() * 0.3;
        
        // Code review completion rate (25% weight)
        $scores[] = $this->calculateCodeReviewCompletionRate() * 0.25;
        
        // Daily scrum attendance rate (20% weight)
        $scores[] = $this->calculateDailyScrumAttendanceRate() * 0.2;
        
        // Velocity deviation (15% weight) - penalize high deviations
        $velocityDeviation = abs($this->calculateVelocityDeviation());
        $velocityScore = max(0, 100 - $velocityDeviation);
        $scores[] = $velocityScore * 0.15;
        
        // Progress percentage (10% weight)
        $scores[] = ($this->progress_percentage ?? 0) * 0.1;
        
        return round(array_sum($scores), 2);
    }

    public function isAtRisk(): bool
    {
        $velocityDeviation = abs($this->calculateVelocityDeviation());
        $bugResolutionRate = $this->calculateBugResolutionRate();
        $attendanceRate = $this->calculateDailyScrumAttendanceRate();
        
        return $velocityDeviation > 20 || $bugResolutionRate < 70 || $attendanceRate < 80;
    }

    public function getRecommendations(): array
    {
        $recommendations = [];
        
        $velocityDeviation = $this->calculateVelocityDeviation();
        if (abs($velocityDeviation) > 15) {
            $recommendations[] = $velocityDeviation > 0 
                ? 'Consider reducing sprint scope to improve velocity accuracy'
                : 'Consider increasing sprint scope to better utilize team capacity';
        }
        
        $bugResolutionRate = $this->calculateBugResolutionRate();
        if ($bugResolutionRate < 80) {
            $recommendations[] = 'Focus on bug resolution to improve quality metrics';
        }
        
        $attendanceRate = $this->calculateDailyScrumAttendanceRate();
        if ($attendanceRate < 85) {
            $recommendations[] = 'Improve daily scrum attendance to enhance team communication';
        }
        
        if (count($this->blockers ?? []) > 3) {
            $recommendations[] = 'Address blockers promptly to maintain sprint momentum';
        }
        
        return $recommendations;
    }

    private function calculateCodeReviewCompletionRate(): float
    {
        $totalReviews = $this->code_reviews_completed + $this->code_reviews_pending;
        
        if (!$totalReviews || $totalReviews == 0) {
            return 0.0;
        }
        
        return round(($this->code_reviews_completed / $totalReviews) * 100, 2);
    }

    // Métodos de Fase 3: Retrospectiva y Mejoras
    public function finishSprint(array $retrospectiveData = []): bool
    {
        $this->actual_end_date = now();
        $this->progress_percentage = 100.0;
        
        // Calcular métricas finales
        $this->calculateFinalMetrics();
        
        // Guardar datos de retrospectiva si se proporcionan
        if (!empty($retrospectiveData)) {
            $this->fill($retrospectiveData);
        }
        
        return $this->save();
    }

    public function addRetrospective(array $retrospectiveData): bool
    {
        $this->fill($retrospectiveData);
        $this->calculateRetrospectiveMetrics();
        
        return $this->save();
    }

    public function calculateFinalMetrics(): void
    {
        // Calcular tasa de logro de objetivos
        $this->calculateGoalAchievementRate();
        
        // Calcular eficiencia del sprint
        $this->calculateSprintEfficiencyScore();
        
        // Calcular ratio de deuda técnica
        $this->calculateTechnicalDebtRatio();
        
        // Calcular tendencia de velocidad del equipo
        $this->calculateTeamVelocityTrend();
    }

    public function calculateGoalAchievementRate(): float
    {
        $achieved = count($this->sprint_goals_achieved ?? []);
        $partiallyAchieved = count($this->sprint_goals_partially_achieved ?? []);
        $notAchieved = count($this->sprint_goals_not_achieved ?? []);
        
        $total = $achieved + $partiallyAchieved + $notAchieved;
        
        if ($total == 0) {
            return 0.0;
        }
        
        // Objetivos logrados cuentan 100%, parcialmente logrados 50%
        $rate = (($achieved * 100) + ($partiallyAchieved * 50)) / $total;
        
        $this->goal_achievement_rate = round($rate, 2);
        return $this->goal_achievement_rate;
    }

    public function calculateSprintEfficiencyScore(): float
    {
        $scores = [];
        
        // Goal achievement rate (40% weight)
        $scores[] = ($this->goal_achievement_rate ?? 0) * 0.4;
        
        // Velocity accuracy (25% weight)
        $velocityAccuracy = max(0, 100 - abs($this->calculateVelocityDeviation()));
        $scores[] = $velocityAccuracy * 0.25;
        
        // Quality score (20% weight)
        $scores[] = $this->calculateQualityScore() * 0.2;
        
        // Team satisfaction (15% weight)
        $teamSatisfaction = ($this->team_satisfaction_score ?? 5) * 10; // Convert 1-10 to 0-100
        $scores[] = $teamSatisfaction * 0.15;
        
        $this->sprint_efficiency_score = round(array_sum($scores), 2);
        return $this->sprint_efficiency_score;
    }

    public function calculateTechnicalDebtRatio(): float
    {
        $debtAdded = count($this->technical_debt_added ?? []);
        $debtResolved = count($this->technical_debt_resolved ?? []);
        
        if ($debtAdded == 0 && $debtResolved == 0) {
            $this->technical_debt_ratio = 0.0;
            return 0.0;
        }
        
        $ratio = $debtAdded > 0 ? ($debtResolved / $debtAdded) * 100 : 100;
        $this->technical_debt_ratio = round($ratio, 2);
        
        return $this->technical_debt_ratio;
    }

    public function calculateTeamVelocityTrend(): int
    {
        // Comparar con sprints anteriores del mismo proyecto
        $previousSprints = $this->project->sprints()
            ->where('id', '!=', $this->id)
            ->where('actual_velocity', '!=', null)
            ->orderBy('end_date', 'desc')
            ->limit(3)
            ->get();
        
        if ($previousSprints->isEmpty()) {
            $this->team_velocity_trend = 0;
            return 0;
        }
        
        $avgPreviousVelocity = $previousSprints->avg('actual_velocity');
        $currentVelocity = $this->actual_velocity ?? 0;
        
        if ($avgPreviousVelocity == 0) {
            $this->team_velocity_trend = 0;
            return 0;
        }
        
        $trend = (($currentVelocity - $avgPreviousVelocity) / $avgPreviousVelocity) * 100;
        $this->team_velocity_trend = (int) round($trend);
        
        return $this->team_velocity_trend;
    }

    public function calculateRetrospectiveMetrics(): void
    {
        // Calcular calificación general del sprint
        $this->calculateOverallSprintRating();
        
        // Generar recomendaciones para el próximo sprint
        $this->generateNextSprintRecommendations();
    }

    public function calculateOverallSprintRating(): float
    {
        $scores = [];
        
        // Goal achievement (30% weight)
        $scores[] = ($this->goal_achievement_rate ?? 0) * 0.3;
        
        // Team satisfaction (25% weight)
        $teamSatisfaction = ($this->team_satisfaction_score ?? 5) * 10;
        $scores[] = $teamSatisfaction * 0.25;
        
        // Stakeholder satisfaction (20% weight)
        $stakeholderSatisfaction = ($this->stakeholder_satisfaction_score ?? 5) * 10;
        $scores[] = $stakeholderSatisfaction * 0.2;
        
        // Sprint efficiency (15% weight)
        $scores[] = ($this->sprint_efficiency_score ?? 0) * 0.15;
        
        // Quality score (10% weight)
        $scores[] = $this->calculateQualityScore() * 0.1;
        
        $overallRating = array_sum($scores) / 10; // Convert to 1-10 scale
        $this->overall_sprint_rating = round($overallRating, 1);
        
        return $this->overall_sprint_rating;
    }

    public function generateNextSprintRecommendations(): string
    {
        $recommendations = [];
        
        // Basado en problemas identificados
        if (!empty($this->problems)) {
            $recommendations[] = "Address identified problems: " . implode(', ', array_slice($this->problems, 0, 3));
        }
        
        // Basado en áreas de mejora
        if (!empty($this->improvement_areas)) {
            $recommendations[] = "Focus on improvements: " . implode(', ', array_slice($this->improvement_areas, 0, 3));
        }
        
        // Basado en lecciones aprendidas
        if (!empty($this->lessons_learned)) {
            $recommendations[] = "Apply lessons learned: " . implode(', ', array_slice($this->lessons_learned, 0, 2));
        }
        
        // Basado en métricas
        if (($this->goal_achievement_rate ?? 0) < 80) {
            $recommendations[] = "Improve goal setting and estimation accuracy";
        }
        
        if (($this->team_satisfaction_score ?? 5) < 7) {
            $recommendations[] = "Address team satisfaction concerns";
        }
        
        if (abs($this->calculateVelocityDeviation()) > 15) {
            $recommendations[] = "Improve velocity estimation and planning";
        }
        
        $this->next_sprint_recommendations = implode('. ', $recommendations);
        return $this->next_sprint_recommendations;
    }

    public function isCompleted(): bool
    {
        return $this->actual_end_date !== null;
    }

    public function hasRetrospective(): bool
    {
        return !empty($this->retrospective_notes) || 
               !empty($this->achievements) || 
               !empty($this->problems) || 
               !empty($this->actions_to_take);
    }

    public function getRetrospectiveSummary(): array
    {
        return [
            'achievements_count' => count($this->achievements ?? []),
            'problems_count' => count($this->problems ?? []),
            'actions_count' => count($this->actions_to_take ?? []),
            'lessons_learned_count' => count($this->lessons_learned ?? []),
            'team_satisfaction' => $this->team_satisfaction_score ?? 0,
            'stakeholder_satisfaction' => $this->stakeholder_satisfaction_score ?? 0,
            'overall_rating' => $this->overall_sprint_rating ?? 0,
            'goal_achievement_rate' => $this->goal_achievement_rate ?? 0,
            'sprint_efficiency' => $this->sprint_efficiency_score ?? 0
        ];
    }

    /**
     * Verifica si el sprint puede ser finalizado
     * Un sprint solo puede finalizarse si todas las tareas están completadas
     */
    public function canBeFinished(): bool
    {
        // Si ya está completado, no puede finalizarse nuevamente
        if ($this->isCompleted()) {
            return false;
        }

        // Obtener todas las tareas del sprint
        $tasks = $this->tasks()->get();
        
        // Si no hay tareas, se puede finalizar
        if ($tasks->isEmpty()) {
            return true;
        }

        // Verificar que todas las tareas estén completadas
        $pendingTasks = $tasks->filter(function ($task) {
            return !in_array($task->status, ['completed', 'done', 'verified']);
        });

        return $pendingTasks->isEmpty();
    }

    /**
     * Obtiene las tareas pendientes que impiden finalizar el sprint
     */
    public function getPendingTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tasks()->whereNotIn('status', ['completed', 'done', 'verified'])->get();
    }

    /**
     * Obtiene información detallada sobre el estado de finalización
     */
    public function getFinishStatus(): array
    {
        $tasks = $this->tasks()->get();
        $pendingTasks = $this->getPendingTasks();
        $completedTasks = $tasks->filter(function ($task) {
            return in_array($task->status, ['completed', 'done', 'verified']);
        });

        return [
            'can_be_finished' => $this->canBeFinished(),
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $completedTasks->count(),
            'pending_tasks' => $pendingTasks->count(),
            'completion_percentage' => $tasks->count() > 0 ? round(($completedTasks->count() / $tasks->count()) * 100, 1) : 100,
            'pending_tasks_list' => $pendingTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'status' => $task->status,
                    'assigned_to' => $task->user ? $task->user->name : 'Unassigned'
                ];
            })
        ];
    }
}
