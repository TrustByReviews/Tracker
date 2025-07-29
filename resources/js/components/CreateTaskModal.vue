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

interface Sprint {
    id: string,
    goal: string,
    name: string,
    start_date: string,
    end_date: string,
}

interface Project {
    id: string,
    name: string,
}

interface User {
    id: string,
    name: string,
    email: string,
}

const props = defineProps<{
    sprint?: Sprint
    project?: Project
    project_id?: string
    developers?: User[]
}>()

const open = ref(false);

const form = useForm({
    name: '',
    description: '',
    priority: 'medium',
    category: 'full stack',
    story_points: 1,
    sprint_id: '',
    project_id: '',
    estimated_hours: 1,
    assigned_user_id: '',
    estimated_start: '',
    estimated_finish: '',
});

// Helper functions
const formatToISO = (date: Date) => {
    return date.toISOString().split('T')[0];
}

const setDefaultDates = () => {
    if (props.sprint) {
        const start_sprint = new Date(props.sprint.start_date);
        const end_sprint = new Date(props.sprint.end_date);
        
        form.estimated_start = formatToISO(start_sprint);
        form.estimated_finish = formatToISO(end_sprint);
    }
}

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

// Story points options
const storyPointsOptions = [1, 2, 3, 5, 8, 13, 21];

// Watch for changes in props and update form accordingly
watch(() => props.sprint, (newSprint) => {
    if (newSprint) {
        form.sprint_id = newSprint.id;
        setDefaultDates();
    }
}, { immediate: true });

watch(() => props.project, (newProject) => {
    if (newProject) {
        form.project_id = newProject.id;
    }
}, { immediate: true });

watch(() => props.project_id, (newProjectId) => {
    if (newProjectId && !props.project) {
        form.project_id = newProjectId;
    }
}, { immediate: true });

const submit = () => {
    console.log('Submitting task:', form.data())
    
    // Validar campos requeridos
    if (!form.name.trim()) {
        alert('Task name is required');
        return;
    }
    
    if (!form.description.trim()) {
        alert('Task description is required');
        return;
    }
    
    if (!form.sprint_id) {
        alert('Please select a sprint');
        return;
    }
    
    if (!form.project_id) {
        alert('Please select a project');
        return;
    }
    
    form.post('/tasks', {
        onSuccess: () => {
            form.reset();
            open.value = false;
            // Redirect to the appropriate page
            if (props.sprint) {
                router.visit(`/projects/${props.project_id}/sprints/${props.sprint.id}`);
            } else {
                router.visit('/tasks');
            }
        },
        onError: (errors) => {
            console.error('Task creation errors:', errors);
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
    form.reset();
    
    // Set default values
    form.priority = 'medium';
    form.category = 'full stack';
    form.story_points = 1;
    form.estimated_hours = 1;
    
    // Set sprint and project if available
    if (props.sprint) {
        form.sprint_id = props.sprint.id;
    }
    if (props.project) {
        form.project_id = props.project.id;
    } else if (props.project_id) {
        form.project_id = props.project_id;
    }
    
    // Set default dates
    setDefaultDates();
}
</script>

<template>
    <div @keydown="handleKeydown">
        <Button @click="openModal" class="border-blue-500 text-white bg-blue-500 hover:bg-blue-600 transition-colors">
            Create Task
        </Button>
        
        <Dialog :open="open" :modal="true">
            <DialogContent class="max-w-2xl p-6 bg-white rounded-lg shadow-lg">
                <DialogTitle class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Create New Task
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

                    <!-- Priority and Category -->
                    <div class="grid grid-cols-2 gap-4">
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

                    <!-- Project and Sprint Info -->
                    <div v-if="props.project || props.sprint" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="text-sm text-gray-600 space-y-2">
                            <div v-if="props.project" class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <strong>Project:</strong> {{ props.project.name }}
                            </div>
                            <div v-if="props.sprint" class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6m-6 0V9a4 4 0 00-4-4H5a4 4 0 00-4 4v6a4 4 0 004 4h2" />
                                </svg>
                                <strong>Sprint:</strong> {{ props.sprint.name }}
                            </div>
                        </div>
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
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Task</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
