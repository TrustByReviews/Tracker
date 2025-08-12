<script setup lang="ts">
import { ref, defineProps } from 'vue';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm, router } from '@inertiajs/vue3';

interface Developer {
    id: number;
    name: string;
    email: string;
}

// Define props con valores por defecto para evitar errores de undefined
const props = defineProps<{
    developers?: Developer[];
}>();

const open = ref(false);
const activeTab = ref('general');

// Variables para campos de texto personalizados
const newTechnology = ref('Docker');
const newLanguage = ref('Python');
const newFramework = ref('Django');
const externalIntegrationsText = ref('Slack, Jira, Confluence');
const stakeholdersText = ref('Legal Team, Finance Department');
const milestonesText = ref('Security Audit, Performance Optimization');
const assignedResourcesText = ref('Security Specialist, Performance Engineer');
const identifiedRisksText = ref('Data privacy compliance, Scalability challenges');

const form = useForm({
  // Información General
  name: 'E-commerce Platform Development',
  description: 'Modern e-commerce platform with advanced features including user authentication, product catalog, shopping cart, payment processing, and admin dashboard.',
  objectives: 'Create a scalable e-commerce solution that supports multiple vendors, real-time inventory management, and seamless payment processing with mobile responsiveness.',
  priority: 'high',
  category: 'web',
  development_type: 'new',
  
  // Planificación
  planned_start_date: '2024-01-15',
  planned_end_date: '2024-06-30',
  actual_start_date: '2024-01-15',
  actual_end_date: '',
  methodology: 'scrum',
  
  // Technology and Architecture
  technologies: ['React', 'Node.js', 'PostgreSQL', 'Redis', 'AWS'],
  programming_languages: ['JavaScript', 'TypeScript', 'PHP'],
  frameworks: ['Laravel', 'Express.js', 'Next.js'],
  database_type: 'PostgreSQL',
  architecture: 'microservices',
  external_integrations: ['Stripe', 'SendGrid', 'AWS S3', 'Google Analytics'],
  
  // Team and Stakeholders
  project_owner: 'Juan Carlos Martínez',
  product_owner: 'María González',
  stakeholders: ['CEO', 'Marketing Team', 'Sales Department', 'Customer Support'],
  
  // Advanced Planning
  milestones: ['Requirements Analysis', 'UI/UX Design', 'Backend Development', 'Frontend Development', 'Testing Phase', 'Deployment'],
  estimated_velocity: 25,
  current_sprint: 'Sprint 3 - Backend API Development',
  
  // Budget and Resources
  estimated_budget: 75000,
  used_budget: 25000,
  assigned_resources: ['Frontend Developer', 'Backend Developer', 'UI/UX Designer', 'DevOps Engineer', 'QA Tester'],
  
  // Tracking and Metrics
  progress_percentage: 35,
  identified_risks: ['Third-party API dependencies', 'Payment gateway integration complexity', 'Mobile responsiveness challenges'],
  open_issues: 12,
  documentation_url: 'https://docs.example.com/ecommerce-platform',
  repository_url: 'https://github.com/company/ecommerce-platform',
  task_board_url: 'https://trello.com/b/ecommerce-project',
  
  // Equipo
  developers_ids: [] as number[],
  
  // Cliente
  client_name: 'Carlos Rodríguez',
  client_email: 'carlos.rodriguez@techstore.com',
  client_company: 'TechStore Solutions',
  client_password: 'TechStore2024!',
});

const submit = () => {
  form.post('/projects', {
    onSuccess: () => {
      form.reset();
      open.value = false;
      router.reload();
    },
  });
};

