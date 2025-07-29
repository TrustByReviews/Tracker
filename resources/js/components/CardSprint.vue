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
    if (!props.sprint.tasks) {
        return {
            total: 0,
            completed: 0,
            inProgress: 0,
            toDo: 0
        };
    }
    
    const tasks = props.sprint.tasks;
    return {
        total: tasks.length,
        completed: tasks.filter(t => t.status === 'completed' || t.status === 'done').length,
        inProgress: tasks.filter(t => t.status === 'in_progress' || t.status === 'in progress').length,
        toDo: tasks.filter(t => t.status === 'to_do' || t.status === 'todo' || t.status === 'pending').length
    };
}
</script>

<template>
    <div class="w-full max-w-xs flex flex-col justify-between p-5 rounded-xl border-l-4 border-blue-500 bg-gradient-to-br from-white to-blue-50 shadow transition hover:shadow-md">
    
        <!-- Sprint name -->
        <h2 class="text-lg font-semibold text-blue-700 flex items-center gap-2 break-words">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6m-6 0V9a4 4 0 00-4-4H5a4 4 0 00-4 4v6a4 4 0 004 4h2" />
            </svg>
            {{ props.sprint.name.length > 25
            ? props.sprint.name.slice(0, 25) + '...'
            : props.sprint.name }}
        </h2>

        <!-- Sprint goal -->
        <p class="text-sm text-gray-700 mt-2 italic break-words">
            {{ props.sprint.goal.length > 30 
            ? props.sprint.goal.slice(0, 30) + '...'
            : props.sprint.goal }}
        </p>

        <!-- Sprint Progress -->
        <div class="mt-3">
            <div class="flex justify-between text-xs text-gray-600 mb-1">
                <span>Progress</span>
                <span>{{ getSprintProgress() }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                    class="bg-blue-600 h-2 rounded-full transition-all"
                    :style="{ width: `${getSprintProgress()}%` }"
                ></div>
            </div>
        </div>

        <!-- Sprint Stats -->
        <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
            <div class="text-center p-2 bg-gray-100 rounded">
                <div class="font-semibold text-gray-800">{{ getSprintStats().total }}</div>
                <div class="text-gray-600">Total</div>
            </div>
            <div class="text-center p-2 bg-green-100 rounded">
                <div class="font-semibold text-green-800">{{ getSprintStats().completed }}</div>
                <div class="text-green-600">Done</div>
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
                <button class="bg-blue-100 text-blue-600 px-3 py-1 rounded hover:bg-blue-200 transition-colors" type="button" @click="showSprint">
                    View more
                </button>
            </div>
        </div>
    </div>
</template>