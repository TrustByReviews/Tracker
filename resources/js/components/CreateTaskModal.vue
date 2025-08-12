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
import InputError from './InputError.vue';

/**
 * CreateTaskModal Component
 * 
 * This component provides a comprehensive modal interface for creating new tasks.
 * It includes form validation, file upload functionality, and dynamic field management
 * based on task type and project context.
 * 
 * Features:
 * - Task creation with detailed form fields
 * - File attachment support with drag & drop
 * - Dynamic field validation
 * - Priority and category selection
 * - Story points and complexity level assignment
 * - Developer assignment capabilities
 * 
 * @component
 * @example
 * <CreateTaskModal 
 *   :sprint="currentSprint"
 *   :project="currentProject"
 *   :developers="availableDevelopers"
 * />
 */

/**
 * Sprint interface for task creation context
 */
interface Sprint {
    id: string,           // Unique sprint identifier
    goal: string,         // Sprint goal description
    name: string,         // Sprint name
    start_date: string,   // Sprint start date
    end_date: string,     // Sprint end date
}

/**
 * Project interface for task creation context
 */
interface Project {
    id: string,           // Unique project identifier
    name: string,         // Project name
}

/**
 * User interface for developer assignment
 */
interface User {
    id: string,           // Unique user identifier
    name: string,         // User display name
    email: string,        // User email address
    roles?: Array<{       // User roles (optional)
        name: string,     // Role name
        value: string     // Role value
    }>
}

/**
 * Component props for task creation modal
 */
const props = defineProps<{
    sprint?: Sprint       // Current sprint context (optional)
    project?: Project     // Current project context (optional)
    project_id?: string   // Project ID for task assignment (optional)
    developers?: User[]   // Available developers for assignment (optional)
}>()

// Modal state management
const open = ref(false);
const uploadedFiles = ref<File[]>([]);
const dragOver = ref(false);

/**
 * Task creation form using Inertia.js useForm
 * Manages all form fields and validation state
 */
const form = useForm({
    // Common fields
    name: 'Implementar sistema de autenticación JWT',                    // Task name/title
    description: 'Desarrollar sistema completo de autenticación usando JWT tokens para la aplicación web',             // Short description
    long_description: 'Se requiere implementar un sistema robusto de autenticación que incluya login, registro, recuperación de contraseña y validación de tokens JWT. El sistema debe ser seguro, escalable y fácil de mantener. Incluir documentación técnica y casos de prueba.',        // Detailed description
    priority: 'high',          // Task priority level
    sprint_id: '',              // Associated sprint ID
    project_id: '',             // Associated project ID
    estimated_hours: 8,         // Estimated hours for completion
    estimated_minutes: 30,       // Estimated minutes for completion
    assigned_user_id: '',       // Assigned developer ID (se mantiene vacío)
    attachments: [] as any[],   // File attachments
    tags: 'autenticacion,jwt,seguridad,backend',                   // Task tags/labels
    
    // Task-specific fields
    category: 'backend',     // Task category/type
    story_points: 8,           // Agile story points
    acceptance_criteria: '1. Usuario puede registrarse con email y contraseña\n2. Usuario puede iniciar sesión y recibir token JWT\n3. Token JWT se valida en cada request\n4. Usuario puede recuperar contraseña\n5. Sistema maneja tokens expirados\n6. Documentación técnica completa',    // Acceptance criteria
    technical_notes: 'Usar bcrypt para hash de contraseñas. Implementar refresh tokens. Considerar rate limiting para endpoints de autenticación. Usar middleware de validación de JWT. Implementar blacklist de tokens para logout.',        // Technical implementation notes
    complexity_level: 'high', // Task complexity level
    task_type: 'feature',       // Type of task
});

/**
 * Priority options for task assignment
 * Each option includes value, display label, and color styling
 */
const priorityOptions = [
    { value: 'low', label: 'Low', color: 'text-green-600' },
    { value: 'medium', label: 'Medium', color: 'text-yellow-600' },
    { value: 'high', label: 'High', color: 'text-orange-600' },
    { value: 'critical', label: 'Critical', color: 'text-red-600' },
];

