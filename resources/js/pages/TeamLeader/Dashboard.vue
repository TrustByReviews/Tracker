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
  CheckCircle, 
  Clock, 
  Users, 
  TrendingUp,
  AlertCircle,
  Bell
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/team-leader/dashboard',
    },
];

interface Props {
    user: any;
    projects: any[];
    activeSprints: any[];
    pendingTasks: any[];
    pendingBugs: any[];
    qaApprovedTasks: any[];
    qaApprovedBugs: any[];
    stats: any;
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

const viewSprint = (sprintId: string) => {
    router.visit(`/sprints/${sprintId}`);
};

const viewTask = (taskId: string) => {
    router.visit(`/tasks/${taskId}`);
};

const viewBug = (bugId: string) => {
    router.visit(`/bugs/${bugId}`);
};

const reviewTask = (taskId: string) => {
    router.visit(`/team-leader/review/tasks`);
};

const reviewBug = (bugId: string) => {
    router.visit(`/team-leader/review/bugs`);
};
</script>

<template>
    <Head title="Team Leader Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
                <h1 class="text-2xl font-bold mb-2">Team Leader Dashboard</h1>
                <p class="text-blue-100">Welcome, {{ user.name }}. Here you have an overview of your projects and pending tasks.</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Projects</CardTitle>
                        <Folder class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeProjects }}</div>
                        <p class="text-xs text-muted-foreground">Projects in progress</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Sprints</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeSprints }}</div>
                        <p class="text-xs text-muted-foreground">Sprints in progress</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Tasks</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pendingTasks }}</div>
                        <p class="text-xs text-muted-foreground">Waiting for review</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Bugs</CardTitle>
                        <Bug class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pendingBugs }}</div>
                        <p class="text-xs text-muted-foreground">Waiting for review</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Content Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Active Projects -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Folder class="h-5 w-5 mr-2" />
                            My Projects
                        </CardTitle>
                        <CardDescription>Projects where you are Team Leader</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="projects.length === 0" class="text-center py-8">
                            <Folder class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">You have no assigned projects</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="project in projects.slice(0, 5)" :key="project.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ project.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ project.description }}</p>
                                        <div class="flex items-center mt-2 space-x-4">
                                            <span class="text-xs text-gray-500">
                                                <Users class="inline h-3 w-3 mr-1" />
                                                {{ project.users?.length || 0 }} members
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <CheckSquare class="inline h-3 w-3 mr-1" />
                                                {{ project.tasks?.length || 0 }} tasks
                                            </span>
                                        </div>
                                    </div>
                                    <Button @click="viewProject(project.id)" size="sm" variant="outline">
                                        View
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Active Sprints -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Calendar class="h-5 w-5 mr-2" />
                            Active Sprints
                        </CardTitle>
                        <CardDescription>Sprints in progress from your projects</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activeSprints.length === 0" class="text-center py-8">
                            <Calendar class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">No active sprints</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="sprint in activeSprints.slice(0, 5)" :key="sprint.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ sprint.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ sprint.project?.name }}</p>
                                        <div class="flex items-center mt-2 space-x-4">
                                            <span class="text-xs text-gray-500">
                                                <Clock class="inline h-3 w-3 mr-1" />
                                                {{ formatDate(sprint.start_date) }} - {{ formatDate(sprint.end_date) }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <CheckSquare class="inline h-3 w-3 mr-1" />
                                                {{ sprint.tasks?.length || 0 }} tasks
                                            </span>
                                        </div>
                                    </div>
                                    <Button @click="viewSprint(sprint.id)" size="sm" variant="outline">
                                        View
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Tasks Pending Review -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <AlertCircle class="h-5 w-5 mr-2 text-orange-500" />
                            Pending Tasks
                        </CardTitle>
                        <CardDescription>Tasks approved by QA that require your review</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="pendingTasks.length === 0" class="text-center py-8">
                            <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">No tasks pending review</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="task in pendingTasks.slice(0, 5)" :key="task.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ task.name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ task.project?.name }} - {{ task.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ task.user?.name }}</p>
                                        <div class="flex items-center mt-2">
                                            <Badge variant="outline" class="text-xs">
                                                <CheckCircle class="h-3 w-3 mr-1" />
                                                Approved by QA
                                            </Badge>
                                        </div>
                                    </div>
                                    <Button @click="reviewTask(task.id)" size="sm" variant="outline">
                                        Review
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bugs Pending Review -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Bug class="h-5 w-5 mr-2 text-red-500" />
                            Pending Bugs
                        </CardTitle>
                        <CardDescription>Bugs approved by QA that require your review</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="pendingBugs.length === 0" class="text-center py-8">
                            <Bug class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">No bugs pending review</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="bug in pendingBugs.slice(0, 5)" :key="bug.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ bug.title }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ bug.project?.name }} - {{ bug.sprint?.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Developer: {{ bug.user?.name }}</p>
                                        <div class="flex items-center mt-2">
                                            <Badge variant="outline" class="text-xs">
                                                <CheckCircle class="h-3 w-3 mr-1" />
                                                Approved by QA
                                            </Badge>
                                        </div>
                                    </div>
                                    <Button @click="reviewBug(bug.id)" size="sm" variant="outline">
                                        Review
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <TrendingUp class="h-5 w-5 mr-2" />
                        Quick Actions
                    </CardTitle>
                    <CardDescription>Direct access to the most used functions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Button @click="router.visit('/team-leader/review/tasks')" variant="outline" class="h-20 flex-col">
                            <CheckSquare class="h-6 w-6 mb-2" />
                            <span class="text-sm">Review Tasks</span>
                        </Button>
                        <Button @click="router.visit('/team-leader/review/bugs')" variant="outline" class="h-20 flex-col">
                            <Bug class="h-6 w-6 mb-2" />
                            <span class="text-sm">Review Bugs</span>
                        </Button>
                        <Button @click="router.visit('/team-leader/projects')" variant="outline" class="h-20 flex-col">
                            <Folder class="h-6 w-6 mb-2" />
                            <span class="text-sm">My Projects</span>
                        </Button>
                        <Button @click="router.visit('/team-leader/notifications')" variant="outline" class="h-20 flex-col">
                            <Bell class="h-6 w-6 mb-2" />
                            <span class="text-sm">Notifications</span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 