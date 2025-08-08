<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  Folder, 
  Calendar, 
  CheckSquare, 
  Bug, 
  Users, 
  Clock,
  Plus,
  Settings
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/team-leader/dashboard',
    },
    {
        title: 'Mis Projects',
        href: '/team-leader/projects',
    },
];

interface Props {
    user: any;
    projects: any[];
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const viewProject = (projectId: string) => {
    router.visit(`/projects/${projectId}`);
};

const viewSprints = (projectId: string) => {
    router.visit(`/projects/${projectId}/sprints`);
};

const viewTasks = (projectId: string) => {
    router.visit(`/projects/${projectId}/tasks`);
};

const viewBugs = (projectId: string) => {
    router.visit(`/projects/${projectId}/bugs`);
};

const getProjectStatus = (project: any) => {
    if (project.status === 'active') return { label: 'Active', color: 'bg-green-100 text-green-800' };
    if (project.status === 'completed') return { label: 'Completed', color: 'bg-blue-100 text-blue-800' };
    if (project.status === 'on_hold') return { label: 'En Pausa', color: 'bg-yellow-100 text-yellow-800' };
    return { label: 'Desconocido', color: 'bg-gray-100 text-gray-800' };
};

const getActiveSprintsCount = (project: any) => {
    return project.sprints?.filter((sprint: any) => sprint.status === 'active').length || 0;
};

const getCompletedTasksCount = (project: any) => {
    return project.tasks?.filter((task: any) => task.status === 'done').length || 0;
};

const getTotalTasksCount = (project: any) => {
    return project.tasks?.length || 0;
};

const getTotalBugsCount = (project: any) => {
    return project.bugs?.length || 0;
};
</script>

<template>
    <Head title="Mis Projects - Team Leader" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mis Projects</h1>
                    <p class="text-gray-600 dark:text-gray-400">Gestiona los proyectos donde eres Team Leader</p>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" size="sm">
                        <Settings class="h-4 w-4 mr-2" />
                        Settings
                    </Button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
                        <Folder class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ projects.length }}</div>
                        <p class="text-xs text-muted-foreground">Projects asignados</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Projects Actives</CardTitle>
                        <Folder class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ projects.filter(p => p.status === 'active').length }}</div>
                        <p class="text-xs text-muted-foreground">En desarrollo</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sprints Actives</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ projects.reduce((total, p) => total + getActiveSprintsCount(p), 0) }}</div>
                        <p class="text-xs text-muted-foreground">Sprints en curso</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tasks Completadas</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ projects.reduce((total, p) => total + getCompletedTasksCount(p), 0) }}</div>
                        <p class="text-xs text-muted-foreground">Tasks finalizadas</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="project in projects" :key="project.id" class="hover:shadow-lg transition-shadow">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="text-lg">{{ project.name }}</CardTitle>
                                <CardDescription class="mt-2">{{ project.description }}</CardDescription>
                            </div>
                            <Badge :class="getProjectStatus(project).color">
                                {{ getProjectStatus(project).label }}
                            </Badge>
                        </div>
                    </CardHeader>
                    
                    <CardContent>
                        <!-- Project Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ getActiveSprintsCount(project) }}</div>
                                <div class="text-xs text-gray-500">Sprints Actives</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ getCompletedTasksCount(project) }}/{{ getTotalTasksCount(project) }}</div>
                                <div class="text-xs text-gray-500">Tasks</div>
                            </div>
                        </div>

                        <!-- Team Members -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Team</h4>
                            <div class="flex flex-wrap gap-1">
                                <div v-for="user in project.users?.slice(0, 3)" :key="user.id" 
                                     class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                    <Users class="h-3 w-3 text-gray-500" />
                                    <span class="text-xs text-gray-600 dark:text-gray-300">{{ user.name }}</span>
                                </div>
                                <div v-if="project.users?.length > 3" 
                                     class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">+{{ project.users.length - 3 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="grid grid-cols-2 gap-2">
                            <Button @click="viewProject(project.id)" size="sm" variant="outline" class="w-full">
                                <Folder class="h-4 w-4 mr-1" />
                                Ver Project
                            </Button>
                            <Button @click="viewSprints(project.id)" size="sm" variant="outline" class="w-full">
                                <Calendar class="h-4 w-4 mr-1" />
                                Sprints
                            </Button>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Bugs: {{ getTotalBugsCount(project) }}</span>
                                <span v-if="project.created_at">Creado: {{ formatDate(project.created_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="projects.length === 0" class="text-center py-12">
                <Folder class="mx-auto h-16 w-16 text-gray-400" />
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No tienes proyectos asignados</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Contacta con un administrador para ser asignado a proyectos.</p>
            </div>
        </div>
    </AppLayout>
</template>
