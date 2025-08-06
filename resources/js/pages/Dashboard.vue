<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DashboardStats from '@/components/DashboardStats.vue';
import TaskList from '@/components/TaskList.vue';
import ProjectList from '@/components/ProjectList.vue';
import AdminStats from '@/components/AdminStats.vue';
import DeveloperPerformance from '@/components/DeveloperPerformance.vue';
import ProjectStatus from '@/components/ProjectStatus.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Props from the controller
interface Props {
    user: any;
    isAdmin: boolean;
    // Developer props
    assignedProjects?: any[];
    tasksInProgress?: any[];
    completedTasks?: any[];
    highPriorityUnassignedTasks?: any[];
    stats?: any;
    // Admin props
    projects?: any[];
    developers?: any[];
    systemStats?: any;
    tasksRequiringAttention?: any[];
    activeProjectsSummary?: any[];
    developerMetrics?: any[];
    // Bugs props
    bugs?: any[];
    bugStats?: any;
}

const props = defineProps<Props>();

const assignTask = (task: any) => {
    router.put(`/tasks/${task.id}`, {
        user_id: props.user.id,
        status: 'in_progress'
    });
};

const formatTime = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const getTimeExcess = (task: any) => {
    if (!task.estimated_hours) return 'N/A';
    
    const estimatedSeconds = task.estimated_hours * 3600;
    const actualSeconds = task.total_time_seconds || 0;
    const excessSeconds = actualSeconds - estimatedSeconds;
    
    if (excessSeconds <= 0) return '0h';
    
    const excessHours = Math.floor(excessSeconds / 3600);
    const excessMinutes = Math.floor((excessSeconds % 3600) / 60);
    
    return `${excessHours}h ${excessMinutes}m`;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const viewTaskDetails = (taskId: string) => {
    router.visit(`/tasks/${taskId}`);
};

const viewProjectDetails = (projectId: string) => {
    router.visit(`/projects/${projectId}`);
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Developer Dashboard -->
            <div v-if="!isAdmin">
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-4 text-white">
                    <h1 class="text-xl font-bold mb-1">Welcome back, {{ user.name }}!</h1>
                    <p class="text-blue-100 text-sm">Here's your current work overview and progress.</p>
                </div>

                <!-- Statistics Cards -->
                <DashboardStats :stats="stats" />

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- My Projects -->
                    <ProjectList :projects="assignedProjects || []" :user-id="user.id" />

                    <!-- Tasks In Progress -->
                    <TaskList 
                        title="Tasks In Progress" 
                        description="Tasks you're currently working on"
                        :tasks="tasksInProgress || []"
                    />
                </div>

                <!-- Tasks Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Completed Tasks -->
                    <TaskList 
                        title="Completed Tasks" 
                        description="Tasks you've finished"
                        :tasks="completedTasks || []"
                    />

                    <!-- High Priority Unassigned Tasks -->
                    <TaskList 
                        title="High Priority Tasks Available" 
                        description="High priority tasks that need assignment"
                        :tasks="highPriorityUnassignedTasks || []"
                        :show-assign-button="true"
                        @assign-task="assignTask"
                    />
                </div>
            </div>

            <!-- Admin Dashboard -->
            <div v-else>
                <!-- Welcome Section -->
                <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-4 text-white">
                    <h1 class="text-xl font-bold mb-1">Admin Dashboard</h1>
                    <p class="text-green-100 text-sm">System overview and team performance metrics.</p>
                </div>

                <!-- System Statistics -->
                <AdminStats :stats="systemStats" />

                <!-- Compact Grid Layout -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    <!-- Tasks Requiring Attention -->
                    <div class="xl:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tareas que Requieren Atención</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Tareas que exceden el tiempo estimado en más del 20%</p>
                            </div>
                            <div class="p-4 max-h-64 overflow-y-auto">
                                <div v-if="tasksRequiringAttention?.length === 0" class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">¡Excelente! No hay tareas que requieran atención</p>
                                </div>
                                
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="task in tasksRequiringAttention"
                                        :key="task.id"
                                        class="border border-red-300 dark:border-red-600 rounded-lg p-3 bg-red-50 dark:bg-red-900/20"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ task.name }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Dev: {{ task.user?.name }}</p>
                                                <div class="mt-2 flex items-center space-x-3 text-xs">
                                                    <span class="text-red-600 dark:text-red-400 font-medium">
                                                        Real: {{ formatTime(task.total_time_seconds || 0) }}
                                                    </span>
                                                    <span v-if="task.estimated_hours" class="text-gray-500 dark:text-gray-400">
                                                        Est: {{ task.estimated_hours }}h
                                                    </span>
                                                    <span class="text-red-600 dark:text-red-400 font-medium">
                                                        +{{ getTimeExcess(task) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-2 flex-shrink-0">
                                                <button
                                                    @click="viewTaskDetails(task.id)"
                                                    class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                                >
                                                    Ver
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Projects Summary -->
                    <div class="xl:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Proyectos Activos</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Resumen de proyectos en curso</p>
                            </div>
                            <div class="p-4 max-h-64 overflow-y-auto">
                                <div v-if="activeProjectsSummary?.length === 0" class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">No hay proyectos activos</p>
                                </div>
                                
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="project in activeProjectsSummary"
                                        :key="project.id"
                                        class="border border-gray-200 dark:border-gray-600 rounded-lg p-3"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ project.name }}</h4>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ project.team_members_count }}m</span>
                                        </div>
                                        
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500 dark:text-gray-400">En progreso:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ project.in_progress_tasks_count }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-500 dark:text-gray-400">Pendientes:</span>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ project.pending_tasks_count }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-600">
                                            <button
                                                @click="viewProjectDetails(project.id)"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                            >
                                                Ver detalles →
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer Performance Metrics -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Métricas de Rendimiento por Desarrollador</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Eficiencia y productividad del equipo</p>
                    </div>
                    <div class="p-4 max-h-64 overflow-y-auto">
                        <div v-if="developerMetrics?.length === 0" class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <p class="mt-1 text-xs text-gray-500">No hay métricas disponibles</p>
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dev</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tareas</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tiempo</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Eficiencia</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Promedio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    <tr v-for="metric in developerMetrics" :key="metric.developer.id">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-xs font-medium text-gray-900 dark:text-white">{{ metric.developer.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ metric.developer.email }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-xs text-gray-900 dark:text-white">{{ metric.total_tasks }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ metric.completed_tasks }} completadas</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-white">
                                            {{ metric.formatted_time_spent }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-12 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mr-2">
                                                    <div
                                                        :class="{
                                                            'bg-green-500': metric.efficiency_percentage >= 80,
                                                            'bg-yellow-500': metric.efficiency_percentage >= 60 && metric.efficiency_percentage < 80,
                                                            'bg-red-500': metric.efficiency_percentage < 60
                                                        }"
                                                        class="h-1.5 rounded-full"
                                                        :style="{ width: Math.min(metric.efficiency_percentage, 100) + '%' }"
                                                    ></div>
                                                </div>
                                                <span class="text-xs text-gray-900 dark:text-white">{{ metric.efficiency_percentage }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 dark:text-white">
                                            {{ metric.average_task_time }}h
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Acciones Rápidas</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Acceso directo a las funciones principales</p>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <a href="/payment-reports" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-2 rounded-full bg-emerald-100 dark:bg-emerald-900">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white">Payment Reports</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Generate payment reports</p>
                                </div>
                            </a>
                            
                            <a href="/users" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white">Manage Users</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Add and manage team</p>
                                </div>
                            </a>

                            <a href="/permissions" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900">
                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white">Permissions</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage user permissions</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bugs Section -->
                <div v-if="bugs && bugs.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Bugs</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Latest bugs requiring attention</p>
                            </div>
                            <button
                                @click="router.visit('/bugs')"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                            >
                                View All →
                            </button>
                        </div>
                    </div>
                    <div class="p-4 max-h-64 overflow-y-auto">
                        <div class="space-y-3">
                            <div
                                v-for="bug in bugs.slice(0, 5)"
                                :key="bug.id"
                                class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                @click="router.visit(`/bugs/${bug.id}`)"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="text-xs font-medium text-gray-900 dark:text-white truncate flex-1">{{ bug.title }}</h4>
                                    <div class="flex items-center space-x-1 ml-2">
                                        <span
                                            :class="{
                                                'bg-blue-100 text-blue-800': bug.status === 'new',
                                                'bg-yellow-100 text-yellow-800': bug.status === 'assigned',
                                                'bg-orange-100 text-orange-800': bug.status === 'in progress',
                                                'bg-green-100 text-green-800': bug.status === 'resolved',
                                                'bg-purple-100 text-purple-800': bug.status === 'verified',
                                                'bg-gray-100 text-gray-800': bug.status === 'closed',
                                                'bg-red-100 text-red-800': bug.status === 'reopened'
                                            }"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ bug.status }}
                                        </span>
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': bug.importance === 'low',
                                                'bg-yellow-100 text-yellow-800': bug.importance === 'medium',
                                                'bg-orange-100 text-orange-800': bug.importance === 'high',
                                                'bg-red-100 text-red-800': bug.importance === 'critical'
                                            }"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ bug.importance }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ bug.project?.name || 'No Project' }}</span>
                                    <span>{{ formatDate(bug.created_at) }}</span>
                                </div>
                                
                                <div v-if="bug.user" class="flex items-center mt-2 text-xs text-gray-600 dark:text-gray-400">
                                    <div class="w-4 h-4 bg-gray-300 rounded-full flex items-center justify-center mr-1">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ bug.user.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <span>{{ bug.user.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
