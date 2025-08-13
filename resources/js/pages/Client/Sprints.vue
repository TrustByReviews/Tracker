<template>
    <AppLayout title="Sprints">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sprints
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ error }}
                </div>

                <!-- Content -->
                <div v-else>
                    <!-- Header Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-blue-100">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500">Total Sprints</p>
                                        <p class="text-2xl font-semibold text-gray-900">{{ stats.totalSprints }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-green-100">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500">Completados</p>
                                        <p class="text-2xl font-semibold text-gray-900">{{ stats.completedSprints }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-yellow-100">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500">En Progreso</p>
                                        <p class="text-2xl font-semibold text-gray-900">{{ stats.inProgressSprints }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-purple-100">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500">Promedio Progreso</p>
                                        <p class="text-2xl font-semibold text-gray-900">{{ stats.averageProgress }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sprints List -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Sprints de Proyectos</h3>
                            
                            <div v-if="sprints.length === 0" class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay sprints</h3>
                                <p class="mt-1 text-sm text-gray-500">No se encontraron sprints para tus proyectos.</p>
                            </div>

                            <div v-else class="space-y-6">
                                <div v-for="sprint in sprints" :key="sprint.id" class="border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">{{ sprint.name }}</h4>
                                            <p class="text-sm text-gray-500">{{ sprint.project.name }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span v-if="sprint.is_current" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Actual
                                            </span>
                                            <span :class="getStatusClass(sprint.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ getStatusLabel(sprint.status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div v-if="sprint.description" class="mb-4">
                                        <p class="text-sm text-gray-600">{{ sprint.description }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha Inicio</p>
                                            <p class="text-sm text-gray-900">{{ formatDate(sprint.start_date) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha Fin</p>
                                            <p class="text-sm text-gray-900">{{ formatDate(sprint.end_date) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Duración</p>
                                            <p class="text-sm text-gray-900">{{ calculateDuration(sprint.start_date, sprint.end_date) }}</p>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">Progreso</span>
                                            <span class="text-sm font-medium text-gray-700">{{ sprint.statistics.progress }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                                :style="{ width: sprint.statistics.progress + '%' }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Task Statistics -->
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-gray-900">{{ sprint.statistics.total_tasks }}</p>
                                            <p class="text-xs text-gray-500">Total Tareas</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-green-600">{{ sprint.statistics.completed_tasks }}</p>
                                            <p class="text-xs text-gray-500">Completadas</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-yellow-600">{{ sprint.statistics.in_progress_tasks }}</p>
                                            <p class="text-xs text-gray-500">En Progreso</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-gray-600">{{ sprint.statistics.pending_tasks }}</p>
                                            <p class="text-xs text-gray-500">Pendientes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
    name: 'ClientSprints',
    components: {
        AppLayout
    },
    setup() {
        const sprints = ref([])
        const loading = ref(true)
        const error = ref(null)

        const stats = computed(() => {
            if (sprints.value.length === 0) {
                return {
                    totalSprints: 0,
                    completedSprints: 0,
                    inProgressSprints: 0,
                    averageProgress: 0
                }
            }

            const totalSprints = sprints.value.length
            const completedSprints = sprints.value.filter(s => s.status === 'completed').length
            const inProgressSprints = sprints.value.filter(s => s.is_current).length
            const averageProgress = Math.round(
                sprints.value.reduce((sum, s) => sum + s.statistics.progress, 0) / totalSprints
            )

            return {
                totalSprints,
                completedSprints,
                inProgressSprints,
                averageProgress
            }
        })

        const fetchSprints = async () => {
            try {
                loading.value = true
                error.value = null
                
                const response = await fetch('/client/sprints/api')
                const result = await response.json()
                
                if (result.success) {
                    sprints.value = result.data
                } else {
                    error.value = 'Error al cargar los sprints'
                }
            } catch (err) {
                error.value = 'Error de conexión'
                console.error('Error fetching sprints:', err)
            } finally {
                loading.value = false
            }
        }

        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            })
        }

        const calculateDuration = (startDate, endDate) => {
            const start = new Date(startDate)
            const end = new Date(endDate)
            const diffTime = Math.abs(end - start)
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
            return `${diffDays} días`
        }

        const getStatusClass = (status) => {
            const classes = {
                'active': 'bg-green-100 text-green-800',
                'completed': 'bg-blue-100 text-blue-800',
                'pending': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800'
            }
            return classes[status] || 'bg-gray-100 text-gray-800'
        }

        const getStatusLabel = (status) => {
            const labels = {
                'active': 'Activo',
                'completed': 'Completado',
                'pending': 'Pendiente',
                'cancelled': 'Cancelado'
            }
            return labels[status] || status
        }

        onMounted(() => {
            fetchSprints()
        })

        return {
            sprints,
            loading,
            error,
            stats,
            formatDate,
            calculateDuration,
            getStatusClass,
            getStatusLabel
        }
    }
}
</script>
