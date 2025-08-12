<script setup lang="ts">
import { defineProps, ref } from 'vue';
import FinishSprintModal from './FinishSprintModal.vue';
import EditSprintModal from './EditSprintModal.vue';

interface Sprint {
    id: string,
    name: string,
    goal: string,
    start_date: string,
    end_date: string,
    project_id: string,
    created_at?: string,
    updated_at?: string,
    tasks?: any[],
    bugs?: any[],
    project?: {
        id: string,
        name: string
    },
    // Fase 1: Campos esenciales
    description?: string,
    sprint_type?: string,
    planned_start_date?: string,
    planned_end_date?: string,
    actual_start_date?: string,
    actual_end_date?: string,
    duration_days?: number,
    sprint_objective?: string,
    user_stories_included?: string[],
    assigned_tasks?: string[],
    acceptance_criteria?: string,
    
    // Fase 2: Campos de seguimiento avanzado
    planned_velocity?: number,
    actual_velocity?: number,
    velocity_deviation?: number,
    progress_percentage?: number,
    blockers?: string[],
    risks?: string[],
    blocker_resolution_notes?: string,
    detailed_acceptance_criteria?: string[],
    definition_of_done?: string[],
    quality_gates?: string[],
    bugs_found?: number,
    bugs_resolved?: number,
    bug_resolution_rate?: number,
    code_reviews_completed?: number,
    code_reviews_pending?: number,
    daily_scrums_held?: number,
    daily_scrums_missed?: number,
    daily_scrum_attendance_rate?: number,
    
    // Fase 3: Campos de retrospectiva y mejoras
    isCompleted?: boolean,
    hasRetrospective?: boolean,
    achievements?: string[],
    problems?: string[],
    actions_to_take?: string[],
    retrospective_notes?: string,
    lessons_learned?: string[],
    improvement_areas?: string[],
    team_feedback?: string[],
    stakeholder_feedback?: string[],
    team_satisfaction_score?: number,
    stakeholder_satisfaction_score?: number,
    process_improvements?: string[],
    tool_improvements?: string[],
    communication_improvements?: string[],
    technical_debt_added?: string[],
    technical_debt_resolved?: string[],
    knowledge_shared?: string[],
    skills_developed?: string[],
    mentoring_sessions?: string[],
    sprint_goals_achieved?: string[],
    sprint_goals_partially_achieved?: string[],
    sprint_goals_not_achieved?: string[],
    sprint_ceremony_effectiveness?: string[]
}

const props = defineProps<{
    sprint: Sprint,
    permissions: string,
    project_id: string
}>()

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US');
}

const showSprint = () => {
    // Get valid project_id, trying multiple sources
    let validProjectId: string | null = props.project_id;
    
    // If props.project_id is invalid, try using sprint.project_id
    if (!validProjectId || validProjectId === 'NaN' || validProjectId === 'undefined' || validProjectId === null) {
        validProjectId = props.sprint.project_id;
    }
    
    // If still invalid, try using sprint.project.id
    if (!validProjectId || validProjectId === 'NaN' || validProjectId === 'undefined' || validProjectId === null) {
        validProjectId = props.sprint.project?.id || null;
    }
    
    // Validate that we have a valid project_id
    if (!validProjectId || validProjectId === 'NaN' || validProjectId === 'undefined' || validProjectId === null) {
        console.error('No valid project_id found:', {
            props_project_id: props.project_id,
            sprint_project_id: props.sprint.project_id,
            sprint_project_id_from_relation: props.sprint.project?.id
        });
        alert('Error: No valid project ID found for this sprint.');
        return;
    }
    
    // Validate that sprint.id is a valid UUID
    if (!props.sprint.id || props.sprint.id === 'NaN' || props.sprint.id === 'undefined') {
        console.error('Invalid sprint.id:', props.sprint.id);
        alert('Error: Invalid sprint ID.');
        return;
    }
    
    const url = `/team-leader/sprints/${props.sprint.id}`;
    window.location.href = url;
}

// Calculate sprint progress based on tasks and new progress_percentage field
const getSprintProgress = () => {
    // Use the new progress_percentage field if available, otherwise calculate from tasks
    if (props.sprint.progress_percentage !== undefined && props.sprint.progress_percentage !== null) {
        return Number(props.sprint.progress_percentage) || 0;
    }
    
    if (!props.sprint.tasks || props.sprint.tasks.length === 0) {
        return 0;
    }
    
    const completedTasks = props.sprint.tasks.filter(task => 
        task.status === 'completed' || task.status === 'done'
    ).length;
    
    return Math.round((completedTasks / props.sprint.tasks.length) * 100);
}

