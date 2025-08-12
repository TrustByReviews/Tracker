<script setup lang="ts">
import { ref, defineProps, watch } from 'vue';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { useForm, router } from '@inertiajs/vue3';

interface Project {
    id: string;
    name: string;
}

// Define props con valores por defecto para evitar errores de undefined
const props = defineProps<{
    projects?: Project[];
    project?: Project;
}>();

const open = ref(false);
const activeTab = ref('basic');

// Variables para campos de texto personalizados
const newBlocker = ref('');
const newRisk = ref('');
const newAcceptanceCriteria = ref('');
const newDefinitionOfDone = ref('');
const newQualityGate = ref('');

const form = useForm({
  // Fase 1: Campos esenciales
  name: 'Sprint 5 - Advanced Features Implementation',
  goal: 'Implement advanced user authentication and payment processing features',
  start_date: '2024-04-01',
  end_date: '2024-04-15',
  project_id: '',
  description: 'This sprint focuses on implementing advanced authentication features including OAuth2, two-factor authentication, and secure payment processing integration.',
  sprint_type: 'regular',
  planned_start_date: '2024-04-01',
  planned_end_date: '2024-04-15',
  actual_start_date: '2024-04-01',
  actual_end_date: '',
  duration_days: 15,
  sprint_objective: 'Complete implementation of advanced authentication system with payment processing',
  user_stories_included: ['US-001', 'US-002', 'US-003', 'US-004'],
  assigned_tasks: ['TASK-001', 'TASK-002', 'TASK-003', 'TASK-004', 'TASK-005'],
  acceptance_criteria: 'All authentication features must be tested and approved by security team',
  
  // Fase 2: Campos de seguimiento avanzado
  planned_velocity: 25,
  actual_velocity: 22,
  velocity_deviation: -12.0,
  progress_percentage: 75.5,
  blockers: ['External API dependency', 'Team member on vacation'],
  risks: ['Security compliance review', 'Performance bottlenecks'],
  blocker_resolution_notes: 'API dependency resolved, team member returning next week',
  detailed_acceptance_criteria: [
    'User can register with email verification',
    'OAuth2 login works with multiple providers',
    'Two-factor authentication is implemented',
    'Payment processing integration is complete'
  ],
  definition_of_done: [
    'Code reviewed and approved',
    'Unit tests written and passing',
    'Security review completed',
    'Documentation updated',
    'Deployed to staging environment'
  ],
  quality_gates: [
    'Code coverage > 80%',
    'No critical security vulnerabilities',
    'Performance benchmarks met',
    'Accessibility standards met'
  ],
  bugs_found: 8,
  bugs_resolved: 6,
  bug_resolution_rate: 75.0,
  code_reviews_completed: 12,
  code_reviews_pending: 3,
  daily_scrums_held: 10,
  daily_scrums_missed: 2,
  daily_scrum_attendance_rate: 83.33
});

// Inicializar project_id cuando se pasa un proyecto individual
const initializeProjectId = () => {
  if (props.project?.id) {
    form.project_id = props.project.id;
  }
};

// Inicializar cuando el componente se monta o cuando cambia el proyecto
watch(() => props.project, initializeProjectId, { immediate: true });

const submit = () => {
  console.log('Submitting sprint form with data:', form.data());
  
  form.post('/sprints', {
    onSuccess: () => {
      console.log('Sprint created successfully');
      form.reset();
      open.value = false;
      
      // Si estamos en una página de proyecto específico, quedarnos ahí
      if (props.project?.id) {
        router.visit(`/projects/${props.project.id}`, { 
          preserveState: true,
          preserveScroll: true 
        });
      } else {
        router.reload();
      }
    },
    onError: (errors) => {
      console.error('Sprint creation failed:', errors);
    },
  });
};

const resetForm = () => {
  form.reset();
  activeTab.value = 'basic';
  // Reset custom input fields
  newBlocker.value = '';
  newRisk.value = '';
  newAcceptanceCriteria.value = '';
  newDefinitionOfDone.value = '';
  newQualityGate.value = '';
};

// Helper functions for array management
const toggleArrayItem = (array: string[], item: string) => {
  const index = array.indexOf(item);
  if (index > -1) {
    array.splice(index, 1);
  } else {
    array.push(item);
  }
};