/**
 * Category options for task classification
 * Defines different types of development work
 */
const categoryOptions = [
    { value: 'frontend', label: 'Frontend' },
    { value: 'backend', label: 'Backend' },
    { value: 'full stack', label: 'Full Stack' },
    { value: 'design', label: 'Design' },
    { value: 'deployment', label: 'Deployment' },
    { value: 'fixes', label: 'Fixes' },
    { value: 'testing', label: 'Testing' },
    { value: 'documentation', label: 'Documentation' },
    { value: 'database', label: 'Database' },
    { value: 'api', label: 'API' },
    { value: 'security', label: 'Security' },
    { value: 'performance', label: 'Performance' },
];

/**
 * Story points options for Agile estimation
 * Uses Fibonacci sequence for story point values
 */
const storyPointsOptions = [1, 2, 3, 5, 8, 13, 21];

/**
 * Complexity levels for task assessment
 * Helps in resource allocation and timeline estimation
 */
const complexityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'expert', label: 'Experto' },
];

/**
 * Task types for task classification
 * Defines the nature of the development work
 */
const taskTypeOptions = [
    { value: 'feature', label: 'Nueva Funcionalidad' },
    { value: 'bugfix', label: 'Corrección de Bug' },
    { value: 'improvement', label: 'Improvement' },
    { value: 'refactor', label: 'Refactorización' },
    { value: 'documentation', label: 'Documentation' },
    { value: 'testing', label: 'Testing' },
    { value: 'research', label: 'Investigación' },
];

/**
 * File handling functions for attachment management
 */
const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files);
        uploadedFiles.value.push(...files);
        form.attachments = uploadedFiles.value;
    }
};

