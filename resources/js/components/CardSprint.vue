<script setup lang="ts">
import { defineProps } from 'vue';

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
    }
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

    
    // Obtener el project_id válido, intentando múltiples fuentes
    let validProjectId: string | null = props.project_id;
    
    // Si props.project_id es inválido, intentar usar sprint.project_id
    if (!validProjectId || validProjectId === 'NaN' || validProjectId === 'undefined' || validProjectId === null) {
        validProjectId = props.sprint.project_id;

    }
    
    // Si aún es inválido, intentar usar sprint.project.id
    if (!validProjectId || validProjectId === 'NaN' || validProjectId === 'undefined' || validProjectId === null) {
        validProjectId = props.sprint.project?.id || null;

    }
    
    // Validar que tengamos un project_id válido
    if (!validProjectId || validProjectId === 'NaN' || validProjectId === 'undefined' || validProjectId === null) {
        console.error('No valid project_id found:', {
            props_project_id: props.project_id,
            sprint_project_id: props.sprint.project_id,
            sprint_project_id_from_relation: props.sprint.project?.id
        });
        alert('Error: No valid project ID found for this sprint.');
        return;
    }
    
    // Validar que sprint.id sea un UUID válido
    if (!props.sprint.id || props.sprint.id === 'NaN' || props.sprint.id === 'undefined') {
        console.error('Invalid sprint.id:', props.sprint.id);
        alert('Error: Invalid sprint ID.');
        return;
    }
    
    const url = `/projects/${validProjectId}/sprints/${props.sprint.id}`;
    window.location.href = url;
}

// Calcular el progreso del sprint basado en las tareas
const getSprintProgress = () => {
    if (!props.sprint.tasks || props.sprint.tasks.length === 0) {
        return 0;
    }
    
    const completedTasks = props.sprint.tasks.filter(task => 
        task.status === 'completed' || task.status === 'done'
    ).length;
    
    return Math.round((completedTasks / props.sprint.tasks.length) * 100);
}

// Obtener estadísticas del sprint
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
            completedBugs: 0
        };
    }
    
    const tasks = props.sprint.tasks || [];
    const bugs = props.sprint.bugs || [];
    
    // Estadísticas de tareas
    const totalTasks = tasks.length;
    const completedTasks = tasks.filter(task => 
        task.status === 'completed' || task.status === 'done'
    ).length;
    const inProgressTasks = tasks.filter(task => 
        task.status === 'in progress'
    ).length;
    
    // Estadísticas de bugs
    const totalBugs = bugs.length;
    const completedBugs = bugs.filter(bug => 
        ['resolved', 'verified', 'closed'].includes(bug.status)
    ).length;
    const inProgressBugs = bugs.filter(bug => 
        ['assigned', 'in progress'].includes(bug.status)
    ).length;
    
    // Totales combinados
    const total = totalTasks + totalBugs;
    const completed = completedTasks + completedBugs;
    const inProgress = inProgressTasks + inProgressBugs;
    const toDo = total - completed - inProgress;
    
    const completionRate = total > 0 ? Math.round((completed / total) * 100) : 0;
    
    // Calcular días restantes
    const endDate = new Date(props.sprint.end_date);
    const today = new Date();
    const daysToEnd = Math.max(0, Math.ceil((endDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)));
    
    // Calcular prioridad
    let priorityScore = 50; // Prioridad normal
    if (daysToEnd === 0 && completionRate < 100) {
        priorityScore = 100; // Atrasado
    } else if (daysToEnd <= 3 && completionRate < 50) {
        priorityScore = 90; // Alta prioridad
    } else if (daysToEnd <= 7 && completionRate < 70) {
        priorityScore = 70; // Prioridad media
    }
    
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
        completedBugs
    };
}

// Obtener estado del sprint
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

// Obtener color de prioridad
const getPriorityColor = () => {
    const stats = getSprintStats();
    if (stats.priorityScore > 2) return 'text-red-600';
    if (stats.priorityScore > 1) return 'text-orange-600';
    if (stats.priorityScore > 0.5) return 'text-yellow-600';
    return 'text-green-600';
}

