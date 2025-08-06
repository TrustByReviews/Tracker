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
    roles?: Array<{
        name: string,
        value: string
    }>
}

const props = defineProps<{
    sprint?: Sprint
    project?: Project
    project_id?: string
    developers?: User[]
}>()

const open = ref(false);
const itemType = ref('task'); // 'task' or 'bug'
const uploadedFiles = ref<File[]>([]);
const dragOver = ref(false);

// Task search variables
const taskSearchQuery = ref('');
const filteredTasks = ref<any[]>([]);
const showTaskResults = ref(false);
const selectedRelatedTask = ref<any>(null);

const form = useForm({
    // Common fields
    name: '',
    description: '',
    long_description: '',
    priority: 'medium',
    sprint_id: '',
    project_id: '',
    estimated_hours: 1,
    estimated_minutes: 0,
    assigned_user_id: '',
    attachments: [],
    tags: '',
    
    // Task-specific fields
    category: 'full stack',
    story_points: 1,
    acceptance_criteria: '',
    technical_notes: '',
    complexity_level: 'medium',
    task_type: 'feature',
    
    // Bug-specific fields
    importance: 'medium',
    bug_type: 'other',
    environment: '',
    browser_info: '',
    os_info: '',
    steps_to_reproduce: '',
    expected_behavior: '',
    actual_behavior: '',
    reproducibility: 'sometimes',
    severity: 'medium',
    related_task_id: '', // Para relacionar bugs con tareas
});

// Priority options
const priorityOptions = [
    { value: 'low', label: 'Baja', color: 'text-green-600' },
    { value: 'medium', label: 'Media', color: 'text-yellow-600' },
    { value: 'high', label: 'Alta', color: 'text-orange-600' },
    { value: 'critical', label: 'Crítica', color: 'text-red-600' },
];

// Category options
const categoryOptions = [
    { value: 'frontend', label: 'Frontend' },
    { value: 'backend', label: 'Backend' },
    { value: 'full stack', label: 'Full Stack' },
    { value: 'design', label: 'Diseño' },
    { value: 'deployment', label: 'Despliegue' },
    { value: 'fixes', label: 'Correcciones' },
    { value: 'testing', label: 'Testing' },
    { value: 'documentation', label: 'Documentación' },
    { value: 'database', label: 'Base de Datos' },
    { value: 'api', label: 'API' },
    { value: 'security', label: 'Seguridad' },
    { value: 'performance', label: 'Rendimiento' },
];

// Story points options
const storyPointsOptions = [1, 2, 3, 5, 8, 13, 21];

// Complexity levels
const complexityOptions = [
    { value: 'low', label: 'Baja' },
    { value: 'medium', label: 'Media' },
    { value: 'high', label: 'Alta' },
    { value: 'expert', label: 'Experto' },
];

// Task types
const taskTypeOptions = [
    { value: 'feature', label: 'Nueva Funcionalidad' },
    { value: 'bugfix', label: 'Corrección de Bug' },
    { value: 'improvement', label: 'Mejora' },
    { value: 'refactor', label: 'Refactorización' },
    { value: 'documentation', label: 'Documentación' },
    { value: 'testing', label: 'Testing' },
    { value: 'research', label: 'Investigación' },
];

// Bug types
const bugTypeOptions = [
    { value: 'frontend', label: 'Frontend' },
    { value: 'backend', label: 'Backend' },
    { value: 'database', label: 'Base de Datos' },
    { value: 'api', label: 'API' },
    { value: 'ui_ux', label: 'UI/UX' },
    { value: 'performance', label: 'Rendimiento' },
    { value: 'security', label: 'Seguridad' },
    { value: 'other', label: 'Otro' },
];

// Importance levels
const importanceOptions = [
    { value: 'low', label: 'Baja' },
    { value: 'medium', label: 'Media' },
    { value: 'high', label: 'Alta' },
    { value: 'critical', label: 'Crítica' },
];

// Severity levels
const severityOptions = [
    { value: 'low', label: 'Baja' },
    { value: 'medium', label: 'Media' },
    { value: 'high', label: 'Alta' },
    { value: 'critical', label: 'Crítica' },
];

// Reproducibility options
const reproducibilityOptions = [
    { value: 'always', label: 'Siempre' },
    { value: 'sometimes', label: 'A veces' },
    { value: 'rarely', label: 'Raramente' },
    { value: 'unable', label: 'No se puede reproducir' },
];

