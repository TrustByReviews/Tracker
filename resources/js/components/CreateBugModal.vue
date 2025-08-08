<template>
  <div>
    <Button @click="open = true" class="border-red-500 text-white bg-red-500 hover:bg-red-600">
      Report Bug
    </Button>

    <BugCreateModal
      v-if="open"
      :projects="projectsForModal"
      :sprints="sprintsForModal"
      :developers="developers || []"
      :current-project="currentProject"
      :current-sprint="currentSprint"
      @close="open = false"
      @created="handleCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Button from './ui/button/Button.vue'
import BugCreateModal from './BugCreateModal.vue'

const props = defineProps({
  projects: {
    type: Array,
    default: () => []
  },
  sprints: {
    type: Array,
    default: () => []
  },
  developers: {
    type: Array,
    default: () => []
  },
  // Context props for auto-selection
  currentProject: {
    type: Object,
    default: null
  },
  currentSprint: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['created'])

const open = ref(false)

// Prepare data for modal
const projectsForModal = computed(() => props.projects)
const sprintsForModal = computed(() => props.sprints)

const handleCreated = () => {
  open.value = false
  emit('created')
}
</script>
