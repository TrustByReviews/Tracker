<script setup lang="ts">
import { ref, defineProps } from 'vue';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
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

const form = useForm({
  name: '',
  description: '',
  developers_ids: [] as number[],
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

</script>

<template>
  <div>
    <Button @click="open = true" class="border-black bg-black text-white hover:bg-gray-400 hover:border-white hover:text-black">
      New Project
    </Button>

    <Dialog :open="open" @update:open="open = $event">
      <DialogContent class="max-w-md p-6 bg-white rounded-lg shadow-lg">
        <!-- Title -->
        <DialogTitle class="text-lg font-bold mb-4 text-gray-800">New Project</DialogTitle>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <Input v-model="form.name" class="w-full border-gray-300 text-black bg-white" />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <Input v-model="form.description" class="w-full border-gray-300 text-black bg-white" />
          </div>

          <!-- Select developers -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Developers</label>
            <select
                v-model="form.developers_ids"
                multiple
                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2"
                >
                <template v-if="props.developers?.length">
                  <option v-for="developer in props.developers"
                    :key="developer.id"
                    :value="developer.id"
                    class="text-black"
                  >
                  {{ developer.name }} ( {{ developer.email }} )
                  </option>
                </template>
                <option v-else disabled>No developers available</option>
                </select>
          </div>

          <!-- Botones -->
          <div class="flex justify-end gap-2">
            <Button type="button" variant="secondary" @click="open = false" class="bg-gray-200 text-gray-800 hover:bg-gray-300">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing" class="bg-blue-500 text-white hover:bg-blue-600">
              Save
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