// Environment options
const environmentOptions = [
    { value: 'development', label: 'Desarrollo' },
    { value: 'staging', label: 'Staging' },
    { value: 'production', label: 'Producción' },
    { value: 'testing', label: 'Testing' },
];

// Browser options
const browserOptions = [
    { value: 'Chrome', label: 'Chrome' },
    { value: 'Firefox', label: 'Firefox' },
    { value: 'Safari', label: 'Safari' },
    { value: 'Edge', label: 'Edge' },
    { value: 'Opera', label: 'Opera' },
    { value: 'Other', label: 'Otro' },
];

// OS options
const osOptions = [
    { value: 'Windows', label: 'Windows' },
    { value: 'macOS', label: 'macOS' },
    { value: 'Linux', label: 'Linux' },
    { value: 'iOS', label: 'iOS' },
    { value: 'Android', label: 'Android' },
    { value: 'Other', label: 'Otro' },
];

// File handling functions
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

// Task search functions
const searchTasks = async () => {
    if (taskSearchQuery.value.length < 2) {
        filteredTasks.value = [];
        return;
    }
    
    try {
        const response = await fetch(`/api/tasks/search?q=${encodeURIComponent(taskSearchQuery.value)}`);
        if (response.ok) {
            const tasks = await response.json();
            filteredTasks.value = tasks.slice(0, 10); // Limit to 10 results
        }
    } catch (error) {
        console.error('Error searching tasks:', error);
        filteredTasks.value = [];
    }
};

const selectRelatedTask = (task: any) => {
    selectedRelatedTask.value = task;
    form.related_task_id = task.id;
    taskSearchQuery.value = '';
    showTaskResults.value = false;
    filteredTasks.value = [];
};

const clearRelatedTask = () => {
    selectedRelatedTask.value = null;
    form.related_task_id = '';
};

// Close task results when clicking outside
const closeTaskResults = () => {
    setTimeout(() => {
        showTaskResults.value = false;
    }, 200);
};

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

