<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  Bell, 
  CheckSquare, 
  Bug, 
  Clock, 
  CheckCircle,
  AlertCircle,
  XCircle,
  Eye
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/team-leader/dashboard',
    },
    {
        title: 'Notifications',
        href: '/team-leader/notifications',
    },
];

interface Props {
    user: any;
    notifications: any[];
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getNotificationIcon = (type: string) => {
    switch (type) {
        case 'task_approved_by_qa':
            return CheckSquare;
        case 'bug_approved_by_qa':
            return Bug;
        case 'task_completed':
            return CheckCircle;
        case 'bug_resolved':
            return CheckCircle;
        default:
            return Bell;
    }
};

const getNotificationColor = (type: string) => {
    switch (type) {
        case 'task_approved_by_qa':
        case 'bug_approved_by_qa':
            return 'bg-green-100 text-green-800';
        case 'task_completed':
        case 'bug_resolved':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getNotificationTitle = (type: string) => {
    switch (type) {
        case 'task_approved_by_qa':
            return 'Task Approved by QA';
        case 'bug_approved_by_qa':
            return 'Bug Approved by QA';
        case 'task_completed':
            return 'Task Completed';
        case 'bug_resolved':
            return 'Bug Resolved';
        default:
            return 'Notification';
    }
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

const markAsRead = (notificationId: string) => {
    // Implement logic to mark as read
    console.log('Marking notification as read:', notificationId);
};
</script>

<template>
    <Head title="Notifications - Team Leader" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
                    <p class="text-gray-600 dark:text-gray-400">Stay up to date with your team activities</p>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" size="sm">
                        Mark all as read
                    </Button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Notifications</CardTitle>
                        <Bell class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ notifications.length }}</div>
                        <p class="text-xs text-muted-foreground">Notifications received</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Unread</CardTitle>
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ notifications.filter(n => !n.read_at).length }}</div>
                        <p class="text-xs text-muted-foreground">Pending review</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved Tasks</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ notifications.filter(n => n.type === 'task_approved_by_qa').length }}</div>
                        <p class="text-xs text-muted-foreground">By QA</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved Bugs</CardTitle>
                        <Bug class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ notifications.filter(n => n.type === 'bug_approved_by_qa').length }}</div>
                        <p class="text-xs text-muted-foreground">By QA</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Notifications List -->
            <div class="space-y-4">
                <div v-if="notifications.length === 0" class="text-center py-12">
                    <Bell class="mx-auto h-16 w-16 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No notifications</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">You have no pending notifications.</p>
                </div>

                <Card v-for="notification in notifications" :key="notification.id" 
                      :class="{ 'border-blue-200 bg-blue-50': !notification.read_at }"
                      class="hover:shadow-md transition-shadow">
                    <CardContent class="p-4">
                        <div class="flex items-start space-x-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <component :is="getNotificationIcon(notification.type)" class="h-5 w-5 text-gray-600" />
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ getNotificationTitle(notification.type) }}
                                            </h4>
                                            <Badge :class="getNotificationColor(notification.type)" class="text-xs">
                                                {{ notification.type }}
                                            </Badge>
                                            <Badge v-if="!notification.read_at" variant="outline" class="text-xs bg-blue-100 text-blue-800">
                                                New
                                            </Badge>
                                        </div>
                                        
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            {{ notification.message || 'System notification' }}
                                        </p>

                                        <!-- Task/Bug Info -->
                                        <div v-if="notification.data" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 mb-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ notification.data.title || notification.data.name }}
                                                    </h5>
                                                    <p class="text-xs text-gray-500">
                                                        Project: {{ notification.data.project?.name }}
                                                        <span v-if="notification.data.sprint?.name">
                                                            - Sprint: {{ notification.data.sprint.name }}
                                                        </span>
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        Developer: {{ notification.data.user?.name }}
                                                    </p>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ formatDate(notification.created_at) }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center space-x-2">
                                            <Button 
                                                v-if="notification.data?.id && (notification.type === 'task_approved_by_qa' || notification.type === 'bug_approved_by_qa')"
                                                @click="notification.type.includes('task') ? reviewTask(notification.data.id) : reviewBug(notification.data.id)"
                                                size="sm" 
                                                variant="outline"
                                            >
                                                <Eye class="h-4 w-4 mr-1" />
                                                Review
                                            </Button>
                                            <Button 
                                                v-if="notification.data?.id"
                                                @click="notification.type.includes('task') ? viewTask(notification.data.id) : viewBug(notification.data.id)"
                                                size="sm" 
                                                variant="outline"
                                            >
                                                View Details
                                            </Button>
                                            <Button 
                                                v-if="!notification.read_at"
                                                @click="markAsRead(notification.id)"
                                                size="sm" 
                                                variant="ghost"
                                            >
                                                Mark as read
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Load More -->
            <div v-if="notifications.length > 0" class="text-center">
                <Button variant="outline" size="sm">
                    Load more notifications
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