const resetForm = () => {
  form.reset();
  activeTab.value = 'general';
  // Reset custom input fields
  newTechnology.value = '';
  newLanguage.value = '';
  newFramework.value = '';
  externalIntegrationsText.value = '';
  stakeholdersText.value = '';
  milestonesText.value = '';
  assignedResourcesText.value = '';
  identifiedRisksText.value = '';
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

const addCustomTechnology = () => {
  if (newTechnology.value.trim() && !form.technologies.includes(newTechnology.value.trim())) {
    form.technologies.push(newTechnology.value.trim());
    newTechnology.value = '';
  }
};

const addCustomLanguage = () => {
  if (newLanguage.value.trim() && !form.programming_languages.includes(newLanguage.value.trim())) {
    form.programming_languages.push(newLanguage.value.trim());
    newLanguage.value = '';
  }
};

const addCustomFramework = () => {
  if (newFramework.value.trim() && !form.frameworks.includes(newFramework.value.trim())) {
    form.frameworks.push(newFramework.value.trim());
    newFramework.value = '';
  }
};

const updateExternalIntegrations = () => {
  form.external_integrations = externalIntegrationsText.value
    .split(',')
    .map(item => item.trim())
    .filter(item => item.length > 0);
};

const updateStakeholders = () => {
  form.stakeholders = stakeholdersText.value
    .split(',')
    .map(item => item.trim())
    .filter(item => item.length > 0);
};

const updateMilestones = () => {
  form.milestones = milestonesText.value
    .split(',')
    .map(item => item.trim())
    .filter(item => item.length > 0);
};

const updateAssignedResources = () => {
  form.assigned_resources = assignedResourcesText.value
    .split(',')
    .map(item => item.trim())
    .filter(item => item.length > 0);
};

const updateIdentifiedRisks = () => {
  form.identified_risks = identifiedRisksText.value
    .split(',')
    .map(item => item.trim())
    .filter(item => item.length > 0);
};

// Opciones para los selects
const priorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'medium', label: 'Medium' },
  { value: 'high', label: 'High' },
];

const categoryOptions = [
  { value: 'web', label: 'Web Application' },
  { value: 'mobile', label: 'Mobile Application' },
  { value: 'backend', label: 'Backend System' },
  { value: 'iot', label: 'IoT Project' },
  { value: 'other', label: 'Other' },
];

const developmentTypeOptions = [
  { value: 'new', label: 'New Development' },
  { value: 'maintenance', label: 'Maintenance' },
  { value: 'improvement', label: 'Improvement' },
];

const methodologyOptions = [
  { value: 'scrum', label: 'Scrum' },
  { value: 'kanban', label: 'Kanban' },
  { value: 'waterfall', label: 'Waterfall' },
  { value: 'hybrid', label: 'Hybrid' },
];

const architectureOptions = [
  { value: 'monolithic', label: 'Monolithic' },
  { value: 'microservices', label: 'Microservices' },
  { value: 'serverless', label: 'Serverless' },
  { value: 'hybrid', label: 'Hybrid' },
];

const databaseTypeOptions = [
  { value: 'mysql', label: 'MySQL' },
  { value: 'postgresql', label: 'PostgreSQL' },
  { value: 'mongodb', label: 'MongoDB' },
  { value: 'redis', label: 'Redis' },
  { value: 'sqlite', label: 'SQLite' },
  { value: 'mariadb', label: 'MariaDB' },
  { value: 'other', label: 'Other' },
];

// Common technology options
const commonTechnologies = [
  'React', 'Vue.js', 'Angular', 'Node.js', 'Laravel', 'Django', 'Spring Boot',
  'Docker', 'Kubernetes', 'AWS', 'Azure', 'Google Cloud', 'Firebase'
];

const commonLanguages = [
  'JavaScript', 'TypeScript', 'PHP', 'Python', 'Java', 'C#', 'Go', 'Rust',
  'Swift', 'Kotlin', 'Ruby', 'Scala'
];

const commonFrameworks = [
  'React', 'Vue.js', 'Angular', 'Express.js', 'Laravel', 'Django', 'Spring Boot',
  'ASP.NET Core', 'Flask', 'FastAPI', 'Next.js', 'Nuxt.js'
];

</script>

