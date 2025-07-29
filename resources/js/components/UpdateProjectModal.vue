<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm, router } from '@inertiajs/vue3';
import { conditionalClass } from '@/lib/utils';
import type { Project } from '@/types'

interface Developer {
  id: string;
  name: string;
  email: string;
  roles?: any[];
}

const props = defineProps<{
  project: Project;
  developers: Developer[];
}>();

const open = ref(false);

const form = useForm({
  name: '',
  description: '',
  status: '',
  developers_id: [] as string[],
});

watch(open, (isOpen) => {
  if (isOpen) {
    form.name = props.project.name;
    form.description = props.project.description;
    form.status = props.project.status;
    form.developers_id = props.developers.map((dev) => dev.id);
  }
});

const submit = () => {
  form.put(`/projects/${props.project.id}`, {
    onSuccess: () => {
      form.reset();
      open.value = false;
      router.reload();
    },
  });
};

const getRoleName = (developer: Developer) => {
  return developer.roles?.[0]?.name || 'Developer';
};
</script>

<template>
  <div>
    <Button @click="open = true" variant="outline" size="sm">
      <Icon name="edit" class="h-4 w-4 mr-2" />
      Update Project
    </Button>

    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Update Project</DialogTitle>
        </DialogHeader>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Name -->
          <div class="space-y-2">
            <Label for="name">Name</Label>
                        <Input
              id="name"
              v-model="form.name"
              placeholder="Enter project name"
              :class="conditionalClass('', form.errors.name, 'border-red-500')"
            />
            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <Label for="description">Description</Label>
                        <Input
              id="description"
              v-model="form.description"
              placeholder="Enter project description"
              :class="conditionalClass('', form.errors.description, 'border-red-500')"
            />
            <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <!-- Status -->
          <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select v-model="form.status">
              <SelectTrigger :class="conditionalClass('', form.errors.status, 'border-red-500')">
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="paused">Paused</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.status" class="text-sm text-red-600">{{ form.errors.status }}</p>
          </div>

          <!-- Developers -->
          <div class="space-y-2">
            <Label>Team Members</Label>
            <div class="space-y-2 max-h-40 overflow-y-auto border rounded-md p-2">
              <div
                v-for="developer in developers"
                :key="developer.id"
                class="flex items-center space-x-2"
              >
                <input
                  type="checkbox"
                  :id="`dev-${developer.id}`"
                  :value="developer.id"
                  v-model="form.developers_id"
                  class="rounded border-gray-300"
                />
                <label :for="`dev-${developer.id}`" class="flex-1 text-sm">
                  <div class="font-medium">{{ developer.name }}</div>
                  <div class="text-gray-500 text-xs">{{ developer.email }}</div>
                  <div class="text-gray-400 text-xs">{{ getRoleName(developer) }}</div>
                </label>
              </div>
            </div>
            <p v-if="form.errors.developers_id" class="text-sm text-red-600">{{ form.errors.developers_id }}</p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="open = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              <span v-if="form.processing">Updating...</span>
              <span v-else>Update Project</span>
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>