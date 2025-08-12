<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SprintController extends Controller
{
    public function index(Request $request)
    {
        $query = Sprint::with(['project', 'tasks', 'bugs']);

        // Aplicar filtros
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $query->where(function ($q) use ($status) {
                $today = now();
                switch ($status) {
                    case 'active':
                        $q->where('start_date', '<=', $today)
                          ->where('end_date', '>=', $today);
                        break;
                    case 'upcoming':
                        $q->where('start_date', '>', $today);
                        break;
                    case 'completed':
                        $q->where('end_date', '<', $today);
                        break;
                }
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'recent');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'task_count':
                $query->withCount('tasks')->orderBy('tasks_count', $sortOrder);
                break;
            case 'completed_tasks':
                $query->withCount(['tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', 'done');
                }])->orderBy('completed_tasks_count', $sortOrder);
                break;
            case 'pending_tasks':
                $query->withCount(['tasks as pending_tasks_count' => function ($q) {
                    $q->whereNotIn('status', ['done']);
                }])->orderBy('pending_tasks_count', $sortOrder);
                break;
            case 'completion_rate':
                $query->orderBy('progress_percentage', $sortOrder);
                break;
            case 'days_to_end':
                $query->orderBy('end_date', $sortOrder);
                break;
            case 'priority_score':
                // Ordenar por fecha de fin (más cercana = mayor prioridad)
                $query->orderBy('end_date', 'asc');
                break;
            case 'velocity_deviation':
                $query->orderBy('velocity_deviation', $sortOrder);
                break;
            case 'bug_resolution_rate':
                $query->orderBy('bug_resolution_rate', $sortOrder);
                break;
            case 'progress_percentage':
                $query->orderBy('progress_percentage', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $sprints = $query->get();
        $projects = Project::all();

        return Inertia::render('Sprint/Index', [
            'sprints' => $sprints,
            'projects' => $projects,
            'permissions' => auth()->user()->role,
            'filters' => $request->only(['project_id', 'sort_by', 'sort_order', 'status', 'item_type'])
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'goal' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'project_id' => 'required|exists:projects,id',
            
            // Fase 1: Campos esenciales
            'description' => 'nullable|string|max:1000',
            'sprint_type' => 'nullable|in:regular,release,hotfix',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'duration_days' => 'nullable|integer|min:1',
            'sprint_objective' => 'nullable|string|max:1000',
            'user_stories_included' => 'nullable|array',
            'user_stories_included.*' => 'string|max:100',
            'assigned_tasks' => 'nullable|array',
            'assigned_tasks.*' => 'string|max:100',
            'acceptance_criteria' => 'nullable|string|max:1000',
            
            // Fase 2: Campos de seguimiento avanzado
            'planned_velocity' => 'nullable|integer|min:0',
            'actual_velocity' => 'nullable|integer|min:0',
            'velocity_deviation' => 'nullable|numeric',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'blockers' => 'nullable|array',
            'blockers.*' => 'string|max:200',
            'risks' => 'nullable|array',
            'risks.*' => 'string|max:200',
            'blocker_resolution_notes' => 'nullable|string|max:1000',
            'detailed_acceptance_criteria' => 'nullable|array',
            'detailed_acceptance_criteria.*' => 'string|max:200',
            'definition_of_done' => 'nullable|array',
            'definition_of_done.*' => 'string|max:200',
            'quality_gates' => 'nullable|array',
            'quality_gates.*' => 'string|max:200',
            'bugs_found' => 'nullable|integer|min:0',
            'bugs_resolved' => 'nullable|integer|min:0',
            'bug_resolution_rate' => 'nullable|numeric|min:0|max:100',
            'code_reviews_completed' => 'nullable|integer|min:0',
            'code_reviews_pending' => 'nullable|integer|min:0',
            'daily_scrums_held' => 'nullable|integer|min:0',
            'daily_scrums_missed' => 'nullable|integer|min:0',
            'daily_scrum_attendance_rate' => 'nullable|numeric|min:0|max:100',
            
            // Fase 3: Retrospectiva y Mejoras
            'achievements' => 'nullable|array',
            'achievements.*' => 'string|max:200',
            'problems' => 'nullable|array',
            'problems.*' => 'string|max:200',
            'actions_to_take' => 'nullable|array',
            'actions_to_take.*' => 'string|max:200',
            'retrospective_notes' => 'nullable|string|max:2000',
            'lessons_learned' => 'nullable|array',
            'lessons_learned.*' => 'string|max:200',
            'improvement_areas' => 'nullable|array',
            'improvement_areas.*' => 'string|max:200',
            'team_feedback' => 'nullable|array',
            'team_feedback.*' => 'string|max:200',
            'stakeholder_feedback' => 'nullable|array',
            'stakeholder_feedback.*' => 'string|max:200',
            'team_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'stakeholder_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'process_improvements' => 'nullable|array',
            'process_improvements.*' => 'string|max:200',
            'tool_improvements' => 'nullable|array',
            'tool_improvements.*' => 'string|max:200',
            'communication_improvements' => 'nullable|array',
            'communication_improvements.*' => 'string|max:200',
            'technical_debt_added' => 'nullable|array',
            'technical_debt_added.*' => 'string|max:200',
            'technical_debt_resolved' => 'nullable|array',
            'technical_debt_resolved.*' => 'string|max:200',
            'knowledge_shared' => 'nullable|array',
            'knowledge_shared.*' => 'string|max:200',
            'skills_developed' => 'nullable|array',
            'skills_developed.*' => 'string|max:200',
            'mentoring_sessions' => 'nullable|array',
            'mentoring_sessions.*' => 'string|max:200',
            'team_velocity_trend' => 'nullable|integer',
            'sprint_efficiency_score' => 'nullable|numeric|min:0|max:100',
            'sprint_goals_achieved' => 'nullable|array',
            'sprint_goals_achieved.*' => 'string|max:200',
            'sprint_goals_partially_achieved' => 'nullable|array',
            'sprint_goals_partially_achieved.*' => 'string|max:200',
            'sprint_goals_not_achieved' => 'nullable|array',
            'sprint_goals_not_achieved.*' => 'string|max:200',
            'goal_achievement_rate' => 'nullable|numeric|min:0|max:100',
            'next_sprint_recommendations' => 'nullable|string|max:1000',
            'sprint_ceremony_effectiveness' => 'nullable|array',
            'sprint_ceremony_effectiveness.*' => 'string|max:200',
            'overall_sprint_rating' => 'nullable|numeric|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Calcular duración automáticamente si no se proporciona
        if (!isset($data['duration_days'])) {
            if (isset($data['planned_start_date']) && isset($data['planned_end_date'])) {
                $data['duration_days'] = \Carbon\Carbon::parse($data['planned_start_date'])
                    ->diffInDays(\Carbon\Carbon::parse($data['planned_end_date'])) + 1;
            } elseif (isset($data['start_date']) && isset($data['end_date'])) {
                $data['duration_days'] = \Carbon\Carbon::parse($data['start_date'])
                    ->diffInDays(\Carbon\Carbon::parse($data['end_date'])) + 1;
            }
        }

        // Calcular desviación de velocidad si se proporcionan ambos valores
        if (isset($data['planned_velocity']) && isset($data['actual_velocity']) && $data['planned_velocity'] > 0) {
            $data['velocity_deviation'] = round((($data['actual_velocity'] - $data['planned_velocity']) / $data['planned_velocity']) * 100, 2);
        }

        // Calcular tasa de resolución de bugs si se proporcionan ambos valores
        if (isset($data['bugs_found']) && isset($data['bugs_resolved']) && $data['bugs_found'] > 0) {
            $data['bug_resolution_rate'] = round(($data['bugs_resolved'] / $data['bugs_found']) * 100, 2);
        }

        // Calcular tasa de asistencia a Daily Scrums si se proporcionan ambos valores
        if (isset($data['daily_scrums_held']) && isset($data['daily_scrums_missed'])) {
            $totalScrums = $data['daily_scrums_held'] + $data['daily_scrums_missed'];
            if ($totalScrums > 0) {
                $data['daily_scrum_attendance_rate'] = round(($data['daily_scrums_held'] / $totalScrums) * 100, 2);
            }
        }

        $sprint = Sprint::create($data);

        return redirect()->route('sprints.index')->with('success', 'Sprint created successfully.');
    }

    /**
     * Mostrar detalles de un sprint específico
     */
    public function show(Sprint $sprint)
    {
        // Cargar el sprint con todas sus relaciones
        $sprint->load([
            'project',
            'tasks.user',
            'tasks.qaReviewedBy',
            'bugs.user',
            'bugs.qaReviewedBy'
        ]);

        // Obtener desarrolladores disponibles del proyecto
        $developers = $sprint->project->users()
            ->whereHas('roles', function($query) {
                $query->where('name', 'developer');
            })->get();

        return Inertia::render('Sprint/Show', [
            'sprint' => $sprint,
            'project' => $sprint->project,
            'tasks' => $sprint->tasks,
            'bugs' => $sprint->bugs,
            'developers' => $developers,
            'permissions' => auth()->user()->roles->first()->name ?? 'developer'
        ]);
    }

    public function update(Request $request, Sprint $sprint)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'goal' => 'sometimes|required|string|max:500',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            
            // Fase 1: Campos esenciales
            'description' => 'nullable|string|max:1000',
            'sprint_type' => 'nullable|in:regular,release,hotfix',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'duration_days' => 'nullable|integer|min:1',
            'sprint_objective' => 'nullable|string|max:1000',
            'user_stories_included' => 'nullable|array',
            'user_stories_included.*' => 'string|max:100',
            'assigned_tasks' => 'nullable|array',
            'assigned_tasks.*' => 'string|max:100',
            'acceptance_criteria' => 'nullable|string|max:1000',
            
            // Fase 2: Campos de seguimiento avanzado
            'planned_velocity' => 'nullable|integer|min:0',
            'actual_velocity' => 'nullable|integer|min:0',
            'velocity_deviation' => 'nullable|numeric',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'blockers' => 'nullable|array',
            'blockers.*' => 'string|max:200',
            'risks' => 'nullable|array',
            'risks.*' => 'string|max:200',
            'blocker_resolution_notes' => 'nullable|string|max:1000',
            'detailed_acceptance_criteria' => 'nullable|array',
            'detailed_acceptance_criteria.*' => 'string|max:200',
            'definition_of_done' => 'nullable|array',
            'definition_of_done.*' => 'string|max:200',
            'quality_gates' => 'nullable|array',
            'quality_gates.*' => 'string|max:200',
            'bugs_found' => 'nullable|integer|min:0',
            'bugs_resolved' => 'nullable|integer|min:0',
            'bug_resolution_rate' => 'nullable|numeric|min:0|max:100',
            'code_reviews_completed' => 'nullable|integer|min:0',
            'code_reviews_pending' => 'nullable|integer|min:0',
            'daily_scrums_held' => 'nullable|integer|min:0',
            'daily_scrums_missed' => 'nullable|integer|min:0',
            'daily_scrum_attendance_rate' => 'nullable|numeric|min:0|max:100',
            
            // Fase 3: Retrospectiva y Mejoras
            'achievements' => 'nullable|array',
            'achievements.*' => 'string|max:200',
            'problems' => 'nullable|array',
            'problems.*' => 'string|max:200',
            'actions_to_take' => 'nullable|array',
            'actions_to_take.*' => 'string|max:200',
            'retrospective_notes' => 'nullable|string|max:2000',
            'lessons_learned' => 'nullable|array',
            'lessons_learned.*' => 'string|max:200',
            'improvement_areas' => 'nullable|array',
            'improvement_areas.*' => 'string|max:200',
            'team_feedback' => 'nullable|array',
            'team_feedback.*' => 'string|max:200',
            'stakeholder_feedback' => 'nullable|array',
            'stakeholder_feedback.*' => 'string|max:200',
            'team_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'stakeholder_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'process_improvements' => 'nullable|array',
            'process_improvements.*' => 'string|max:200',
            'tool_improvements' => 'nullable|array',
            'tool_improvements.*' => 'string|max:200',
            'communication_improvements' => 'nullable|array',
            'communication_improvements.*' => 'string|max:200',
            'technical_debt_added' => 'nullable|array',
            'technical_debt_added.*' => 'string|max:200',
            'technical_debt_resolved' => 'nullable|array',
            'technical_debt_resolved.*' => 'string|max:200',
            'knowledge_shared' => 'nullable|array',
            'knowledge_shared.*' => 'string|max:200',
            'skills_developed' => 'nullable|array',
            'skills_developed.*' => 'string|max:200',
            'mentoring_sessions' => 'nullable|array',
            'mentoring_sessions.*' => 'string|max:200',
            'team_velocity_trend' => 'nullable|integer',
            'sprint_efficiency_score' => 'nullable|numeric|min:0|max:100',
            'sprint_goals_achieved' => 'nullable|array',
            'sprint_goals_achieved.*' => 'string|max:200',
            'sprint_goals_partially_achieved' => 'nullable|array',
            'sprint_goals_partially_achieved.*' => 'string|max:200',
            'sprint_goals_not_achieved' => 'nullable|array',
            'sprint_goals_not_achieved.*' => 'string|max:200',
            'goal_achievement_rate' => 'nullable|numeric|min:0|max:100',
            'next_sprint_recommendations' => 'nullable|string|max:1000',
            'sprint_ceremony_effectiveness' => 'nullable|array',
            'sprint_ceremony_effectiveness.*' => 'string|max:200',
            'overall_sprint_rating' => 'nullable|numeric|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Recalcular métricas automáticas
        if (isset($data['planned_velocity']) && isset($data['actual_velocity']) && $data['planned_velocity'] > 0) {
            $data['velocity_deviation'] = round((($data['actual_velocity'] - $data['planned_velocity']) / $data['planned_velocity']) * 100, 2);
        }

        if (isset($data['bugs_found']) && isset($data['bugs_resolved']) && $data['bugs_found'] > 0) {
            $data['bug_resolution_rate'] = round(($data['bugs_resolved'] / $data['bugs_found']) * 100, 2);
        }

        if (isset($data['daily_scrums_held']) && isset($data['daily_scrums_missed'])) {
            $totalScrums = $data['daily_scrums_held'] + $data['daily_scrums_missed'];
            if ($totalScrums > 0) {
                $data['daily_scrum_attendance_rate'] = round(($data['daily_scrums_held'] / $totalScrums) * 100, 2);
            }
        }

        $sprint->update($data);

        return back()->with('success', 'Sprint updated successfully.');
    }

    public function destroy(Sprint $sprint)
    {
        $sprint->delete();
        return redirect()->route('sprints.index')->with('success', 'Sprint deleted successfully.');
    }

    // Nuevos métodos para Fase 3
    public function finishSprint(Request $request, Sprint $sprint)
    {
        // Verificar que el usuario tiene permisos para finalizar sprints
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'team_leader') {
            return back()->with('error', 'You do not have permission to finish sprints.');
        }

        // Verificar que el sprint no esté ya finalizado
        if ($sprint->isCompleted()) {
            return back()->with('error', 'Sprint is already completed.');
        }

        // Verificar que todas las tareas estén completadas
        if (!$sprint->canBeFinished()) {
            $finishStatus = $sprint->getFinishStatus();
            $pendingTasksList = $finishStatus['pending_tasks_list']->take(5)->pluck('name')->implode(', ');
            
            $message = "Cannot finish sprint. {$finishStatus['pending_tasks']} task(s) still pending. ";
            if ($finishStatus['pending_tasks'] > 5) {
                $message .= "First 5: {$pendingTasksList}...";
            } else {
                $message .= "Pending: {$pendingTasksList}";
            }
            
            return back()->with('error', $message);
        }

        // Finalizar el sprint
        $success = $sprint->finishSprint();

        if ($success) {
            return back()->with('success', 'Sprint finished successfully.');
        } else {
            return back()->with('error', 'Failed to finish sprint.');
        }
    }

    public function addRetrospective(Request $request, Sprint $sprint)
    {
        // Verificar que el usuario tiene permisos para agregar retrospectivas
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'team_leader') {
            return back()->with('error', 'You do not have permission to add retrospectives.');
        }

        // Verificar que el sprint esté finalizado
        if (!$sprint->isCompleted()) {
            return back()->with('error', 'Sprint must be completed before adding retrospective.');
        }

        $validator = Validator::make($request->all(), [
            'achievements' => 'nullable|array',
            'achievements.*' => 'string|max:200',
            'problems' => 'nullable|array',
            'problems.*' => 'string|max:200',
            'actions_to_take' => 'nullable|array',
            'actions_to_take.*' => 'string|max:200',
            'retrospective_notes' => 'nullable|string|max:2000',
            'lessons_learned' => 'nullable|array',
            'lessons_learned.*' => 'string|max:200',
            'improvement_areas' => 'nullable|array',
            'improvement_areas.*' => 'string|max:200',
            'team_feedback' => 'nullable|array',
            'team_feedback.*' => 'string|max:200',
            'stakeholder_feedback' => 'nullable|array',
            'stakeholder_feedback.*' => 'string|max:200',
            'team_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'stakeholder_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'process_improvements' => 'nullable|array',
            'process_improvements.*' => 'string|max:200',
            'tool_improvements' => 'nullable|array',
            'tool_improvements.*' => 'string|max:200',
            'communication_improvements' => 'nullable|array',
            'communication_improvements.*' => 'string|max:200',
            'technical_debt_added' => 'nullable|array',
            'technical_debt_added.*' => 'string|max:200',
            'technical_debt_resolved' => 'nullable|array',
            'technical_debt_resolved.*' => 'string|max:200',
            'knowledge_shared' => 'nullable|array',
            'knowledge_shared.*' => 'string|max:200',
            'skills_developed' => 'nullable|array',
            'skills_developed.*' => 'string|max:200',
            'mentoring_sessions' => 'nullable|array',
            'mentoring_sessions.*' => 'string|max:200',
            'sprint_goals_achieved' => 'nullable|array',
            'sprint_goals_achieved.*' => 'string|max:200',
            'sprint_goals_partially_achieved' => 'nullable|array',
            'sprint_goals_partially_achieved.*' => 'string|max:200',
            'sprint_goals_not_achieved' => 'nullable|array',
            'sprint_goals_not_achieved.*' => 'string|max:200',
            'sprint_ceremony_effectiveness' => 'nullable|array',
            'sprint_ceremony_effectiveness.*' => 'string|max:200'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $retrospectiveData = $validator->validated();
        
        // Agregar la retrospectiva
        $success = $sprint->addRetrospective($retrospectiveData);

        if ($success) {
            return back()->with('success', 'Retrospective added successfully.');
        } else {
            return back()->with('error', 'Failed to add retrospective.');
        }
    }

    public function finishSprintWithRetrospective(Request $request, Sprint $sprint)
    {
        // Verificar permisos
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'team_leader') {
            return back()->with('error', 'You do not have permission to finish sprints.');
        }

        // Verificar que el sprint no esté ya finalizado
        if ($sprint->isCompleted()) {
            return back()->with('error', 'Sprint is already completed.');
        }

        // Verificar que todas las tareas estén completadas
        if (!$sprint->canBeFinished()) {
            $finishStatus = $sprint->getFinishStatus();
            $pendingTasksList = $finishStatus['pending_tasks_list']->take(5)->pluck('name')->implode(', ');
            
            $message = "Cannot finish sprint. {$finishStatus['pending_tasks']} task(s) still pending. ";
            if ($finishStatus['pending_tasks'] > 5) {
                $message .= "First 5: {$pendingTasksList}...";
            } else {
                $message .= "Pending: {$pendingTasksList}";
            }
            
            return back()->with('error', $message);
        }

        $validator = Validator::make($request->all(), [
            'achievements' => 'nullable|array',
            'achievements.*' => 'string|max:200',
            'problems' => 'nullable|array',
            'problems.*' => 'string|max:200',
            'actions_to_take' => 'nullable|array',
            'actions_to_take.*' => 'string|max:200',
            'retrospective_notes' => 'nullable|string|max:2000',
            'lessons_learned' => 'nullable|array',
            'lessons_learned.*' => 'string|max:200',
            'improvement_areas' => 'nullable|array',
            'improvement_areas.*' => 'string|max:200',
            'team_feedback' => 'nullable|array',
            'team_feedback.*' => 'string|max:200',
            'stakeholder_feedback' => 'nullable|array',
            'stakeholder_feedback.*' => 'string|max:200',
            'team_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'stakeholder_satisfaction_score' => 'nullable|numeric|min:1|max:10',
            'process_improvements' => 'nullable|array',
            'process_improvements.*' => 'string|max:200',
            'tool_improvements' => 'nullable|array',
            'tool_improvements.*' => 'string|max:200',
            'communication_improvements' => 'nullable|array',
            'communication_improvements.*' => 'string|max:200',
            'technical_debt_added' => 'nullable|array',
            'technical_debt_added.*' => 'string|max:200',
            'technical_debt_resolved' => 'nullable|array',
            'technical_debt_resolved.*' => 'string|max:200',
            'knowledge_shared' => 'nullable|array',
            'knowledge_shared.*' => 'string|max:200',
            'skills_developed' => 'nullable|array',
            'skills_developed.*' => 'string|max:200',
            'mentoring_sessions' => 'nullable|array',
            'mentoring_sessions.*' => 'string|max:200',
            'sprint_goals_achieved' => 'nullable|array',
            'sprint_goals_achieved.*' => 'string|max:200',
            'sprint_goals_partially_achieved' => 'nullable|array',
            'sprint_goals_partially_achieved.*' => 'string|max:200',
            'sprint_goals_not_achieved' => 'nullable|array',
            'sprint_goals_not_achieved.*' => 'string|max:200',
            'sprint_ceremony_effectiveness' => 'nullable|array',
            'sprint_ceremony_effectiveness.*' => 'string|max:200'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $retrospectiveData = $validator->validated();
        
        // Finalizar sprint con retrospectiva
        $success = $sprint->finishSprint($retrospectiveData);

        if ($success) {
            return back()->with('success', 'Sprint finished with retrospective successfully.');
        } else {
            return back()->with('error', 'Failed to finish sprint with retrospective.');
        }
    }

    public function getRetrospectiveSummary(Sprint $sprint)
    {
        if (!$sprint->isCompleted()) {
            return response()->json(['error' => 'Sprint is not completed'], 400);
        }

        return response()->json([
            'summary' => $sprint->getRetrospectiveSummary(),
            'has_retrospective' => $sprint->hasRetrospective()
        ]);
    }

    /**
     * Obtiene el estado de finalización del sprint
     */
    public function getFinishStatus(Sprint $sprint)
    {
        return response()->json($sprint->getFinishStatus());
    }
}