<template>
  <div>
    <Button @click="open = true" class="border-black bg-black text-white hover:bg-gray-400 hover:border-white hover:text-black">
      New Project
    </Button>

    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 bg-white rounded-lg shadow-lg">
        <!-- Title -->
        <DialogTitle class="text-lg sm:text-xl font-bold mb-4 sm:mb-6 text-gray-800">Create New Project</DialogTitle>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4 sm:mb-6">
          <nav class="-mb-px flex space-x-2 sm:space-x-4 overflow-x-auto">
            <button
              @click="activeTab = 'general'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'general'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              General
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
              @click="activeTab = 'technology'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'technology'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Technology
            </button>
            <button
              @click="activeTab = 'team'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'team'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Team
            </button>
            <button
              @click="activeTab = 'budget'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'budget'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Budget & Resources
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
          </nav>
        </div>

        <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
          <!-- General Information Tab -->
          <div v-if="activeTab === 'general'" class="space-y-4 sm:space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
              <!-- Name -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Project Name *</label>
                <Input 
                  v-model="form.name" 
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="Enter project name"
                />
                <div v-if="form.errors.name" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.name }}</div>
              </div>

              <!-- Description -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2">Description *</label>
                <Textarea 
                  v-model="form.description" 
                  class="w-full border-gray-300 text-black bg-white" 
                  rows="3"
                  placeholder="Brief description of the project"
                />
                <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
              </div>

              <!-- Objectives -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Objectives</label>
                <Textarea 
                  v-model="form.objectives" 
                  class="w-full border-gray-300 text-black bg-white" 
                  rows="4"
                  placeholder="List the main objectives and goals of this project"
                />
                <div v-if="form.errors.objectives" class="text-red-500 text-sm mt-1">{{ form.errors.objectives }}</div>
              </div>

              <!-- Priority -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <Select v-model="form.priority">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select priority" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in priorityOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.priority" class="text-red-500 text-sm mt-1">{{ form.errors.priority }}</div>
              </div>

              <!-- Category -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <Select v-model="form.category">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select category" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in categoryOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.category" class="text-red-500 text-sm mt-1">{{ form.errors.category }}</div>
              </div>

              <!-- Development Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Development Type</label>
                <Select v-model="form.development_type">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select development type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in developmentTypeOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.development_type" class="text-red-500 text-sm mt-1">{{ form.errors.development_type }}</div>
              </div>

              <!-- Methodology -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Methodology</label>
                <Select v-model="form.methodology">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select methodology" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in methodologyOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.methodology" class="text-red-500 text-sm mt-1">{{ form.errors.methodology }}</div>
              </div>
            </div>
          </div>

          <!-- Planning Tab -->
          <div v-if="activeTab === 'planning'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Planned Start Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Planned Start Date</label>
                <Input 
                  v-model="form.planned_start_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.planned_start_date" class="text-red-500 text-sm mt-1">{{ form.errors.planned_start_date }}</div>
              </div>

              <!-- Planned End Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Planned End Date</label>
                <Input 
                  v-model="form.planned_end_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.planned_end_date" class="text-red-500 text-sm mt-1">{{ form.errors.planned_end_date }}</div>
              </div>

              <!-- Actual Start Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Actual Start Date</label>
                <Input 
                  v-model="form.actual_start_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.actual_start_date" class="text-red-500 text-sm mt-1">{{ form.errors.actual_start_date }}</div>
              </div>

              <!-- Actual End Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Actual End Date</label>
                <Input 
                  v-model="form.actual_end_date" 
                  type="date"
                  class="w-full border-gray-300 text-black bg-white" 
                />
                <div v-if="form.errors.actual_end_date" class="text-red-500 text-sm mt-1">{{ form.errors.actual_end_date }}</div>
              </div>
            </div>
                    </div>

          <!-- Technology Tab -->
          <div v-if="activeTab === 'technology'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Technologies -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
                <div class="flex flex-wrap gap-2 mb-2">
                  <button
                    v-for="tech in commonTechnologies"
                    :key="tech"
                    @click="toggleArrayItem(form.technologies, tech)"
                    :class="[
                      'px-3 py-1 rounded-full text-sm border',
                      form.technologies.includes(tech)
                        ? 'bg-blue-100 border-blue-300 text-blue-700'
                        : 'bg-gray-100 border-gray-300 text-gray-700 hover:bg-gray-200'
                    ]"
                  >
                    {{ tech }}
                  </button>
                </div>
                <Input 
                  v-model="newTechnology"
                  @keyup.enter="addCustomTechnology"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="Add custom technology and press Enter"
                />
                <div v-if="form.errors.technologies" class="text-red-500 text-sm mt-1">{{ form.errors.technologies }}</div>
              </div>

              <!-- Programming Languages -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Programming Languages</label>
                <div class="flex flex-wrap gap-2 mb-2">
                  <button
                    v-for="lang in commonLanguages"
                    :key="lang"
                    @click="toggleArrayItem(form.programming_languages, lang)"
                    :class="[
                      'px-3 py-1 rounded-full text-sm border',
                      form.programming_languages.includes(lang)
                        ? 'bg-green-100 border-green-300 text-green-700'
                        : 'bg-gray-100 border-gray-300 text-gray-700 hover:bg-gray-200'
                    ]"
                  >
                    {{ lang }}
                  </button>
                </div>
                <Input 
                  v-model="newLanguage"
                  @keyup.enter="addCustomLanguage"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="Add custom language and press Enter"
                />
                <div v-if="form.errors.programming_languages" class="text-red-500 text-sm mt-1">{{ form.errors.programming_languages }}</div>
              </div>

              <!-- Frameworks -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Frameworks</label>
                <div class="flex flex-wrap gap-2 mb-2">
                  <button
                    v-for="framework in commonFrameworks"
                    :key="framework"
                    @click="toggleArrayItem(form.frameworks, framework)"
                    :class="[
                      'px-3 py-1 rounded-full text-sm border',
                      form.frameworks.includes(framework)
                        ? 'bg-purple-100 border-purple-300 text-purple-700'
                        : 'bg-gray-100 border-gray-300 text-gray-700 hover:bg-gray-200'
                    ]"
                  >
                    {{ framework }}
                  </button>
                </div>
                <Input 
                  v-model="newFramework"
                  @keyup.enter="addCustomFramework"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="Add custom framework and press Enter"
                />
                <div v-if="form.errors.frameworks" class="text-red-500 text-sm mt-1">{{ form.errors.frameworks }}</div>
              </div>

              <!-- Database Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Database Type</label>
                <Select v-model="form.database_type">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select database type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in databaseTypeOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.database_type" class="text-red-500 text-sm mt-1">{{ form.errors.database_type }}</div>
              </div>

              <!-- Architecture -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Architecture</label>
                <Select v-model="form.architecture">
                  <SelectTrigger class="w-full border-gray-300 text-black bg-white">
                    <SelectValue placeholder="Select architecture" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in architectureOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.architecture" class="text-red-500 text-sm mt-1">{{ form.errors.architecture }}</div>
              </div>

              <!-- External Integrations -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">External Integrations</label>
                <Textarea 
                  v-model="externalIntegrationsText"
                  @input="updateExternalIntegrations"
                  class="w-full border-gray-300 text-black bg-white" 
                  rows="3"
                  placeholder="Enter integrations separated by commas (e.g., Stripe, SendGrid, AWS S3)"
                />
                <p class="text-sm text-gray-500 mt-1">Separate multiple integrations with commas</p>
                <div v-if="form.errors.external_integrations" class="text-red-500 text-sm mt-1">{{ form.errors.external_integrations }}</div>
              </div>
            </div>
          </div>

          <!-- Team Tab -->
              <div v-if="activeTab === 'team'" class="space-y-6">
                <!-- Select developers -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Assign Team Members</label>
                  <select
                      v-model="form.developers_ids"
                      multiple
                      class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-3 min-h-[120px]"
                      >
                      <template v-if="props.developers?.length">
                        <option v-for="developer in props.developers"
                          :key="developer.id"
                          :value="developer.id"
                          class="text-black p-2"
                        >
                        {{ developer.name }} ({{ developer.email }})
                        </option>
                      </template>
                      <option v-else disabled>No developers available</option>
                  </select>
                  <p class="text-sm text-gray-500 mt-1">Hold Ctrl (or Cmd on Mac) to select multiple developers</p>
                  <div v-if="form.errors.developers_ids" class="text-red-500 text-sm mt-1">{{ form.errors.developers_ids }}</div>
                </div>

                <!-- Create Client User Section -->
                <div class="border-t border-gray-200 pt-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Create Client User</h3>
                  <p class="text-sm text-gray-600 mb-4">Create a client user who can only view project progress and task status.</p>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Client Name -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Client Name *</label>
                      <Input 
                        v-model="form.client_name" 
                        class="w-full border-gray-300 text-black bg-white" 
                        placeholder="Enter client name"
                      />
                      <div v-if="form.errors.client_name" class="text-red-500 text-sm mt-1">{{ form.errors.client_name }}</div>
                    </div>

                    <!-- Client Email -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Client Email *</label>
                      <Input 
                        v-model="form.client_email" 
                        type="email"
                        class="w-full border-gray-300 text-black bg-white" 
                        placeholder="Enter client email"
                      />
                      <div v-if="form.errors.client_email" class="text-red-500 text-sm mt-1">{{ form.errors.client_email }}</div>
                    </div>

                    <!-- Client Company -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Company (Optional)</label>
                      <Input 
                        v-model="form.client_company" 
                        class="w-full border-gray-300 text-black bg-white" 
                        placeholder="Enter company name"
                      />
                      <div v-if="form.errors.client_company" class="text-red-500 text-sm mt-1">{{ form.errors.client_company }}</div>
                    </div>

                    <!-- Client Password -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Password (Optional)</label>
                      <Input 
                        v-model="form.client_password" 
                        type="password"
                        class="w-full border-gray-300 text-black bg-white" 
                        placeholder="Leave empty for auto-generated password"
                      />
                      <p class="text-xs text-gray-500 mt-1">If left empty, a secure password will be generated automatically</p>
                      <div v-if="form.errors.client_password" class="text-red-500 text-sm mt-1">{{ form.errors.client_password }}</div>
                    </div>
                  </div>

                  <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                      <strong>Client Permissions:</strong> View projects, tasks, and progress reports. Cannot create, edit, or delete items.
                    </p>
                  </div>
                </div>
              </div>

            <!-- Budget & Resources Tab -->
            <div v-if="activeTab === 'budget'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Project Owner -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Project Owner</label>
                  <Input 
                    v-model="form.project_owner" 
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="Enter project owner name"
                  />
                  <div v-if="form.errors.project_owner" class="text-red-500 text-sm mt-1">{{ form.errors.project_owner }}</div>
                </div>

                <!-- Product Owner -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Product Owner</label>
                  <Input 
                    v-model="form.product_owner" 
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="Enter product owner name"
                  />
                  <div v-if="form.errors.product_owner" class="text-red-500 text-sm mt-1">{{ form.errors.product_owner }}</div>
                </div>

                <!-- Stakeholders -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Stakeholders</label>
                  <Textarea 
                    v-model="stakeholdersText"
                    @input="updateStakeholders"
                    class="w-full border-gray-300 text-black bg-white" 
                    rows="3"
                    placeholder="Enter stakeholders separated by commas"
                  />
                  <p class="text-sm text-gray-500 mt-1">Separate multiple stakeholders with commas</p>
                  <div v-if="form.errors.stakeholders" class="text-red-500 text-sm mt-1">{{ form.errors.stakeholders }}</div>
                </div>

                <!-- Milestones -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Milestones</label>
                  <Textarea 
                    v-model="milestonesText"
                    @input="updateMilestones"
                    class="w-full border-gray-300 text-black bg-white" 
                    rows="3"
                    placeholder="Enter milestones separated by commas"
                  />
                  <p class="text-sm text-gray-500 mt-1">Separate multiple milestones with commas</p>
                  <div v-if="form.errors.milestones" class="text-red-500 text-sm mt-1">{{ form.errors.milestones }}</div>
                </div>

                <!-- Estimated Velocity -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Velocity (points/sprint)</label>
                  <Input 
                    v-model="form.estimated_velocity" 
                    type="number"
                    min="1"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="e.g., 20"
                  />
                  <div v-if="form.errors.estimated_velocity" class="text-red-500 text-sm mt-1">{{ form.errors.estimated_velocity }}</div>
                </div>

                <!-- Current Sprint -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Current Sprint</label>
                  <Input 
                    v-model="form.current_sprint" 
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="e.g., Sprint 3"
                  />
                  <div v-if="form.errors.current_sprint" class="text-red-500 text-sm mt-1">{{ form.errors.current_sprint }}</div>
                </div>

                <!-- Estimated Budget -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Budget ($)</label>
                  <Input 
                    v-model="form.estimated_budget" 
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="0.00"
                  />
                  <div v-if="form.errors.estimated_budget" class="text-red-500 text-sm mt-1">{{ form.errors.estimated_budget }}</div>
                </div>

                <!-- Used Budget -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Used Budget ($)</label>
                  <Input 
                    v-model="form.used_budget" 
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="0.00"
                  />
                  <div v-if="form.errors.used_budget" class="text-red-500 text-sm mt-1">{{ form.errors.used_budget }}</div>
                </div>

                <!-- Assigned Resources -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Resources</label>
                  <Textarea 
                    v-model="assignedResourcesText"
                    @input="updateAssignedResources"
                    class="w-full border-gray-300 text-black bg-white" 
                    rows="3"
                    placeholder="Enter resources separated by commas (e.g., Servers, Licenses, Tools)"
                  />
                  <p class="text-sm text-gray-500 mt-1">Separate multiple resources with commas</p>
                  <div v-if="form.errors.assigned_resources" class="text-red-500 text-sm mt-1">{{ form.errors.assigned_resources }}</div>
                </div>
              </div>
            </div>

            <!-- Tracking Tab -->
            <div v-if="activeTab === 'tracking'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Progress Percentage -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Progress Percentage (%)</label>
                  <Input 
                    v-model="form.progress_percentage" 
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="0.00"
                  />
                  <div v-if="form.errors.progress_percentage" class="text-red-500 text-sm mt-1">{{ form.errors.progress_percentage }}</div>
                </div>

                <!-- Open Issues -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Open Issues</label>
                  <Input 
                    v-model="form.open_issues" 
                    type="number"
                    min="0"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="0"
                  />
                  <div v-if="form.errors.open_issues" class="text-red-500 text-sm mt-1">{{ form.errors.open_issues }}</div>
                </div>

                <!-- Identified Risks -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Identified Risks</label>
                  <Textarea 
                    v-model="identifiedRisksText"
                    @input="updateIdentifiedRisks"
                    class="w-full border-gray-300 text-black bg-white" 
                    rows="3"
                    placeholder="Enter risks separated by commas"
                  />
                  <p class="text-sm text-gray-500 mt-1">Separate multiple risks with commas</p>
                  <div v-if="form.errors.identified_risks" class="text-red-500 text-sm mt-1">{{ form.errors.identified_risks }}</div>
                </div>

                <!-- Documentation URL -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Documentation URL</label>
                  <Input 
                    v-model="form.documentation_url" 
                    type="url"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="https://docs.example.com"
                  />
                  <div v-if="form.errors.documentation_url" class="text-red-500 text-sm mt-1">{{ form.errors.documentation_url }}</div>
                </div>

                <!-- Repository URL -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Repository URL</label>
                  <Input 
                    v-model="form.repository_url" 
                    type="url"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="https://github.com/example/repo"
                  />
                  <div v-if="form.errors.repository_url" class="text-red-500 text-sm mt-1">{{ form.errors.repository_url }}</div>
                </div>

                <!-- Task Board URL -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Task Board URL</label>
                  <Input 
                    v-model="form.task_board_url" 
                    type="url"
                    class="w-full border-gray-300 text-black bg-white" 
                    placeholder="https://trello.com/board or https://jira.com/project"
                  />
                  <div v-if="form.errors.task_board_url" class="text-red-500 text-sm mt-1">{{ form.errors.task_board_url }}</div>
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
                @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                General
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
                @click="activeTab = 'technology'"
                :class="activeTab === 'technology' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Technology
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="activeTab = 'team'"
                :class="activeTab === 'team' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Team
              </Button>
              <Button 
                type="button" 
                variant="outline" 
                @click="activeTab = 'budget'"
                :class="activeTab === 'budget' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Budget
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
                {{ form.processing ? 'Creating...' : 'Create Project' }}
              </Button>
            </div>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
