<script setup lang="ts">
import Button from './ui/button/Button.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Dialog from './ui/dialog/Dialog.vue';
import DialogContent from './ui/dialog/DialogContent.vue';
import DialogTitle from './ui/dialog/DialogTitle.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface User {
    id: string,
    name: string,
    email: string,
}

interface Task {
    id: string,
    name: string,
    description: string,
    estimated_start: string | null,
    estimated_finish: string | null,
    status: string,
    priority: string,
    category: string,
    story_points: number,
    estimated_hours: number,
    user_id: string | null,
    user?: User,
}

const props = defineProps<{
    task: Task,
    developers?: User[]
}>()

const open = ref(false);

const form = useForm({
    name: '',
    description: '',
    priority: '',
    category: '',
    story_points: 1,
    estimated_hours: 1,
    assigned_user_id: '',
    estimated_start: '',
    estimated_finish: '',
    status: '',
});

// Priority options
const priorityOptions = [
    { value: 'low', label: 'Low', color: 'text-yellow-600' },
    { value: 'medium', label: 'Medium', color: 'text-orange-600' },
    { value: 'high', label: 'High', color: 'text-red-600' },
];

// Category options
const categoryOptions = [
    { value: 'frontend', label: 'Frontend' },
    { value: 'backend', label: 'Backend' },
    { value: 'full stack', label: 'Full Stack' },
    { value: 'design', label: 'Design' },
    { value: 'deployment', label: 'Deployment' },
    { value: 'fixes', label: 'Fixes' },
    { value: 'testing', label: 'Testing' },
    { value: 'documentation', label: 'Documentation' },
];

// Status options
const statusOptions = [
    { value: 'to do', label: 'To Do', color: 'text-yellow-600' },
    { value: 'in progress', label: 'In Progress', color: 'text-blue-600' },
    { value: 'ready for test', label: 'Ready for Test', color: 'text-orange-600' },
    { value: 'done', label: 'Done', color: 'text-green-600' },
    { value: 'rejected', label: 'Rejected', color: 'text-red-600' },
];

// Story points options
const storyPointsOptions = [1, 2, 3, 5, 8, 13, 21];

// Watch for changes in props and update form accordingly
watch(() => props.task, (newTask) => {
    if (newTask) {
        form.name = newTask.name;
        form.description = newTask.description;
        form.priority = newTask.priority;
        form.category = newTask.category;
        form.story_points = newTask.story_points;
        form.estimated_hours = newTask.estimated_hours;
        form.assigned_user_id = newTask.user_id || '';
        form.estimated_start = newTask.estimated_start || '';
        form.estimated_finish = newTask.estimated_finish || '';
        form.status = newTask.status;
    }
}, { immediate: true });

const submit = () => {
    console.log('Updating task:', form.data())
    
    // Validar campos requeridos
    if (!form.name.trim()) {
        alert('Task name is required');
        return;
    }
    
    if (!form.description.trim()) {
        alert('Task description is required');
        return;
    }
    
    form.put(`/tasks/${props.task.id}`, {
        onSuccess: () => {
            form.reset();
            open.value = false;
            // Redirect to the task detail page
            router.visit(`/tasks/${props.task.id}`);
        },
        onError: (errors) => {
            console.error('Task update errors:', errors);
            // Mostrar errores específicos
            Object.keys(errors).forEach(field => {
                alert(`${field}: ${errors[field]}`);
            });
        }
    });
}

const closeModal = () => {
    if (form.processing) {
        return; // Don't close if form is being processed
    }
    
    // Check if there's any data in the form
    const hasData = form.name || form.description || form.story_points > 1 || form.estimated_hours > 1;
    
    if (hasData) {
        // Ask for confirmation before closing
        if (confirm('Are you sure you want to close? Any unsaved changes will be lost.')) {
            open.value = false;
            form.reset();
        }
    } else {
        // No data, close directly
        open.value = false;
        form.reset();
    }
}