const removeFile = (index: number) => {
    uploadedFiles.value.splice(index, 1);
    form.attachments = uploadedFiles.value;
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    dragOver.value = false;
    
    if (event.dataTransfer?.files) {
        const files = Array.from(event.dataTransfer.files);
        uploadedFiles.value.push(...files);
        form.attachments = uploadedFiles.value;
    }
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Related-task search removed (solo tareas)

// Watch for changes in props and update form accordingly
watch(() => props.sprint, (newSprint) => {
    if (newSprint) {
        form.sprint_id = newSprint.id;
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

// Prefijar ids cuando se abre el modal
watch(open, (isOpen) => {
    if (isOpen) {
        if (!form.sprint_id && props.sprint?.id) {
            form.sprint_id = props.sprint.id
        }
        if (!form.project_id) {
            if (props.project?.id) {
                form.project_id = props.project.id
            } else if (props.sprint && (props as any).sprint.project_id) {
                form.project_id = (props as any).sprint.project_id
            } else if (props.project_id) {
                form.project_id = props.project_id
            }
        }
    }
});

const submit = () => {
    console.log('Submitting task:', form.data())
    
    // Asegurar que sprint_id y project_id estén poblados desde el contexto
    if (!form.sprint_id) {
        if (props.sprint?.id) {
            form.sprint_id = props.sprint.id
        } else {
            const match = window.location.pathname.match(/team-leader\/sprints\/([0-9a-fA-F-]+)/)
            if (match && match[1]) {
                form.sprint_id = match[1]
            }
        }
    }
    if (!form.project_id) {
        if (props.project?.id) {
            form.project_id = props.project.id
        } else if ((props as any)?.sprint?.project_id) {
            form.project_id = (props as any).sprint.project_id
        } else if (props.project_id) {
            form.project_id = props.project_id
        }
    }

    // Validar campos requeridos
    if (!form.name.trim() || !form.description.trim() || !form.sprint_id || !form.project_id) {
        // Dejamos que el backend devuelva los errores exactos; no interrumpimos con alerts
    }
    
    // Create FormData para manejar archivos
    const formData = new FormData();
    
    // Agregar todos los campos del formulario
    Object.keys(form.data()).forEach(key => {
        if (key === 'attachments') {
            // Manejar archivos por separado
            uploadedFiles.value.forEach(file => {
                formData.append('attachments[]', file);
            });
        } else {
            // Evitar enviar campos vacíos que rompan validación backend
            const value = form.data()[key as keyof ReturnType<typeof form.data>];
            if (key === 'assigned_user_id' && (!value || String(value).trim() === '')) {
                // no enviar assigned_user_id vacío
                return;
            }
            formData.append(key, value as any);
        }
    });
    
    const endpoint = '/tasks';
    
    // Usar post con FormData
    router.post(endpoint, formData, {
        onSuccess: () => {
            form.reset();
            uploadedFiles.value = [];
            open.value = false;
            // Redirigir a la vista de sprint del TL si tenemos sprint_id
            if (form.sprint_id) {
                router.visit(`/team-leader/sprints/${form.sprint_id}`);
            }
        },
        onError: (errors) => {
            console.error('task creation errors:', errors);
            // Los errores se mostrarán junto a los campos mediante InputError
        }
    });
}

const closeModal = () => {
    if (form.processing) {
        return; // Don't close if form is being processed
    }
    
    // Check if there's any data in the form
    const hasData = form.name || form.description || form.story_points > 1 || form.estimated_hours > 1 || uploadedFiles.value.length > 0;
    
    if (hasData) {
        // Ask for confirmation before closing
        if (confirm('Are you sure you want to close? Any unsaved changes will be lost.')) {
            open.value = false;
            form.reset();
            uploadedFiles.value = [];
        }
    } else {
        open.value = false;
        form.reset();
        uploadedFiles.value = [];
    }
}

const resetForm = () => {
    form.reset();
    uploadedFiles.value = [];
    // Solo tareas en este modal
}
</script>

<template>
    <div>
        <Button @click="open = true" class="bg-blue-600 hover:bg-blue-700 text-white">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Task
        </Button>

        <Dialog :open="open" @update:open="open = $event">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogTitle class="text-xl font-semibold mb-4">
                    Create Task
                </DialogTitle>

                

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Required Fields Section -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Required Fields
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label for="name" class="text-gray-700 font-medium">Name <span class="text-red-500">*</span></Label>
                                <Input 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Task name"
                                    required
                                    class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <Label for="priority" class="text-gray-700 font-medium">Priority <span class="text-red-500">*</span></Label>
                                <Select v-model="form.priority">
                                    <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                                        <SelectValue placeholder="Select priority" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="option in priorityOptions" 
                                            :key="option.value" 
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.priority" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <Label for="description" class="text-gray-700 font-medium">Description <span class="text-red-500">*</span></Label>
                            <Textarea 
                                id="description" 
                                v-model="form.description" 
                                placeholder="Brief description of the task"
                                :rows="3"
                                required
                                class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <Label for="category" class="text-gray-700 font-medium">Category <span class="text-red-500">*</span></Label>
                                <Select v-model="form.category">
                                    <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="option in categoryOptions" 
                                            :key="option.value" 
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.category" />
                            </div>

                            <div>
                                <Label for="story_points" class="text-gray-700 font-medium">Story Points <span class="text-red-500">*</span></Label>
                                <Select v-model="form.story_points">
                                    <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                                        <SelectValue placeholder="Select story points" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="points in storyPointsOptions" 
                                            :key="points" 
                                            :value="points"
                                        >
                                            {{ points }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.story_points" />
                            </div>

                            <div>
                                <Label for="task_type" class="text-gray-700 font-medium">Task Type <span class="text-red-500">*</span></Label>
                                <Select v-model="form.task_type">
                                    <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="feature">Feature</SelectItem>
                                        <SelectItem value="bug">Bug</SelectItem>
                                        <SelectItem value="improvement">Improvement</SelectItem>
                                        <SelectItem value="task">Task</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.task_type" />
                            </div>
                        </div>
                    </div>

                    <!-- Optional Fields Section -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Optional Fields
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <Label for="long_description" class="text-gray-700 font-medium">Detailed Description</Label>
                                <Textarea 
                                    id="long_description" 
                                    v-model="form.long_description" 
                                    placeholder="Detailed description with context, requirements, etc."
                                    :rows="4"
                                    class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label for="complexity_level" class="text-gray-700 font-medium">Complexity Level</Label>
                                    <Select v-model="form.complexity_level">
                                        <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                                            <SelectValue placeholder="Select complexity" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="low">Low</SelectItem>
                                            <SelectItem value="medium">Medium</SelectItem>
                                            <SelectItem value="high">High</SelectItem>
                                            <SelectItem value="very_high">Very High</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label for="assigned_user_id" class="text-gray-700 font-medium">Assign To</Label>
                                    <Select v-model="form.assigned_user_id">
                                        <SelectTrigger class="bg-white border-gray-300 text-gray-900">
                                            <SelectValue placeholder="Select developer" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">Unassigned</SelectItem>
                                            <SelectItem 
                                                v-for="developer in props.developers" 
                                                :key="developer.id" 
                                                :value="developer.id"
                                            >
                                                {{ developer.name }} ({{ developer.email }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label for="estimated_hours" class="text-gray-700 font-medium">Estimated Hours</Label>
                                    <Input 
                                        id="estimated_hours" 
                                        v-model="form.estimated_hours" 
                                        type="number" 
                                        min="0" 
                                        max="100"
                                        placeholder="0"
                                        class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                    />
                                </div>

                                <div>
                                    <Label for="estimated_minutes" class="text-gray-700 font-medium">Estimated Minutes</Label>
                                    <Input 
                                        id="estimated_minutes" 
                                        v-model="form.estimated_minutes" 
                                        type="number" 
                                        min="0" 
                                        max="59"
                                        placeholder="0"
                                        class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label for="acceptance_criteria" class="text-gray-700 font-medium">Acceptance Criteria</Label>
                                <Textarea 
                                    id="acceptance_criteria" 
                                    v-model="form.acceptance_criteria" 
                                    placeholder="List of criteria that must be met to consider the task completed"
                                    :rows="3"
                                    class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                />
                            </div>

                            <div>
                                <Label for="technical_notes" class="text-gray-700 font-medium">Technical Notes</Label>
                                <Textarea 
                                    id="technical_notes" 
                                    v-model="form.technical_notes" 
                                    placeholder="Technical notes, implementation considerations, etc."
                                    :rows="3"
                                    class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                />
                            </div>

                            <div>
                                <Label for="tags" class="text-gray-700 font-medium">Tags</Label>
                                <Input 
                                    id="tags" 
                                    v-model="form.tags" 
                                    placeholder="Tags separated by commas (e.g., frontend, api, bug)"
                                    class="bg-white border-gray-300 text-gray-900 placeholder-gray-500"
                                />
                            </div>

                            <!-- File Upload Section -->
                            <div>
                                <Label class="text-gray-700 font-medium">Attachments</Label>
                                <div 
                                    @drop="handleDrop"
                                    @dragover="handleDragOver"
                                    @dragleave="handleDragLeave"
                                    :class="[
                                        'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
                                        dragOver ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:border-gray-400 bg-white'
                                    ]"
                                >
                                    <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Drag files here or 
                                        <label for="file-upload" class="text-blue-600 hover:text-blue-500 cursor-pointer">
                                            select files
                                        </label>
                                    </p>
                                    <input 
                                        id="file-upload" 
                                        type="file" 
                                        multiple 
                                        @change="handleFileUpload" 
                                        class="hidden"
                                    />
                                    <p class="text-xs text-gray-500">Maximum 10MB per file</p>
                                </div>

                                <!-- File List -->
                                <div v-if="uploadedFiles.length > 0" class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Selected files:</h4>
                                    <div class="space-y-2">
                                        <div 
                                            v-for="(file, index) in uploadedFiles" 
                                            :key="index"
                                            class="flex items-center justify-between p-2 bg-gray-50 rounded"
                                        >
                                            <span class="text-sm text-gray-700">{{ file.name }}</span>
                                            <button 
                                                @click="removeFile(index)" 
                                                type="button"
                                                class="text-red-500 hover:text-red-700"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="resetForm"
                            :disabled="form.processing"
                        >
                            Clear
                        </Button>
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="closeModal"
                            :disabled="form.processing"
                        >
                            Cancel
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700"
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