// Get enhanced sprint statistics
const getSprintStats = () => {
    if (!props.sprint.tasks && !props.sprint.bugs) {
        return {
            total: 0,
            completed: 0,
            inProgress: 0,
            toDo: 0,
            completionRate: 0,
            priorityScore: 0,
            daysToEnd: 0,
            totalTasks: 0,
            completedTasks: 0,
            totalBugs: 0,
            completedBugs: 0,
            // New metrics from Phase 2 - ensure they are numbers
            velocityDeviation: Number(props.sprint.velocity_deviation) || 0,
            bugResolutionRate: Number(props.sprint.bug_resolution_rate) || 0,
            attendanceRate: Number(props.sprint.daily_scrum_attendance_rate) || 0,
            blockersCount: props.sprint.blockers?.length || 0,
            risksCount: props.sprint.risks?.length || 0,
            codeReviewCompletionRate: 0
        };
    }
    
    const tasks = props.sprint.tasks || [];
    const bugs = props.sprint.bugs || [];
    
    // Task statistics
    const totalTasks = tasks.length;
    const completedTasks = tasks.filter(task => 
        task.status === 'completed' || task.status === 'done'
    ).length;
    const inProgressTasks = tasks.filter(task => 
        task.status === 'in progress'
    ).length;
    
    // Bug statistics
    const totalBugs = bugs.length;
    const completedBugs = bugs.filter(bug => 
        ['resolved', 'verified', 'closed'].includes(bug.status)
    ).length;
    const inProgressBugs = bugs.filter(bug => 
        ['assigned', 'in progress'].includes(bug.status)
    ).length;
    
    // Combined totals
    const total = totalTasks + totalBugs;
    const completed = completedTasks + completedBugs;
    const inProgress = inProgressTasks + inProgressBugs;
    const toDo = total - completed - inProgress;
    
    const completionRate = total > 0 ? Math.round((completed / total) * 100) : 0;
    
    // Calculate remaining days
    const endDate = new Date(props.sprint.end_date);
    const today = new Date();
    const daysToEnd = Math.max(0, Math.ceil((endDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)));
    
    // Calculate priority
    let priorityScore = 50; // Normal priority
    if (daysToEnd === 0 && completionRate < 100) {
        priorityScore = 100; // Overdue
    } else if (daysToEnd <= 3 && completionRate < 50) {
        priorityScore = 90; // High priority
    } else if (daysToEnd <= 7 && completionRate < 70) {
        priorityScore = 70; // Medium priority
    }
    
    // New metrics from Phase 2 - ensure they are numbers
    const velocityDeviation = Number(props.sprint.velocity_deviation) || 0;
    const bugResolutionRate = Number(props.sprint.bug_resolution_rate) || 0;
    const attendanceRate = Number(props.sprint.daily_scrum_attendance_rate) || 0;
    const blockersCount = props.sprint.blockers?.length || 0;
    const risksCount = props.sprint.risks?.length || 0;
    const codeReviewCompletionRate = props.sprint.code_reviews_completed && props.sprint.code_reviews_pending 
        ? Math.round((props.sprint.code_reviews_completed / (props.sprint.code_reviews_completed + props.sprint.code_reviews_pending)) * 100)
        : 0;
    
    return {
        total,
        completed,
        inProgress,
        toDo,
        completionRate,
        priorityScore,
        daysToEnd,
        totalTasks,
        completedTasks,
        totalBugs,
        completedBugs,
        velocityDeviation,
        bugResolutionRate,
        attendanceRate,
        blockersCount,
        risksCount,
        codeReviewCompletionRate
    };
}

// Get sprint status
const getSprintStatus = () => {
    const today = new Date();
    const startDate = new Date(props.sprint.start_date);
    const endDate = new Date(props.sprint.end_date);
    
    if (today >= startDate && today <= endDate) {
        return 'active';
    } else if (today < startDate) {
        return 'upcoming';
    } else {
        return 'completed';
    }
}

// Get priority color
const getPriorityColor = () => {
    const stats = getSprintStats();
    if (stats.priorityScore > 2) return 'text-red-600';
    if (stats.priorityScore > 1) return 'text-orange-600';
    if (stats.priorityScore > 0.5) return 'text-yellow-600';
    return 'text-green-600';
}