// Obtener icono de prioridad
const getPriorityIcon = () => {
    const stats = getSprintStats();
    if (stats.priorityScore > 2) return 'alert-triangle';
    if (stats.priorityScore > 1) return 'clock';
    if (stats.priorityScore > 0.5) return 'info';
    return 'check-circle';
}

// Obtener color de estado
const getStatusColor = () => {
    const status = getSprintStatus();
    switch (status) {
        case 'active': return 'text-green-600';
        case 'upcoming': return 'text-blue-600';
        case 'completed': return 'text-gray-600';
        default: return 'text-gray-600';
    }
}

// Obtener color de borde
const getBorderColor = () => {
    const status = getSprintStatus();
    switch (status) {
        case 'active': return 'border-green-500';
        case 'upcoming': return 'border-blue-500';
        case 'completed': return 'border-gray-500';
        default: return 'border-blue-500';
    }
}
</script>

<template>
    <div class="w-full max-w-xs flex flex-col justify-between p-5 rounded-xl border-l-4 shadow transition hover:shadow-md"
         :class="getBorderColor() + ' bg-gradient-to-br from-white to-gray-50'">
    
        <!-- Header con estado y prioridad -->
        <div class="flex items-start justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 break-words">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="getStatusColor()" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6m-6 0V9a4 4 0 00-4-4H5a4 4 0 00-4 4v6a4 4 0 004 4h2" />
                </svg>
                {{ props.sprint.name.length > 20
                ? props.sprint.name.slice(0, 20) + '...'
                : props.sprint.name }}
            </h2>
            
            <!-- Indicador de prioridad -->
            <div class="flex items-center gap-1" :class="getPriorityColor()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="text-xs font-medium">{{ getSprintStats().priorityScore.toFixed(1) }}</span>
            </div>
        </div>

        <!-- Sprint goal -->
        <p class="text-sm text-gray-600 mt-2 italic break-words">
            {{ props.sprint.goal.length > 40 
            ? props.sprint.goal.slice(0, 40) + '...'
            : props.sprint.goal }}
        </p>

        <!-- Sprint Progress -->
        <div class="mt-3">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
                <span>Progress</span>
                <span>{{ getSprintStats().completionRate }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                    class="h-2 rounded-full transition-all"
                    :class="getSprintStats().completionRate >= 80 ? 'bg-green-600' : getSprintStats().completionRate >= 50 ? 'bg-yellow-600' : 'bg-red-600'"
                    :style="{ width: `${getSprintStats().completionRate}%` }"
                ></div>
            </div>
        </div>

        <!-- Sprint Stats Detalladas -->
        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
            <!-- Tareas -->
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

        <!-- Total Progress Summary -->
        <div class="mt-2 grid grid-cols-3 gap-1 text-xs">
            <div class="text-center p-2 bg-gray-100 rounded">
                <div class="font-semibold text-gray-800">{{ getSprintStats().total }}</div>
                <div class="text-gray-600">Total</div>
            </div>
            <div class="text-center p-2 bg-green-100 rounded">
                <div class="font-semibold text-green-800">{{ getSprintStats().completed }}</div>
                <div class="text-green-600">Done</div>
            </div>
            <div class="text-center p-2 bg-orange-100 rounded">
                <div class="font-semibold text-orange-800">{{ getSprintStats().toDo }}</div>
                <div class="text-orange-600">Pending</div>
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
        </div>

        <!-- Date and view more -->
        <div class="mt-auto">
            <div class="text-xs text-gray-600 flex items-center gap-2 mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(props.sprint.start_date) }} → {{ formatDate(props.sprint.end_date) }}
            </div>
            <div class="flex justify-end mt-2">
                <button class="bg-blue-100 text-blue-600 px-3 py-1 rounded hover:bg-blue-200 transition-colors text-xs" type="button" @click="showSprint">
                    View more
                </button>
            </div>
        </div>
    </div>
</template>