const addCustomBlocker = () => {
  if (newBlocker.value.trim() && !form.blockers.includes(newBlocker.value.trim())) {
    form.blockers.push(newBlocker.value.trim());
    newBlocker.value = '';
  }
};

const addCustomRisk = () => {
  if (newRisk.value.trim() && !form.risks.includes(newRisk.value.trim())) {
    form.risks.push(newRisk.value.trim());
    newRisk.value = '';
  }
};

const addCustomAcceptanceCriteria = () => {
  if (newAcceptanceCriteria.value.trim() && !form.detailed_acceptance_criteria.includes(newAcceptanceCriteria.value.trim())) {
    form.detailed_acceptance_criteria.push(newAcceptanceCriteria.value.trim());
    newAcceptanceCriteria.value = '';
  }
};

const addCustomDefinitionOfDone = () => {
  if (newDefinitionOfDone.value.trim() && !form.definition_of_done.includes(newDefinitionOfDone.value.trim())) {
    form.definition_of_done.push(newDefinitionOfDone.value.trim());
    newDefinitionOfDone.value = '';
  }
};

const addCustomQualityGate = () => {
  if (newQualityGate.value.trim() && !form.quality_gates.includes(newQualityGate.value.trim())) {
    form.quality_gates.push(newQualityGate.value.trim());
    newQualityGate.value = '';
  }
};

// Opciones para los selects
const sprintTypeOptions = [
  { value: 'regular', label: 'Regular Sprint' },
  { value: 'release', label: 'Release Sprint' },
  { value: 'hotfix', label: 'Hotfix Sprint' },
];

// Common blockers and risks
const commonBlockers = [
  'External API dependency',
  'Team member on vacation',
  'Infrastructure issues',
  'Third-party service down',
  'Security review pending',
  'Performance bottlenecks'
];

const commonRisks = [
  'API rate limiting',
  'Security compliance delays',
  'Performance bottlenecks',
  'Team availability',
  'Technical debt',
  'Scope creep'
];

const commonAcceptanceCriteria = [
  'User can register with email',
  'User can login with OAuth2',
  'Password reset functionality works',
  'Email verification is implemented',
  'Payment processing works',
  'Order confirmation is sent'
];

const commonDefinitionOfDone = [
  'Code reviewed and approved',
  'Unit tests written and passing',
  'Integration tests passing',
  'Documentation updated',
  'Deployed to staging environment',
  'QA testing completed'
];

const commonQualityGates = [
  'Code coverage > 80%',
  'No critical security vulnerabilities',
  'Performance benchmarks met',
  'Accessibility standards met',
  'Mobile responsiveness verified',
  'Cross-browser compatibility tested'
];

</script>