// Get priority icon
const getPriorityIcon = () => {
    const stats = getSprintStats();
    if (stats.priorityScore > 2) return 'alert-triangle';
    if (stats.priorityScore > 1) return 'clock';
    if (stats.priorityScore > 0.5) return 'info';
    return 'check-circle';
}

// Get status color
const getStatusColor = () => {
    const status = getSprintStatus();
    switch (status) {
        case 'active': return 'text-green-600';
        case 'upcoming': return 'text-blue-600';
        case 'completed': return 'text-gray-600';
        default: return 'text-gray-600';
    }
}

// Get border color
const getBorderColor = () => {
    const status = getSprintStatus();
    switch (status) {
        case 'active': return 'border-green-500';
        case 'upcoming': return 'border-blue-500';
        case 'completed': return 'border-gray-500';
        default: return 'border-blue-500';
    }
}

// Edit sprint modal state
const editModalOpen = ref(false);

const openEditModal = () => {
    editModalOpen.value = true;
};

const closeEditModal = () => {
    editModalOpen.value = false;
};

// Get sprint type badge
const getSprintTypeBadge = (sprintType: string) => {
    const types = {
        'regular': { label: 'Regular', class: 'bg-blue-100 text-blue-800' },
        'release': { label: 'Release', class: 'bg-green-100 text-green-800' },
        'hotfix': { label: 'Hotfix', class: 'bg-red-100 text-red-800' }
    }
    return types[sprintType as keyof typeof types] || { label: 'Unknown', class: 'bg-gray-100 text-gray-800' }
}

// Get velocity deviation color
const getVelocityDeviationColor = (deviation: number) => {
    if (deviation < -10) return 'text-red-600';
    if (deviation < -5) return 'text-orange-600';
    if (deviation > 10) return 'text-red-600';
    if (deviation > 5) return 'text-orange-600';
    return 'text-green-600';
}
</script>

