<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useForm, router } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { conditionalClass } from '@/lib/utils';

interface Sprint {
    id: string,
    name: string
    goal: string
    start_date: string
    end_date: string
}

const props = defineProps<{
    sprint: Sprint
    project_id: string
}>();

const open = ref(false);

const form = useForm({
    name: '',
    goal: '',
    start_date: '',
    end_date: ''
});

watch(open, (isOpen) => {
    if (isOpen) {
        form.name = props.sprint.name;
        form.goal = props.sprint.goal;
        form.start_date = props.sprint.start_date?.split('T')[0] || '';
        form.end_date = props.sprint.end_date?.split('T')[0] || '';
    }
});

const formatToISO = (date: Date) => {
  return date.toISOString().split('T')[0];
};

const today = new Date();
const todayFormatted = formatToISO(today);

const submit = () => {
    if (form.start_date < (todayFormatted || '')) {
        alert('Start date cannot be before today.');
        return;
    }

    if (form.end_date < form.start_date) {
        alert('End date cannot be before start date.');
        return;
    }

    form.put(`/projects/${props.project_id}/sprints/${props.sprint.id}`, {
        onSuccess: () => {
            form.reset();
            open.value = false;
            router.reload();
        },
    });
};
</script>

<template>
  <div>
    <Button @click="open = true" variant="outline" size="sm">
      <Icon name="edit" class="h-4 w-4 mr-2" />
      Update Sprint
    </Button>
    
    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Update Sprint</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Name -->
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input 
              id="name"
              v-model="form.name" 
              placeholder="Enter sprint name"
              :class="conditionalClass('', form.errors.name, 'border-red-500')"
            />
            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Goal -->
          <div class="space-y-2">
            <Label for="goal">Goal</Label>
            <Input 
              id="goal"
              v-model="form.goal" 
              placeholder="Enter sprint goal"
              :class="conditionalClass('', form.errors.goal, 'border-red-500')"
            />
            <p v-if="form.errors.goal" class="text-sm text-red-600">{{ form.errors.goal }}</p>
          </div>

          <!-- Start Date -->
          <div class="space-y-2">
            <Label for="start_date">Start Date</Label>
            <Input 
              id="start_date"
              v-model="form.start_date" 
              type="date"
              :min="todayFormatted"
              :class="conditionalClass('', form.errors.start_date, 'border-red-500')"
            />
            <p v-if="form.errors.start_date" class="text-sm text-red-600">{{ form.errors.start_date }}</p>
          </div>

          <!-- End Date -->
          <div class="space-y-2">
            <Label for="end_date">End Date</Label>
            <Input 
              id="end_date"
              v-model="form.end_date" 
              type="date"
              :min="form.start_date"
              :class="conditionalClass('', form.errors.end_date, 'border-red-500')"
            />
            <p v-if="form.errors.end_date" class="text-sm text-red-600">{{ form.errors.end_date }}</p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="open = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              <span v-if="form.processing">Updating...</span>
              <span v-else>Update Sprint</span>
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