<template>
  <div>
    <Button @click="open = true" class="border-black bg-black text-white hover:bg-gray-400 hover:border-white hover:text-black">
      New Sprint
    </Button>

    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 bg-white rounded-lg shadow-lg">
        <!-- Title -->
        <DialogTitle class="text-lg sm:text-xl font-bold mb-4 sm:mb-6 text-gray-800">Create New Sprint</DialogTitle>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4 sm:mb-6">
          <nav class="-mb-px flex space-x-2 sm:space-x-4 overflow-x-auto">
            <button
              @click="activeTab = 'basic'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'basic'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Basic Info
            </button>
            <button
              @click="activeTab = 'planning'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'planning'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Planning
            </button>
            <button
              @click="activeTab = 'tracking'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'tracking'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Tracking
            </button>
            <button
              @click="activeTab = 'quality'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'quality'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Quality
            </button>
          </nav>
        </div>

        <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
          <!-- Basic Information Tab -->
          <div v-if="activeTab === 'basic'" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <!-- Name -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Sprint Name *</label>
                <Input 
                  v-model="form.name" 
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="Enter sprint name"
                />
                <div v-if="form.errors.name" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.name }}</div>
              </div>

              <!-- Goal -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Sprint Goal *</label>
                <Input 
                  v-model="form.goal" 
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="Enter sprint goal"
                />
                <div v-if="form.errors.goal" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.goal }}</div>
              </div>

              <!-- Description -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Description</label>
                <Textarea 
                  v-model="form.description" 
                  class="w-full border-gray-300 text-black bg-white" 
                  rows="3"
                  placeholder="Brief description of the sprint"
                />
                <div v-if="form.errors.description" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.description }}</div>
              </div>

              <!-- Project -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Project *</label>
                <!-- Si se pasa un proyecto individual, mostrar como texto -->
                <div v-if="props.project" class="p-3 bg-gray-50 border border-gray-300 rounded-md">
                  <span class="text-sm font-medium text-gray-900">{{ props.project.name }}</span>
                  <input type="hidden" v-model="form.project_id" />
                </div>
                <!-- Si se pasa una lista de proyectos, mostrar selector -->
                <Select v-else v-model="form.project_id">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select project" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.project_id" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.project_id }}</div>
              </div>

              <!-- Sprint Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Sprint Type</label>
                <Select v-model="form.sprint_type">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select sprint type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in sprintTypeOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.sprint_type" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.sprint_type }}</div>
              </div>

              <!-- Duration Days -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Duration (Days)</label>
                <Input 
                  v-model="form.duration_days" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="15"
                />
                <div v-if="form.errors.duration_days" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.duration_days }}</div>
              </div>

              <!-- Sprint Objective -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Sprint Objective</label>
                <Textarea 
                  v-model="form.sprint_objective" 
                  class="w-full border-gray-300 text-black bg-white" 
                  rows="3"
                  placeholder="Main objective of this sprint"
                />
                <div v-if="form.errors.sprint_objective" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.sprint_objective }}</div>
              </div>

              <!-- Acceptance Criteria -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Acceptance Criteria</label>
                <Textarea 
                  v-model="form.acceptance_criteria" 
                  class="w-full border-gray-300 text-black bg-white" 
                  rows="3"
                  placeholder="General acceptance criteria for the sprint"
                />
                <div v-if="form.errors.acceptance_criteria" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.acceptance_criteria }}</div>
              </div>
            </div>
          </div>

          <!-- Planning Tab -->
          <div v-if="activeTab === 'planning'" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <!-- Start Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Start Date *</label>
                <Input 
                  v-model="form.start_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.start_date" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.start_date }}</div>
              </div>

              <!-- End Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">End Date *</label>
                <Input 
                  v-model="form.end_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.end_date" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.end_date }}</div>
              </div>

              <!-- Planned Start Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Planned Start Date</label>
                <Input 
                  v-model="form.planned_start_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.planned_start_date" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.planned_start_date }}</div>
              </div>

              <!-- Planned End Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Planned End Date</label>
                <Input 
                  v-model="form.planned_end_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.planned_end_date" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.planned_end_date }}</div>
              </div>

              <!-- Actual Start Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Actual Start Date</label>
                <Input 
                  v-model="form.actual_start_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.actual_start_date" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.actual_start_date }}</div>
              </div>

              <!-- Actual End Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Actual End Date</label>
                <Input 
                  v-model="form.actual_end_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.actual_end_date" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.actual_end_date }}</div>
              </div>

              <!-- Planned Velocity -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Planned Velocity</label>
                <Input 
                  v-model="form.planned_velocity" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="25"
                />
                <div v-if="form.errors.planned_velocity" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.planned_velocity }}</div>
              </div>

              <!-- Progress Percentage -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Progress Percentage</label>
                <Input 
                  v-model="form.progress_percentage" 
                  type="number"
                  step="0.1"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="75.5"
                />
                <div v-if="form.errors.progress_percentage" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.progress_percentage }}</div>
              </div>
            </div>
          </div>

          <!-- Tracking Tab -->
          <div v-if="activeTab === 'tracking'" class="space-y-4 sm:space-y-6">
            <!-- Blocker Resolution Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Blocker Resolution Notes</label>
              <Textarea 
                v-model="form.blocker_resolution_notes" 
                class="w-full border-gray-300 text-black bg-white" 
                rows="3"
                placeholder="Notes about blocker resolutions"
              />
              <div v-if="form.errors.blocker_resolution_notes" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.blocker_resolution_notes }}</div>
            </div>

            <!-- Blocker Management -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Blockers</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="blocker in commonBlockers" 
                    :key="blocker"
                    @click="toggleArrayItem(form.blockers, blocker)"
                    :class="form.blockers.includes(blocker) ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ blocker }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newBlocker" 
                    placeholder="Add custom blocker"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomBlocker" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Risk Management -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Risks</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="risk in commonRisks" 
                    :key="risk"
                    @click="toggleArrayItem(form.risks, risk)"
                    :class="form.risks.includes(risk) ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ risk }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newRisk" 
                    placeholder="Add custom risk"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomRisk" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Bugs Found -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Bugs Found</label>
                <Input 
                  v-model="form.bugs_found" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="8"
                />
              </div>

              <!-- Bugs Resolved -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Bugs Resolved</label>
                <Input 
                  v-model="form.bugs_resolved" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="6"
                />
              </div>

              <!-- Code Reviews Completed -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Code Reviews Completed</label>
                <Input 
                  v-model="form.code_reviews_completed" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="12"
                />
              </div>

              <!-- Code Reviews Pending -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Code Reviews Pending</label>
                <Input 
                  v-model="form.code_reviews_pending" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="3"
                />
              </div>

              <!-- Daily Scrums Held -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Daily Scrums Held</label>
                <Input 
                  v-model="form.daily_scrums_held" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="10"
                />
              </div>

              <!-- Daily Scrums Missed -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Daily Scrums Missed</label>
                <Input 
                  v-model="form.daily_scrums_missed" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="2"
                />
              </div>

              <!-- Actual Velocity -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Actual Velocity</label>
                <Input 
                  v-model="form.actual_velocity" 
                  type="number"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="22"
                />
              </div>

              <!-- Velocity Deviation -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Velocity Deviation (%)</label>
                <Input 
                  v-model="form.velocity_deviation" 
                  type="number"
                  step="0.01"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="-12.0"
                />
              </div>
            </div>
          </div>

          <!-- Quality Tab -->
          <div v-if="activeTab === 'quality'" class="space-y-4 sm:space-y-6">
            <!-- Detailed Acceptance Criteria -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Detailed Acceptance Criteria</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="criteria in commonAcceptanceCriteria" 
                    :key="criteria"
                    @click="toggleArrayItem(form.detailed_acceptance_criteria, criteria)"
                    :class="form.detailed_acceptance_criteria.includes(criteria) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ criteria }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newAcceptanceCriteria" 
                    placeholder="Add custom acceptance criteria"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomAcceptanceCriteria" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Definition of Done -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Definition of Done</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="done in commonDefinitionOfDone" 
                    :key="done"
                    @click="toggleArrayItem(form.definition_of_done, done)"
                    :class="form.definition_of_done.includes(done) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ done }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newDefinitionOfDone" 
                    placeholder="Add custom definition of done"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomDefinitionOfDone" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Quality Gates -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quality Gates</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="gate in commonQualityGates" 
                    :key="gate"
                    @click="toggleArrayItem(form.quality_gates, gate)"
                    :class="form.quality_gates.includes(gate) ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ gate }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newQualityGate" 
                    placeholder="Add custom quality gate"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomQualityGate" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Quality Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Bug Resolution Rate -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Bug Resolution Rate (%)</label>
                <Input 
                  v-model="form.bug_resolution_rate" 
                  type="number"
                  step="0.01"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="75.0"
                />
              </div>

              <!-- Daily Scrum Attendance Rate -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Daily Scrum Attendance Rate (%)</label>
                <Input 
                  v-model="form.daily_scrum_attendance_rate" 
                  type="number"
                  step="0.01"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="83.33"
                />
              </div>
            </div>
          </div>

          <!-- Navigation and Submit Buttons -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pt-4 sm:pt-6 border-t border-gray-200 space-y-4 sm:space-y-0">
            <!-- Tab Navigation -->
            <div class="flex space-x-1 sm:space-x-2 overflow-x-auto w-full sm:w-auto">
              <Button 
                type="button" 
                variant="outline" 
                @click="activeTab = 'basic'"
                :class="activeTab === 'basic' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Basic
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="activeTab = 'planning'"
                :class="activeTab === 'planning' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Planning
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="activeTab = 'tracking'"
                :class="activeTab === 'tracking' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Tracking
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="activeTab = 'quality'"
                :class="activeTab === 'quality' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Quality
              </Button>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-2 sm:space-x-3 w-full sm:w-auto">
              <Button type="button" variant="outline" @click="resetForm" class="text-xs sm:text-sm px-2 sm:px-3">
                Reset
              </Button>
              <Button type="button" variant="secondary" @click="open = false" class="text-xs sm:text-sm px-2 sm:px-3">
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing" class="bg-blue-500 text-white hover:bg-blue-600 text-xs sm:text-sm px-2 sm:px-3">
                {{ form.processing ? 'Creating...' : 'Create Sprint' }}
              </Button>
            </div>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