<template>
    <div class="w-full max-w-xs flex flex-col justify-between p-4 sm:p-5 rounded-xl border-l-4 shadow transition hover:shadow-md"
         :class="getBorderColor() + ' bg-gradient-to-br from-white to-gray-50'">
    
        <!-- Header con estado, tipo y prioridad -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center gap-2 break-words">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" :class="getStatusColor()" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6m-6 0V9a4 4 0 00-4-4H5a4 4 0 00-4 4v6a4 4 0 004 4h2" />
                    </svg>
                    <span class="truncate">{{ props.sprint.name }}</span>
                </h2>
                
                <!-- Sprint Type Badge -->
                <div v-if="props.sprint.sprint_type" class="mt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                          :class="getSprintTypeBadge(props.sprint.sprint_type).class">
                        {{ getSprintTypeBadge(props.sprint.sprint_type).label }}
                    </span>
                </div>
            </div>
            
            <!-- Indicador de prioridad -->
            <div class="flex items-center gap-1 ml-2" :class="getPriorityColor()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="text-xs font-medium">{{ getSprintStats().priorityScore.toFixed(1) }}</span>
            </div>
        </div>

        <!-- Sprint goal -->
        <p class="text-xs sm:text-sm text-gray-600 mt-2 italic break-words">
            {{ props.sprint.goal.length > 40 
            ? props.sprint.goal.slice(0, 40) + '...'
            : props.sprint.goal }}
        </p>

        <!-- Sprint Progress -->
        <div class="mt-3">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
                <span>Progress</span>
                <span>{{ getSprintProgress().toFixed(1) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                    class="h-2 rounded-full transition-all"
                    :class="getSprintProgress() >= 80 ? 'bg-green-600' : getSprintProgress() >= 50 ? 'bg-yellow-600' : 'bg-red-600'"
                    :style="{ width: `${getSprintProgress()}%` }"
                ></div>
            </div>
        </div>

        <!-- Enhanced Sprint Stats -->
        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
            <!-- Tasks -->
            <div class="space-y-1">
                <div class="text-center p-2 bg-blue-100 rounded">
                    <div class="font-semibold text-blue-800">{{ getSprintStats().totalTasks }}</div>
                    <div class="text-blue-600">Tasks</div>
                </div>
                <div class="text-center p-1 bg-green-100 rounded">
                    <div class="font-semibold text-green-800">{{ getSprintStats().completedTasks }}</div>
                    <div class="text-green-600">Done</div>
                </div>
            </div>
            
            <!-- Bugs -->
            <div class="space-y-1">
                <div class="text-center p-2 bg-red-100 rounded">
                    <div class="font-semibold text-red-800">{{ getSprintStats().totalBugs }}</div>
                    <div class="text-red-600">Bugs</div>
                </div>
                <div class="text-center p-1 bg-green-100 rounded">
                    <div class="font-semibold text-green-800">{{ getSprintStats().completedBugs }}</div>
                    <div class="text-green-600">Fixed</div>
                </div>
            </div>
        </div>

        <!-- New Metrics from Phase 2 -->
        <div class="mt-2 grid grid-cols-3 gap-1 text-xs">
            <!-- Velocity Deviation -->
            <div class="text-center p-1 bg-orange-100 rounded">
                <div class="font-semibold" :class="getVelocityDeviationColor(getSprintStats().velocityDeviation)">
                    {{ getSprintStats().velocityDeviation.toFixed(1) }}%
                </div>
                <div class="text-orange-600">Vel. Dev.</div>
            </div>
            
            <!-- Bug Resolution Rate -->
            <div class="text-center p-1 bg-purple-100 rounded">
                <div class="font-semibold text-purple-800">{{ getSprintStats().bugResolutionRate.toFixed(1) }}%</div>
                <div class="text-purple-600">Bug Rate</div>
            </div>
            
            <!-- Attendance Rate -->
            <div class="text-center p-1 bg-indigo-100 rounded">
                <div class="font-semibold text-indigo-800">{{ getSprintStats().attendanceRate.toFixed(1) }}%</div>
                <div class="text-indigo-600">Attendance</div>
            </div>
        </div>

        <!-- Blockers and Risks Summary -->
        <div v-if="getSprintStats().blockersCount > 0 || getSprintStats().risksCount > 0" class="mt-2 grid grid-cols-2 gap-1 text-xs">
            <div class="text-center p-1 bg-red-50 rounded">
                <div class="font-semibold text-red-700">{{ getSprintStats().blockersCount }}</div>
                <div class="text-red-600">Blockers</div>
            </div>
            <div class="text-center p-1 bg-yellow-50 rounded">
                <div class="font-semibold text-yellow-700">{{ getSprintStats().risksCount }}</div>
                <div class="text-yellow-600">Risks</div>
            </div>
        </div>

        <!-- Información de tiempo -->
        <div class="mt-3 text-xs text-gray-600">
            <div class="flex items-center justify-between">
                <span>Days to end:</span>
                <span :class="getSprintStats().daysToEnd <= 3 ? 'text-red-600 font-semibold' : getSprintStats().daysToEnd <= 7 ? 'text-orange-600' : 'text-gray-600'">
                    {{ getSprintStats().daysToEnd > 0 ? getSprintStats().daysToEnd : 'Ended' }}
                </span>
            </div>
            
            <!-- Duration if available -->
            <div v-if="props.sprint.duration_days" class="flex items-center justify-between mt-1">
                <span>Duration:</span>
                <span class="text-gray-600">{{ props.sprint.duration_days }} days</span>
            </div>
        </div>

        <!-- Date and view more -->
        <div class="mt-auto">
            <div class="text-xs text-gray-600 flex items-center gap-2 mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="truncate">{{ formatDate(props.sprint.start_date) }} → {{ formatDate(props.sprint.end_date) }}</span>
            </div>
            <div class="flex justify-between items-center mt-2">
                <div class="flex gap-2">
                    <FinishSprintModal 
                        v-if="getSprintStatus() === 'active' || (getSprintStatus() === 'completed' && !props.sprint.hasRetrospective)"
                        :sprint="props.sprint"
                    />
                    <button 
                        v-if="permissions === 'admin' || permissions === 'team_leader'"
                        class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition-colors text-xs" 
                        type="button" 
                        @click="openEditModal"
                    >
                        Edit
                    </button>
                </div>
                <button class="bg-blue-100 text-blue-600 px-3 py-1 rounded hover:bg-blue-200 transition-colors text-xs" type="button" @click="showSprint">
                    View more
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Sprint Modal -->
    <EditSprintModal 
        v-model:open="editModalOpen"
        :sprint="props.sprint"
    />
</template>