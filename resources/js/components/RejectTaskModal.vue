<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'

interface Props {
  task: any
  isOpen: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:isOpen': [value: boolean]
}>()

const rejectionReason = ref('')
const isSubmitting = ref(false)

const closeModal = () => {
  emit('update:isOpen', false)
  rejectionReason.value = ''
}

const rejectTask = async () => {
  if (!rejectionReason.value.trim()) {
    return
  }

  isSubmitting.value = true

  try {
    await router.post(`/team-leader/tasks/${props.task.id}/reject`, {
      reason: rejectionReason.value
    }, {
      preserveState: true,
      preserveScroll: true
    })
    
    closeModal()
  } catch (error) {
    console.error('Error rejecting task:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Dialog :open="isOpen" @update:open="closeModal">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Reject Task</DialogTitle>
      </DialogHeader>
      
      <div class="space-y-4">
        <div>
          <h4 class="font-medium text-gray-900 dark:text-white mb-2">
            {{ task?.name }}
          </h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ task?.description }}
          </p>
        </div>

        <div>
          <Label for="rejection-reason">Rejection Reason</Label>
          <Textarea
            id="rejection-reason"
            v-model="rejectionReason"
            placeholder="Please provide a detailed reason for rejecting this task..."
            class="mt-1"
            :rows="4"
          />
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="closeModal" :disabled="isSubmitting">
          Cancel
        </Button>
        <Button 
          variant="destructive" 
          @click="rejectTask" 
          :disabled="!rejectionReason.trim() || isSubmitting"
        >
          <span v-if="isSubmitting">Rejecting...</span>
          <span v-else>Reject Task</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template> 