const submit = () => {
    console.log(`Submitting ${itemType.value}:`, form.data())
    
    // Validar campos requeridos
    if (!form.name.trim()) {
        alert(`El nombre del ${itemType.value} es requerido`);
        return;
    }
    
    if (!form.description.trim()) {
        alert(`La descripción del ${itemType.value} es requerida`);
        return;
    }
    
    if (!form.sprint_id) {
        alert('Por favor selecciona un sprint');
        return;
    }
    
    if (!form.project_id) {
        alert('Por favor selecciona un proyecto');
        return;
    }
    
    // Crear FormData para manejar archivos
    const formData = new FormData();
    
    // Agregar todos los campos del formulario
    Object.keys(form.data()).forEach(key => {
        if (key === 'attachments') {
            // Manejar archivos por separado
            uploadedFiles.value.forEach(file => {
                formData.append('attachments[]', file);
            });
        } else {
            formData.append(key, form.data()[key]);
        }
    });
    
    const endpoint = itemType.value === 'task' ? '/tasks' : '/bugs';
    
    // Usar post con FormData
    router.post(endpoint, formData, {
        onSuccess: () => {
            form.reset();
            uploadedFiles.value = [];
            open.value = false;
            // Redirect to the appropriate page
            if (props.sprint) {
                if (itemType.value === 'task') {
                    router.visit(`/projects/${props.project_id}/sprints/${props.sprint.id}`);
                } else {
                    router.visit('/bugs');
                }
            } else {
                router.visit(itemType.value === 'task' ? '/tasks' : '/bugs');
            }
        },
        onError: (errors) => {
            console.error(`${itemType.value} creation errors:`, errors);
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
    const hasData = form.name || form.description || form.story_points > 1 || form.estimated_hours > 1 || uploadedFiles.value.length > 0;
    
    if (hasData) {
        // Ask for confirmation before closing
        if (confirm('¿Estás seguro de que quieres cerrar? Cualquier cambio no guardado se perderá.')) {
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
    itemType.value = 'task';
}
</script>

<template>
    <div>
        <Button @click="open = true" class="bg-blue-600 hover:bg-blue-700 text-white">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Crear {{ itemType === 'task' ? 'Tarea' : 'Bug' }}
        </Button>

        <Dialog :open="open" @update:open="open = $event">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogTitle class="text-xl font-semibold mb-4">
                    Crear {{ itemType === 'task' ? 'Tarea' : 'Bug' }}
                </DialogTitle>

                <!-- Type Selector -->
                <div class="mb-6">
                    <Label class="block text-sm font-medium mb-2">Tipo de Elemento</Label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                v-model="itemType" 
                                value="task" 
                                class="mr-2"
                            >
                            <span class="text-sm">Tarea</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                v-model="itemType" 
                                value="bug" 
                                class="mr-2"
                            >
                            <span class="text-sm">Bug</span>
                        </label>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <Label for="name">Nombre *</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                placeholder="Nombre del elemento"
                                required
                            />
                        </div>

                        <div>
                            <Label for="priority">Prioridad</Label>
                            <Select v-model="form.priority">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar prioridad" />
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
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <Label for="description">Descripción *</Label>
                        <Textarea 
                            id="description" 
                            v-model="form.description" 
                            placeholder="Descripción breve del elemento"
                            rows="3"
                            required
                        />
                    </div>

                    <!-- Long Description -->
                    <div>
                        <Label for="long_description">Descripción Detallada</Label>
                        <Textarea 
                            id="long_description" 
                            v-model="form.long_description" 
                            placeholder="Descripción detallada con contexto, requisitos, etc."
                            rows="5"
                        />
                    </div>

                    <!-- Task-specific fields -->
                    <div v-if="itemType === 'task'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <Label for="category">Categoría</Label>
                            <Select v-model="form.category">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar categoría" />
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
                        </div>

                        <div>
                            <Label for="story_points">Story Points</Label>
                            <Select v-model="form.story_points">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar story points" />
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
                        </div>

                        <div>
                            <Label for="task_type">Tipo de Tarea</Label>
                            <Select v-model="form.task_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in taskTypeOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Bug-specific fields -->
                    <div v-if="itemType === 'bug'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <Label for="bug_type">Tipo de Bug</Label>
                            <Select v-model="form.bug_type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in bugTypeOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="importance">Importancia</Label>
                            <Select v-model="form.importance">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar importancia" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in importanceOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="severity">Severidad</Label>
                            <Select v-model="form.severity">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar severidad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in severityOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Environment Information (Bug-specific) -->
                    <div v-if="itemType === 'bug'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <Label for="environment">Ambiente</Label>
                            <Select v-model="form.environment">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar ambiente" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in environmentOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="browser_info">Navegador</Label>
                            <Select v-model="form.browser_info">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar navegador" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in browserOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="os_info">Sistema Operativo</Label>
                            <Select v-model="form.os_info">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar SO" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="option in osOptions" 
                                        :key="option.value" 
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Steps to Reproduce (Bug-specific) -->
                    <div v-if="itemType === 'bug'">
                        <Label for="steps_to_reproduce">Pasos para Reproducir</Label>
                        <Textarea 
                            id="steps_to_reproduce" 
                            v-model="form.steps_to_reproduce" 
                            placeholder="1. Ir a la página...&#10;2. Hacer clic en...&#10;3. Observar que..."
                            rows="4"
                        />
                    </div>

                    <!-- Expected vs Actual Behavior (Bug-specific) -->
                    <div v-if="itemType === 'bug'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <Label for="expected_behavior">Comportamiento Esperado</Label>
                            <Textarea 
                                id="expected_behavior" 
                                v-model="form.expected_behavior" 
                                placeholder="Lo que debería suceder"
                                rows="3"
                            />
                        </div>

                        <div>
                            <Label for="actual_behavior">Comportamiento Actual</Label>
                            <Textarea 
                                id="actual_behavior" 
                                v-model="form.actual_behavior" 
                                placeholder="Lo que realmente sucede"
                                rows="3"
                            />
                        </div>
                    </div>

                    <!-- Reproducibility (Bug-specific) -->
                    <div v-if="itemType === 'bug'">
                        <Label for="reproducibility">Reproducibilidad</Label>
                        <Select v-model="form.reproducibility">
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar reproducibilidad" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="option in reproducibilityOptions" 
                                    :key="option.value" 
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Related Task (Bug-specific) -->
                    <div v-if="itemType === 'bug'">
                        <Label for="related_task_search">Tarea Relacionada</Label>
                        <div class="relative">
                            <Input 
                                id="related_task_search" 
                                v-model="taskSearchQuery" 
                                placeholder="Buscar tarea por nombre, sprint o proyecto..."
                                @input="searchTasks"
                                @focus="showTaskResults = true"
                            />
                            
                            <!-- Task Search Results -->
                            <div v-if="showTaskResults && filteredTasks.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <div 
                                    v-for="task in filteredTasks" 
                                    :key="task.id"
                                    @click="selectRelatedTask(task)"
                                    class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0"
                                >
                                    <div class="font-medium text-sm">{{ task.name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ task.sprint?.name || 'Sin sprint' }} • {{ task.project?.name || 'Sin proyecto' }}
                                    </div>
                                    <div class="text-xs text-gray-400">{{ task.status }} • {{ task.priority }}</div>
                                </div>
                            </div>
                            
                            <!-- Selected Task Display -->
                            <div v-if="selectedRelatedTask" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-sm text-blue-900">{{ selectedRelatedTask.name }}</div>
                                        <div class="text-xs text-blue-700">
                                            {{ selectedRelatedTask.sprint?.name || 'Sin sprint' }} • {{ selectedRelatedTask.project?.name || 'Sin proyecto' }}
                                        </div>
                                    </div>
                                    <button 
                                        @click="clearRelatedTask"
                                        class="text-blue-500 hover:text-blue-700 text-sm"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Opcional: Relacionar este bug con una tarea existente</p>
                    </div>

                    <!-- Acceptance Criteria (Task-specific) -->
                    <div v-if="itemType === 'task'">
                        <Label for="acceptance_criteria">Criterios de Aceptación</Label>
                        <Textarea 
                            id="acceptance_criteria" 
                            v-model="form.acceptance_criteria" 
                            placeholder="Criterios que deben cumplirse para considerar la tarea completada"
                            rows="4"
                        />
                    </div>

                    <!-- Technical Notes -->
                    <div>
                        <Label for="technical_notes">Notas Técnicas</Label>
                        <Textarea 
                            id="technical_notes" 
                            v-model="form.technical_notes" 
                            placeholder="Notas técnicas, consideraciones de implementación, etc."
                            rows="3"
                        />
                    </div>

                    <!-- Tags -->
                    <div>
                        <Label for="tags">Etiquetas</Label>
                        <Input 
                            id="tags" 
                            v-model="form.tags" 
                            placeholder="etiqueta1, etiqueta2, etiqueta3"
                        />
                        <p class="text-sm text-gray-500 mt-1">Separar etiquetas con comas</p>
                    </div>

                    <!-- Time Estimation -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <Label for="estimated_hours">Horas Estimadas</Label>
                            <Input 
                                id="estimated_hours" 
                                v-model="form.estimated_hours" 
                                type="number" 
                                min="0" 
                                max="100"
                            />
                        </div>

                        <div>
                            <Label for="estimated_minutes">Minutos Estimados</Label>
                            <Input 
                                id="estimated_minutes" 
                                v-model="form.estimated_minutes" 
                                type="number" 
                                min="0" 
                                max="59"
                            />
                        </div>
                    </div>

                    <!-- Complexity Level (Task-specific) -->
                    <div v-if="itemType === 'task'">
                        <Label for="complexity_level">Nivel de Complejidad</Label>
                        <Select v-model="form.complexity_level">
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar complejidad" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="option in complexityOptions" 
                                    :key="option.value" 
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <Label>Archivos Adjuntos</Label>
                        <div 
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
                            :class="{ 'border-blue-500 bg-blue-50': dragOver }"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                        >
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="mt-4">
                                <label for="file-upload" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                    <span>Seleccionar archivos</span>
                                    <input 
                                        id="file-upload" 
                                        type="file" 
                                        multiple 
                                        class="sr-only" 
                                        @change="handleFileUpload"
                                        accept="image/*,.pdf,.doc,.docx,.txt,.zip,.rar"
                                    >
                                </label>
                                <p class="text-sm text-gray-500 mt-2">
                                    o arrastra y suelta archivos aquí
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    PNG, JPG, PDF, DOC, TXT hasta 10MB
                                </p>
                            </div>
                        </div>

                        <!-- File List -->
                        <div v-if="uploadedFiles.length > 0" class="mt-4">
                            <h4 class="text-sm font-medium mb-2">Archivos seleccionados:</h4>
                            <div class="space-y-2">
                                <div 
                                    v-for="(file, index) in uploadedFiles" 
                                    :key="index"
                                    class="flex items-center justify-between p-2 bg-gray-50 rounded"
                                >
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span class="text-sm">{{ file.name }}</span>
                                        <span class="text-xs text-gray-500 ml-2">({{ formatFileSize(file.size) }})</span>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="removeFile(index)"
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

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="resetForm"
                            :disabled="form.processing"
                        >
                            Limpiar
                        </Button>
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="closeModal"
                            :disabled="form.processing"
                        >
                            Cancelar
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700"
                        >
                            <span v-if="form.processing">Creando...</span>
                            <span v-else>Crear {{ itemType === 'task' ? 'Tarea' : 'Bug' }}</span>
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