// Prevent closing with Escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && open.value) {
        event.preventDefault();
        closeModal();
    }
}

// Reset form when modal opens
const openModal = () => {
    open.value = true;
    
    // Set form values from task
    form.name = props.task.name;
    form.description = props.task.description;
    form.priority = props.task.priority;
    form.category = props.task.category;
    form.story_points = props.task.story_points;
    form.estimated_hours = props.task.estimated_hours;
    form.assigned_user_id = props.task.user_id || '';
    form.estimated_start = props.task.estimated_start || '';
    form.estimated_finish = props.task.estimated_finish || '';
    form.status = props.task.status;
}
</script>

<template>
    <div @keydown="handleKeydown">
        <Button @click="openModal" class="border-blue-500 text-white bg-blue-500 hover:bg-blue-600 transition-colors">
            Edit Task
        </Button>
        
        <Dialog :open="open" :modal="true">
            <DialogContent class="max-w-2xl p-6 bg-white rounded-lg shadow-lg">
                <DialogTitle class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Task
                </DialogTitle>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Task Name -->
                    <div>
                        <Label for="name" class="block text-sm font-medium text-gray-700 mb-2">Task Name *</Label>
                        <Input 
                            id="name"
                            v-model="form.name" 
                            placeholder="e.g., UI Design, Component Library, API Integration"
                            class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500" 
                            required
                        />
                    </div>

                    <!-- Task Description -->
                    <div>
                        <Label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</Label>
                        <Textarea 
                            id="description"
                            v-model="form.description" 
                            placeholder="Describe the task in detail, including requirements and acceptance criteria..."
                            rows="3"
                            class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500" 
                            required
                        />
                    </div>

                    <!-- Status, Priority and Category -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <Label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                        <span :class="option.color">{{ option.label }}</span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</Label>
                            <Select v-model="form.priority">
                                <SelectTrigger class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500">
                                    <SelectValue placeholder="Select priority" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in priorityOptions" :key="option.value" :value="option.value">
                                        <span :class="option.color">{{ option.label }}</span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</Label>
                            <Select v-model="form.category">
                                <SelectTrigger class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500">
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in categoryOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Story Points and Estimated Hours -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="story_points" class="block text-sm font-medium text-gray-700 mb-2">Story Points</Label>
                            <Select v-model="form.story_points">
                                <SelectTrigger class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500">
                                    <SelectValue placeholder="Select story points" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="points in storyPointsOptions" :key="points" :value="points">
                                        {{ points }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-2">Estimated Hours</Label>
                            <Input
                                id="estimated_hours"
                                v-model="form.estimated_hours"
                                type="number"
                                min="1"
                                max="40"
                                class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Estimated Dates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="estimated_start" class="block text-sm font-medium text-gray-700 mb-2">Estimated Start Date</Label>
                            <Input
                                id="estimated_start"
                                v-model="form.estimated_start"
                                type="date"
                                class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <Label for="estimated_finish" class="block text-sm font-medium text-gray-700 mb-2">Estimated Finish Date</Label>
                            <Input
                                id="estimated_finish"
                                v-model="form.estimated_finish"
                                type="date"
                                :min="form.estimated_start"
                                class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Assign to Developer -->
                    <div v-if="props.developers && props.developers.length > 0">
                        <Label for="assigned_user_id" class="block text-sm font-medium text-gray-700 mb-2">Assign to Developer</Label>
                        <Select v-model="form.assigned_user_id">
                            <SelectTrigger class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500">
                                <SelectValue placeholder="Select a developer..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Unassigned</SelectItem>
                                <SelectItem v-for="developer in props.developers" :key="developer.id" :value="developer.id">
                                    {{ developer.name }} ({{ developer.email }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <Button 
                            type="button" 
                            variant="secondary" 
                            @click="closeModal" 
                            class="bg-gray-200 text-gray-800 hover:bg-gray-300 transition-colors"
                        >
                            Cancel
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 transition-colors"
                        >
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Task</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
