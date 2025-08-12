<script setup lang="ts">
import { ref, defineProps, onMounted, watch } from 'vue';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

interface Sprint {
    id: string;
    name: string;
    goal: string;
    start_date: string;
    end_date: string;
    project_id: string;
    isCompleted?: boolean;
    hasRetrospective?: boolean;
}

const props = defineProps<{
    sprint: Sprint;
}>();

const open = ref(false);
const activeTab = ref('finish');
const includeRetrospective = ref(false);

// Estado de finalización del sprint
const finishStatus = ref({
    can_be_finished: true,
    total_tasks: 0,
    completed_tasks: 0,
    pending_tasks: 0,
    completion_percentage: 100,
    pending_tasks_list: []
});

const loadingFinishStatus = ref(false);

// Función para obtener el estado de finalización del sprint
const getFinishStatus = async () => {
    if (props.sprint.isCompleted) {
        return;
    }
    
    loadingFinishStatus.value = true;
    try {
        const response = await axios.get(`/sprints/${props.sprint.id}/finish-status`);
        finishStatus.value = response.data;
    } catch (error) {
        console.error('Error getting finish status:', error);
    } finally {
        loadingFinishStatus.value = false;
    }
};

// Variables para campos de texto personalizados
const newAchievement = ref('');
const newProblem = ref('');
const newAction = ref('');
const newLessonLearned = ref('');
const newImprovementArea = ref('');
const newTeamFeedback = ref('');
const newStakeholderFeedback = ref('');
const newProcessImprovement = ref('');
const newToolImprovement = ref('');
const newCommunicationImprovement = ref('');
const newTechnicalDebtAdded = ref('');
const newTechnicalDebtResolved = ref('');
const newKnowledgeShared = ref('');
const newSkillDeveloped = ref('');
const newMentoringSession = ref('');
const newSprintGoalAchieved = ref('');
const newSprintGoalPartiallyAchieved = ref('');
const newSprintGoalNotAchieved = ref('');
const newCeremonyEffectiveness = ref('');

const form = useForm({
  // Retrospectiva básica
  achievements: [] as string[],
  problems: [] as string[],
  actions_to_take: [] as string[],
  retrospective_notes: '',
  lessons_learned: [] as string[],
  improvement_areas: [] as string[],
  
  // Feedback
  team_feedback: [] as string[],
  stakeholder_feedback: [] as string[],
  team_satisfaction_score: 7.5,
  stakeholder_satisfaction_score: 7.0,
  
  // Mejoras
  process_improvements: [] as string[],
  tool_improvements: [] as string[],
  communication_improvements: [] as string[],
  
  // Deuda técnica
  technical_debt_added: [] as string[],
  technical_debt_resolved: [] as string[],
  
  // Conocimiento y habilidades
  knowledge_shared: [] as string[],
  skills_developed: [] as string[],
  mentoring_sessions: [] as string[],
  
  // Objetivos del sprint
  sprint_goals_achieved: [] as string[],
  sprint_goals_partially_achieved: [] as string[],
  sprint_goals_not_achieved: [] as string[],
  
  // Efectividad de ceremonias
  sprint_ceremony_effectiveness: [] as string[]
});

const submit = () => {
  if (includeRetrospective.value) {
    form.post(`/sprints/${props.sprint.id}/finish-with-retrospective`, {
      onSuccess: () => {
        form.reset();
        open.value = false;
        router.reload();
      },
    });
  } else {
    form.post(`/sprints/${props.sprint.id}/finish`, {
      onSuccess: () => {
        form.reset();
        open.value = false;
        router.reload();
      },
    });
  }
};

// Cargar estado de finalización cuando se abre el modal
watch(open, (newValue) => {
  if (newValue && !props.sprint.isCompleted) {
    getFinishStatus();
  }
});

// También cargar al montar si el modal ya está abierto
onMounted(() => {
  if (open.value && !props.sprint.isCompleted) {
    getFinishStatus();
  }
});

