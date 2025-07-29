<script setup lang="ts">
import { ref } from 'vue';
import Button from './ui/button/Button.vue';
import Dialog from './ui/dialog/Dialog.vue';
import DialogContent from './ui/dialog/DialogContent.vue';
import DialogTitle from './ui/dialog/DialogTitle.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { useForm, router } from '@inertiajs/vue3';
import { Project } from '@/types'

const props = defineProps<{
    project: Project,
}>()

const open = ref(false);

const formatToISO = (date: Date) => {
  return date.toISOString().split('T')[0];
}

const today = new Date();
const tomorrow = new Date(today);
tomorrow.setDate(today.getDate() + 14); // Default to 2 weeks

const todayFormatted = formatToISO(today);
const tomorrowFormatted = formatToISO(tomorrow);

const form = useForm({
  name: '',
  goal: '',
  start_date: todayFormatted,
  end_date: tomorrowFormatted,
  project_id: props.project.id,
});

const submit = () => {
  if (form.start_date && todayFormatted && form.start_date < todayFormatted) {
    alert('Start date cannot be before today.');
    return;
  }

  if (form.end_date && form.start_date && form.end_date < form.start_date) {
    alert('End date cannot be before start date.');
    return;
  }

  form.post('/sprints', {
    onSuccess: () => {
      form.reset();
      open.value = false;
      // Redirect to the project page to show the new sprint
      router.visit(`/projects/${props.project.id}`);
    },
    onError: (errors) => {
      console.error('Sprint creation errors:', errors);
    }
  });
}

const resetForm = () => {
  form.reset();
  form.start_date = todayFormatted;
  form.end_date = tomorrowFormatted;
  form.project_id = props.project.id;
}
</script>

<template>
  <div>
    <!-- button -->
    <Button @click="open = true" class="border-blue-500 text-white bg-blue-500 hover:bg-blue-600">
      Create Sprint
    </Button>

    <!-- Modal -->
    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="max-w-lg p-6 bg-white rounded-lg shadow-lg">
        <!-- title -->
        <DialogTitle class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6m-6 0V9a4 4 0 00-4-4H5a4 4 0 00-4 4v6a4 4 0 004 4h2" />
          </svg>
          Create New Sprint
        </DialogTitle>

        <!-- form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Name -->
          <div>
            <Label for="name" class="block text-sm font-medium text-gray-700 mb-2">Sprint Name</Label>
            <Input 
              id="name"
              v-model="form.name" 
              placeholder="e.g., Sprint 1 - Foundation"
              class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500" 
            />
          </div>

          <!-- Goal -->
          <div>
            <Label for="goal" class="block text-sm font-medium text-gray-700 mb-2">Sprint Goal</Label>
            <Textarea 
              id="goal"
              v-model="form.goal" 
              placeholder="Describe the main objective of this sprint..."
              rows="3"
              class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500" 
            />
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Start Date -->
            <div>
              <Label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</Label>
              <Input
                id="start_date"
                :model-value="form.start_date || ''"
                @update:model-value="(value) => form.start_date = String(value)"
                type="date"
                :min="todayFormatted || ''"
                class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <!-- End Date -->
            <div>
              <Label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</Label>
              <Input
                id="end_date"
                :model-value="form.end_date || ''"
                @update:model-value="(value) => form.end_date = String(value)"
                type="date"
                :min="form.start_date || ''"
                class="w-full border-gray-300 text-black bg-white focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Project Info -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">
              <strong>Project:</strong> {{ project.name }}
            </div>
          </div>

          <!-- Botones -->
          <div class="flex justify-end gap-3 pt-4">
            <Button
              type="button"
              variant="secondary"
              @click="open = false"
              class="bg-gray-200 text-gray-800 hover:bg-gray-300"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="form.processing"
              class="bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50"
            >
              <span v-if="form.processing">Creating...</span>
              <span v-else>Create Sprint</span>
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
