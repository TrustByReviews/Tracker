<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { CheckCircle, XCircle, Clock, AlertCircle, Eye } from 'lucide-vue-next';
import axios from 'axios';

interface User {
    id: string;
    name: string;
    email: string;
}

interface Project {
    id: string;
    name: string;
    description: string;
}

interface Sprint {
    id: string;
    name: string;
    goal: string;
    start_date: string;
    end_date: string;
}

interface Task {
    id: string;
    name: string;
    description: string;
    status: string;
    priority: string;
    category: string;
    story_points: number;
    estimated_hours: number;
    user_id: string;
    user?: User;
    sprint?: Sprint;
    project?: Project;
    qa_reviewed_by?: string;
    qa_reviewed_at?: string;
    qa_notes?: string;
    qa_completed_at?: string;
    qa_reviewed_by_user?: User;
}

const tasks = ref<Task[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const stats = ref({
    ready_for_review: 0,
    approved: 0,
    changes_requested: 0
});

// Modal states
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const selectedTask = ref<Task | null>(null);
const approveNotes = ref('');
const rejectNotes = ref('');

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'low': return 'bg-green-100 text-green-800';
        case 'medium': return 'bg-yellow-100 text-yellow-800';
        case 'high': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getCategoryColor = (category: string) => {
    switch (category) {
        case 'frontend': return 'bg-blue-100 text-blue-800';
        case 'backend': return 'bg-purple-100 text-purple-800';
        case 'full stack': return 'bg-indigo-100 text-indigo-800';
        case 'design': return 'bg-pink-100 text-pink-800';
        case 'deployment': return 'bg-orange-100 text-orange-800';
        case 'fixes': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const loadTasks = async () => {
    try {
        loading.value = true;
        const response = await axios.get(route('team-leader.review.tasks'));
        tasks.value = response.data.tasks;
    } catch (err: any) {
        error.value = err.response?.data?.error || 'Failed to load tasks';
    } finally {
        loading.value = false;
    }
};

const loadStats = async () => {
    try {
        const response = await axios.get(route('team-leader.review.stats'));
        stats.value = response.data.stats.tasks;
    } catch (err: any) {
        console.error('Failed to load stats:', err);
    }
};

const openApproveModal = (task: Task) => {
    selectedTask.value = task;
    approveNotes.value = '';
    showApproveModal.value = true;
};

const openRejectModal = (task: Task) => {
    selectedTask.value = task;
    rejectNotes.value = '';
    showRejectModal.value = true;
};

const approveTask = async () => {
    if (!selectedTask.value) return;
    
    try {
        const response = await axios.post(route('team-leader.review.tasks.approve', selectedTask.value.id), {
            notes: approveNotes.value
        });
        
        // Remove the task from the list
        tasks.value = tasks.value.filter(t => t.id !== selectedTask.value!.id);
        
        showApproveModal.value = false;
        selectedTask.value = null;
        approveNotes.value = '';
        
        // Reload stats
        await loadStats();
        
        // Show success message
        alert('Task approved successfully!');
    } catch (err: any) {
        alert(err.response?.data?.error || 'Failed to approve task');
    }
};

const requestChanges = async () => {
    if (!selectedTask.value || !rejectNotes.value.trim()) {
        alert('Please provide a reason for requesting changes');
        return;
    }
    
    try {
        const response = await axios.post(route('team-leader.review.tasks.request-changes', selectedTask.value.id), {
            notes: rejectNotes.value
        });
        
        // Remove the task from the list
        tasks.value = tasks.value.filter(t => t.id !== selectedTask.value!.id);
        
        showRejectModal.value = false;
        selectedTask.value = null;
        rejectNotes.value = '';
        
        // Reload stats
        await loadStats();
        
        // Show success message
        alert('Changes requested successfully!');
    } catch (err: any) {
        alert(err.response?.data?.error || 'Failed to request changes');
    }
};

const viewTask = (task: Task) => {
    // Navigate to task detail page
    window.open(route('tasks.show', task.id), '_blank');
};

onMounted(() => {
    loadTasks();
    loadStats();
});
</script>

<template>
    <AppLayout>
        <Head title="Review Tasks" />
        
        <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Review Tasks Completed by QA</h1>
            <p class="text-gray-600">Review and approve tasks that have been completed by QA testing</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Ready for Review</CardTitle>
                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ stats.ready_for_review }}</div>
                    <p class="text-xs text-muted-foreground">Tasks waiting for your review</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Approved</CardTitle>
                    <CheckCircle class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ stats.approved }}</div>
                    <p class="text-xs text-muted-foreground">Tasks you've approved</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Changes Requested</CardTitle>
                    <AlertCircle class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-orange-600">{{ stats.changes_requested }}</div>
                    <p class="text-xs text-muted-foreground">Tasks with requested changes</p>
                </CardContent>
            </Card>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12">
            <div class="text-red-600 mb-4">{{ error }}</div>
            <Button @click="loadTasks" variant="outline">Retry</Button>
        </div>

        <!-- Tasks List -->
        <div v-else-if="tasks.length > 0" class="space-y-4">
            <div v-for="task in tasks" :key="task.id" class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ task.name }}</h3>
                            <Badge :class="getPriorityColor(task.priority)" class="text-xs">
                                {{ task.priority }}
                            </Badge>
                            <Badge :class="getCategoryColor(task.category)" class="text-xs">
                                {{ task.category }}
                            </Badge>
                        </div>
                        
                        <p class="text-gray-600 mb-4">{{ task.description }}</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-500">
                            <div>
                                <span class="font-medium">Developer:</span>
                                <span class="ml-1">{{ task.user?.name || 'Unassigned' }}</span>
                            </div>
                            <div>
                                <span class="font-medium">Project:</span>
                                <span class="ml-1">{{ task.project?.name || 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="font-medium">Sprint:</span>
                                <span class="ml-1">{{ task.sprint?.name || 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="font-medium">Story Points:</span>
                                <span class="ml-1">{{ task.story_points }}</span>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <div class="text-sm text-blue-800">
                                <div class="font-medium mb-1">QA Review Information:</div>
                                <div>Completed by: {{ task.qa_reviewed_by_user?.name || 'Unknown' }}</div>
                                <div>Completed at: {{ task.qa_completed_at ? formatDate(task.qa_completed_at) : 'Unknown' }}</div>
                                <div v-if="task.qa_notes" class="mt-2">
                                    <span class="font-medium">QA Notes:</span>
                                    <span class="ml-1">{{ task.qa_notes }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 ml-4">
                        <Button @click="viewTask(task)" variant="outline" size="sm">
                            <Eye class="h-4 w-4 mr-1" />
                            View
                        </Button>
                        <Button @click="openApproveModal(task)" variant="default" size="sm" class="bg-green-600 hover:bg-green-700">
                            <CheckCircle class="h-4 w-4 mr-1" />
                            Approve
                        </Button>
                        <Button @click="openRejectModal(task)" variant="outline" size="sm" class="border-orange-300 text-orange-600 hover:bg-orange-50">
                            <XCircle class="h-4 w-4 mr-1" />
                            Request Changes
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <div class="text-gray-500 mb-4">No tasks ready for review</div>
            <p class="text-sm text-gray-400">All tasks have been reviewed or are still in progress</p>
        </div>
    </div>
    </AppLayout>

    <!-- Approve Modal -->
    <Dialog :open="showApproveModal" @update:open="showApproveModal = false">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Approve Task</DialogTitle>
                <DialogDescription>
                    Are you sure you want to approve this task? You can add optional notes below.
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-4">
                <div v-if="selectedTask">
                    <Label class="text-sm font-medium">Task: {{ selectedTask.name }}</Label>
                </div>
                <div>
                    <Label for="approve-notes">Notes (Optional)</Label>
                    <Textarea
                        id="approve-notes"
                        v-model="approveNotes"
                        placeholder="Add any notes or comments about this approval..."
                        rows="3"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showApproveModal = false">Cancel</Button>
                <Button @click="approveTask" class="bg-green-600 hover:bg-green-700">Approve Task</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Request Changes Modal -->
    <Dialog :open="showRejectModal" @update:open="showRejectModal = false">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Request Changes</DialogTitle>
                <DialogDescription>
                    Please provide a detailed explanation of what changes are needed.
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-4">
                <div v-if="selectedTask">
                    <Label class="text-sm font-medium">Task: {{ selectedTask.name }}</Label>
                </div>
                <div>
                    <Label for="reject-notes">Reason for Changes *</Label>
                    <Textarea
                        id="reject-notes"
                        v-model="rejectNotes"
                        placeholder="Explain what changes are needed and why..."
                        rows="4"
                        required
                    />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showRejectModal = false">Cancel</Button>
                <Button @click="requestChanges" class="bg-orange-600 hover:bg-orange-700">Request Changes</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