const resetForm = () => {
  form.reset();
  activeTab.value = 'finish';
  includeRetrospective.value = false;
  // Reset custom input fields
  newAchievement.value = '';
  newProblem.value = '';
  newAction.value = '';
  newLessonLearned.value = '';
  newImprovementArea.value = '';
  newTeamFeedback.value = '';
  newStakeholderFeedback.value = '';
  newProcessImprovement.value = '';
  newToolImprovement.value = '';
  newCommunicationImprovement.value = '';
  newTechnicalDebtAdded.value = '';
  newTechnicalDebtResolved.value = '';
  newKnowledgeShared.value = '';
  newSkillDeveloped.value = '';
  newMentoringSession.value = '';
  newSprintGoalAchieved.value = '';
  newSprintGoalPartiallyAchieved.value = '';
  newSprintGoalNotAchieved.value = '';
  newCeremonyEffectiveness.value = '';
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

const addCustomItem = (array: string[], newItem: string, resetRef: any) => {
  if (newItem.trim() && !array.includes(newItem.trim())) {
    array.push(newItem.trim());
    resetRef.value = '';
  }
};

// Common items for quick selection
const commonAchievements = [
  'All planned features completed',
  'Team velocity improved',
  'Bug resolution rate increased',
  'Code quality improved',
  'Team collaboration enhanced',
  'Process improvements implemented'
];

const commonProblems = [
  'Scope creep occurred',
  'Technical debt accumulated',
  'Communication issues',
  'Unclear requirements',
  'Resource constraints',
  'External dependencies delayed work'
];

const commonActions = [
  'Improve estimation accuracy',
  'Enhance communication channels',
  'Implement better code review process',
  'Reduce technical debt',
  'Improve requirement gathering',
  'Strengthen team collaboration'
];

const commonLessonsLearned = [
  'Better planning reduces last-minute issues',
  'Regular communication prevents misunderstandings',
  'Code reviews improve quality significantly',
  'Technical debt should be addressed early',
  'Clear requirements lead to better outcomes',
  'Team collaboration is key to success'
];

const commonImprovementAreas = [
  'Estimation accuracy',
  'Communication processes',
  'Code review practices',
  'Technical debt management',
  'Requirement gathering',
  'Team collaboration'
];

const commonTeamFeedback = [
  'Good team collaboration',
  'Clear communication channels',
  'Supportive environment',
  'Opportunities for learning',
  'Work-life balance maintained',
  'Recognition of achievements'
];

const commonStakeholderFeedback = [
  'Deliverables met expectations',
  'Timely communication',
  'Quality standards maintained',
  'Responsive to feedback',
  'Professional approach',
  'Clear progress reporting'
];

const commonProcessImprovements = [
  'Streamline code review process',
  'Improve sprint planning',
  'Enhance daily standups',
  'Better backlog grooming',
  'Optimize deployment process',
  'Improve testing procedures'
];

const commonToolImprovements = [
  'Better project management tools',
  'Improved development environment',
  'Enhanced testing tools',
  'Better monitoring solutions',
  'Improved CI/CD pipeline',
  'Better documentation tools'
];

const commonCommunicationImprovements = [
  'Regular status updates',
  'Clear escalation procedures',
  'Better stakeholder communication',
  'Improved team meetings',
  'Enhanced documentation',
  'Better feedback channels'
];

const commonTechnicalDebt = [
  'Code refactoring needed',
  'Test coverage improvement',
  'Documentation updates',
  'Performance optimization',
  'Security improvements',
  'Architecture updates'
];

const commonKnowledgeShared = [
  'New technology implementation',
  'Best practices documentation',
  'Code review guidelines',
  'Testing strategies',
  'Deployment procedures',
  'Troubleshooting techniques'
];

const commonSkillsDeveloped = [
  'New programming language',
  'Framework expertise',
  'Testing methodologies',
  'DevOps practices',
  'Project management',
  'Communication skills'
];

const commonMentoringSessions = [
  'Code review mentoring',
  'Architecture discussions',
  'Testing best practices',
  'Performance optimization',
  'Security practices',
  'Team collaboration'
];

const commonSprintGoals = [
  'Feature implementation completed',
  'Bug fixes delivered',
  'Performance improvements',
  'Security enhancements',
  'Documentation updated',
  'Testing completed'
];

const commonCeremonyEffectiveness = [
  'Sprint planning was effective',
  'Daily standups were productive',
  'Sprint review was valuable',
  'Retrospective was insightful',
  'Backlog grooming was helpful',
  'Sprint demo was successful'
];

</script>

<template>
  <div>
    <Button @click="open = true" variant="outline" class="border-green-500 text-green-600 hover:bg-green-50">
      {{ props.sprint.isCompleted ? 'Add Retrospective' : 'Finish Sprint' }}
    </Button>

    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 bg-white rounded-lg shadow-lg">
        <!-- Title -->
        <DialogTitle class="text-lg sm:text-xl font-bold mb-4 sm:mb-6 text-gray-800">
          {{ props.sprint.isCompleted ? 'Add Sprint Retrospective' : 'Finish Sprint' }}
        </DialogTitle>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4 sm:mb-6">
          <nav class="-mb-px flex space-x-2 sm:space-x-4 overflow-x-auto">
            <button
              @click="activeTab = 'finish'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'finish'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Finish Sprint
            </button>
            <button
              v-if="!props.sprint.isCompleted"
              @click="activeTab = 'retrospective'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'retrospective'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Include Retrospective
            </button>
            <button
              v-if="activeTab === 'retrospective'"
              @click="activeTab = 'feedback'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'feedback'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Feedback & Satisfaction
            </button>
            <button
              v-if="activeTab === 'feedback'"
              @click="activeTab = 'improvements'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'improvements'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Improvements
            </button>
            <button
              v-if="activeTab === 'improvements'"
              @click="activeTab = 'goals'"
              :class="[
                'py-1 sm:py-2 px-1 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap',
                activeTab === 'goals'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Sprint Goals
            </button>
          </nav>
        </div>

        <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
          <!-- Finish Sprint Tab -->
          <div v-if="activeTab === 'finish'" class="space-y-4 sm:space-y-6">
            <div class="bg-blue-50 p-4 rounded-lg">
              <h3 class="text-lg font-semibold text-blue-800 mb-2">Finish Sprint: {{ props.sprint.name }}</h3>
              <p class="text-blue-700 mb-4">
                This will mark the sprint as completed and set the actual end date to today.
              </p>
              
              <!-- Estado de tareas -->
              <div v-if="!props.sprint.isCompleted" class="mb-4">
                <div v-if="loadingFinishStatus" class="text-sm text-blue-600">
                  Loading task status...
                </div>
                <div v-else>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-blue-800">Task Completion:</span>
                    <span class="text-sm text-blue-600">{{ finishStatus.completion_percentage }}%</span>
                  </div>
                  <div class="w-full bg-blue-200 rounded-full h-2 mb-2">
                    <div 
                      class="h-2 rounded-full transition-all"
                      :class="finishStatus.completion_percentage >= 100 ? 'bg-green-600' : 'bg-blue-600'"
                      :style="{ width: `${finishStatus.completion_percentage}%` }"
                    ></div>
                  </div>
                  <div class="text-xs text-blue-600">
                    {{ finishStatus.completed_tasks }} of {{ finishStatus.total_tasks }} tasks completed
                  </div>
                  
                  <!-- Tareas pendientes -->
                  <div v-if="finishStatus.pending_tasks > 0" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center mb-2">
                      <svg class="w-4 h-4 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      <span class="text-sm font-medium text-red-800">
                        {{ finishStatus.pending_tasks }} task(s) still pending
                      </span>
                    </div>
                    <div class="text-xs text-red-700">
                      <div v-for="task in finishStatus.pending_tasks_list.slice(0, 3)" :key="task.id" class="mb-1">
                        • {{ task.name }} ({{ task.status }}) - {{ task.assigned_to }}
                      </div>
                      <div v-if="finishStatus.pending_tasks_list.length > 3" class="text-red-600">
                        ... and {{ finishStatus.pending_tasks_list.length - 3 }} more
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-if="!props.sprint.isCompleted && finishStatus.can_be_finished" class="flex items-center space-x-2 mb-4">
                <input 
                  type="checkbox" 
                  id="includeRetrospective" 
                  v-model="includeRetrospective"
                  class="rounded border-gray-300"
                />
                <label for="includeRetrospective" class="text-sm font-medium text-blue-800">
                  Include retrospective data
                </label>
              </div>
              
              <div v-if="includeRetrospective" class="text-sm text-blue-600">
                You'll be able to add retrospective information in the next steps.
              </div>
            </div>
          </div>

          <!-- Retrospective Tab -->
          <div v-if="activeTab === 'retrospective'" class="space-y-4 sm:space-y-6">
            <!-- Achievements -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Achievements</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="achievement in commonAchievements" 
                    :key="achievement"
                    @click="toggleArrayItem(form.achievements, achievement)"
                    :class="form.achievements.includes(achievement) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ achievement }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newAchievement" 
                    placeholder="Add custom achievement"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.achievements, newAchievement, newAchievement)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Problems -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Problems Identified</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="problem in commonProblems" 
                    :key="problem"
                    @click="toggleArrayItem(form.problems, problem)"
                    :class="form.problems.includes(problem) ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ problem }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newProblem" 
                    placeholder="Add custom problem"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.problems, newProblem, newProblem)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Actions to Take -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Actions to Take</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="action in commonActions" 
                    :key="action"
                    @click="toggleArrayItem(form.actions_to_take, action)"
                    :class="form.actions_to_take.includes(action) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ action }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newAction" 
                    placeholder="Add custom action"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.actions_to_take, newAction, newAction)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Lessons Learned -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Lessons Learned</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="lesson in commonLessonsLearned" 
                    :key="lesson"
                    @click="toggleArrayItem(form.lessons_learned, lesson)"
                    :class="form.lessons_learned.includes(lesson) ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ lesson }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newLessonLearned" 
                    placeholder="Add custom lesson learned"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.lessons_learned, newLessonLearned, newLessonLearned)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Improvement Areas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Areas for Improvement</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="area in commonImprovementAreas" 
                    :key="area"
                    @click="toggleArrayItem(form.improvement_areas, area)"
                    :class="form.improvement_areas.includes(area) ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ area }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newImprovementArea" 
                    placeholder="Add custom improvement area"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.improvement_areas, newImprovementArea, newImprovementArea)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Retrospective Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Retrospective Notes</label>
              <Textarea 
                v-model="form.retrospective_notes" 
                class="w-full border-gray-300 text-black bg-white" 
                rows="4"
                placeholder="Additional notes about the sprint retrospective..."
              />
            </div>
          </div>

          <!-- Feedback Tab -->
          <div v-if="activeTab === 'feedback'" class="space-y-4 sm:space-y-6">
            <!-- Team Feedback -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Team Feedback</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="feedback in commonTeamFeedback" 
                    :key="feedback"
                    @click="toggleArrayItem(form.team_feedback, feedback)"
                    :class="form.team_feedback.includes(feedback) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ feedback }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newTeamFeedback" 
                    placeholder="Add custom team feedback"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.team_feedback, newTeamFeedback, newTeamFeedback)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Stakeholder Feedback -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Stakeholder Feedback</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="feedback in commonStakeholderFeedback" 
                    :key="feedback"
                    @click="toggleArrayItem(form.stakeholder_feedback, feedback)"
                    :class="form.stakeholder_feedback.includes(feedback) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ feedback }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newStakeholderFeedback" 
                    placeholder="Add custom stakeholder feedback"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.stakeholder_feedback, newStakeholderFeedback, newStakeholderFeedback)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Satisfaction Scores -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Team Satisfaction Score (1-10)</label>
                <Input 
                  v-model="form.team_satisfaction_score" 
                  type="number"
                  min="1"
                  max="10"
                  step="0.1"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="7.5"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stakeholder Satisfaction Score (1-10)</label>
                <Input 
                  v-model="form.stakeholder_satisfaction_score" 
                  type="number"
                  min="1"
                  max="10"
                  step="0.1"
                  class="w-full border-gray-300 text-black bg-white" 
                  placeholder="7.0"
                />
              </div>
            </div>
          </div>

          <!-- Improvements Tab -->
          <div v-if="activeTab === 'improvements'" class="space-y-4 sm:space-y-6">
            <!-- Process Improvements -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Process Improvements</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="improvement in commonProcessImprovements" 
                    :key="improvement"
                    @click="toggleArrayItem(form.process_improvements, improvement)"
                    :class="form.process_improvements.includes(improvement) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ improvement }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newProcessImprovement" 
                    placeholder="Add custom process improvement"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.process_improvements, newProcessImprovement, newProcessImprovement)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Tool Improvements -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tool Improvements</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="improvement in commonToolImprovements" 
                    :key="improvement"
                    @click="toggleArrayItem(form.tool_improvements, improvement)"
                    :class="form.tool_improvements.includes(improvement) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ improvement }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newToolImprovement" 
                    placeholder="Add custom tool improvement"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.tool_improvements, newToolImprovement, newToolImprovement)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Communication Improvements -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Communication Improvements</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="improvement in commonCommunicationImprovements" 
                    :key="improvement"
                    @click="toggleArrayItem(form.communication_improvements, improvement)"
                    :class="form.communication_improvements.includes(improvement) ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ improvement }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newCommunicationImprovement" 
                    placeholder="Add custom communication improvement"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.communication_improvements, newCommunicationImprovement, newCommunicationImprovement)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
              </div>
            </div>

            <!-- Technical Debt -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Technical Debt Added</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="debt in commonTechnicalDebt" 
                      :key="debt"
                      @click="toggleArrayItem(form.technical_debt_added, debt)"
                      :class="form.technical_debt_added.includes(debt) ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80"
                    >
                      {{ debt }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newTechnicalDebtAdded" 
                      placeholder="Add custom technical debt"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.technical_debt_added, newTechnicalDebtAdded, newTechnicalDebtAdded)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Technical Debt Resolved</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="debt in commonTechnicalDebt" 
                      :key="debt"
                      @click="toggleArrayItem(form.technical_debt_resolved, debt)"
                      :class="form.technical_debt_resolved.includes(debt) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80"
                    >
                      {{ debt }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newTechnicalDebtResolved" 
                      placeholder="Add custom resolved debt"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.technical_debt_resolved, newTechnicalDebtResolved, newTechnicalDebtResolved)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Knowledge and Skills -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Knowledge Shared</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="knowledge in commonKnowledgeShared" 
                      :key="knowledge"
                      @click="toggleArrayItem(form.knowledge_shared, knowledge)"
                      :class="form.knowledge_shared.includes(knowledge) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80 text-xs"
                    >
                      {{ knowledge }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newKnowledgeShared" 
                      placeholder="Add custom knowledge"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.knowledge_shared, newKnowledgeShared, newKnowledgeShared)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Skills Developed</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="skill in commonSkillsDeveloped" 
                      :key="skill"
                      @click="toggleArrayItem(form.skills_developed, skill)"
                      :class="form.skills_developed.includes(skill) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80 text-xs"
                    >
                      {{ skill }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newSkillDeveloped" 
                      placeholder="Add custom skill"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.skills_developed, newSkillDeveloped, newSkillDeveloped)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mentoring Sessions</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="session in commonMentoringSessions" 
                      :key="session"
                      @click="toggleArrayItem(form.mentoring_sessions, session)"
                      :class="form.mentoring_sessions.includes(session) ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80 text-xs"
                    >
                      {{ session }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newMentoringSession" 
                      placeholder="Add custom session"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.mentoring_sessions, newMentoringSession, newMentoringSession)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Goals Tab -->
          <div v-if="activeTab === 'goals'" class="space-y-4 sm:space-y-6">
            <!-- Sprint Goals -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Goals Achieved</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="goal in commonSprintGoals" 
                      :key="goal"
                      @click="toggleArrayItem(form.sprint_goals_achieved, goal)"
                      :class="form.sprint_goals_achieved.includes(goal) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80 text-xs"
                    >
                      {{ goal }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newSprintGoalAchieved" 
                      placeholder="Add custom achieved goal"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.sprint_goals_achieved, newSprintGoalAchieved, newSprintGoalAchieved)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Goals Partially Achieved</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="goal in commonSprintGoals" 
                      :key="goal"
                      @click="toggleArrayItem(form.sprint_goals_partially_achieved, goal)"
                      :class="form.sprint_goals_partially_achieved.includes(goal) ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80 text-xs"
                    >
                      {{ goal }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newSprintGoalPartiallyAchieved" 
                      placeholder="Add custom partially achieved goal"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.sprint_goals_partially_achieved, newSprintGoalPartiallyAchieved, newSprintGoalPartiallyAchieved)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Goals Not Achieved</label>
                <div class="space-y-2">
                  <div class="flex flex-wrap gap-2">
                    <Badge 
                      v-for="goal in commonSprintGoals" 
                      :key="goal"
                      @click="toggleArrayItem(form.sprint_goals_not_achieved, goal)"
                      :class="form.sprint_goals_not_achieved.includes(goal) ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'"
                      class="cursor-pointer hover:opacity-80 text-xs"
                    >
                      {{ goal }}
                    </Badge>
                  </div>
                  <div class="flex gap-2">
                    <Input 
                      v-model="newSprintGoalNotAchieved" 
                      placeholder="Add custom not achieved goal"
                      class="flex-1"
                    />
                    <Button type="button" @click="addCustomItem(form.sprint_goals_not_achieved, newSprintGoalNotAchieved, newSprintGoalNotAchieved)" variant="outline" size="sm">
                      Add
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ceremony Effectiveness -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sprint Ceremony Effectiveness</label>
              <div class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="ceremony in commonCeremonyEffectiveness" 
                    :key="ceremony"
                    @click="toggleArrayItem(form.sprint_ceremony_effectiveness, ceremony)"
                    :class="form.sprint_ceremony_effectiveness.includes(ceremony) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600'"
                    class="cursor-pointer hover:opacity-80"
                  >
                    {{ ceremony }}
                  </Badge>
                </div>
                <div class="flex gap-2">
                  <Input 
                    v-model="newCeremonyEffectiveness" 
                    placeholder="Add custom ceremony effectiveness"
                    class="flex-1"
                  />
                  <Button type="button" @click="addCustomItem(form.sprint_ceremony_effectiveness, newCeremonyEffectiveness, newCeremonyEffectiveness)" variant="outline" size="sm">
                    Add
                  </Button>
                </div>
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
                @click="activeTab = 'finish'"
                :class="activeTab === 'finish' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Finish
              </Button>
              <Button 
                v-if="!props.sprint.isCompleted"
                type="button" 
                variant="outline" 
                @click="activeTab = 'retrospective'"
                :class="activeTab === 'retrospective' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Retrospective
              </Button>
              <Button 
                v-if="activeTab === 'retrospective'"
                type="button" 
                variant="outline" 
                @click="activeTab = 'feedback'"
                :class="activeTab === 'feedback' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Feedback
              </Button>
              <Button 
                v-if="activeTab === 'feedback'"
                type="button" 
                variant="outline" 
                @click="activeTab = 'improvements'"
                :class="activeTab === 'improvements' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Improvements
              </Button>
              <Button 
                v-if="activeTab === 'improvements'"
                type="button" 
                variant="outline" 
                @click="activeTab = 'goals'"
                :class="activeTab === 'goals' ? 'bg-blue-50 border-blue-200' : ''"
                class="whitespace-nowrap text-xs sm:text-sm px-2 sm:px-3"
              >
                Goals
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
                             <Button 
                 type="submit" 
                 :disabled="form.processing || (!props.sprint.isCompleted && !finishStatus.can_be_finished)" 
                 :class="[
                   'text-xs sm:text-sm px-2 sm:px-3',
                   form.processing || (!props.sprint.isCompleted && !finishStatus.can_be_finished)
                     ? 'bg-gray-400 text-white cursor-not-allowed'
                     : 'bg-green-500 text-white hover:bg-green-600'
                 ]"
               >
                 {{ 
                   form.processing 
                     ? 'Processing...' 
                     : (!props.sprint.isCompleted && !finishStatus.can_be_finished)
                       ? 'Cannot Finish (Tasks Pending)'
                       : (props.sprint.isCompleted ? 'Add Retrospective' : 'Finish Sprint')
                 }}
               </Button>
            </div>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
