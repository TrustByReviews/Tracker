<script setup lang="ts">
import { computed, provide, ref, watch } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  modelValue?: string | number
  disabled?: boolean
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  placeholder: 'Select an option'
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const isOpen = ref(false)
const selectedValue = ref(props.modelValue)

const classes = computed(() => {
  return cn(
    'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    {
      'ring-2 ring-ring': isOpen.value
    }
  )
})

const toggle = () => {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

const select = (value: string | number) => {
  selectedValue.value = value
  emit('update:modelValue', value)
  isOpen.value = false
}

watch(() => props.modelValue, (newValue) => {
  selectedValue.value = newValue
})

provide('select', {
  isOpen,
  selectedValue,
  select,
  toggle,
  disabled: props.disabled
})
</script>

<template>
  <div class="relative">
    <button
      type="button"
      :class="classes"
      :disabled="disabled || false" @click="toggle"
    >
      <span v-if="selectedValue" class="block truncate">
        <slot name="value" :value="selectedValue">
          {{ selectedValue }}
        </slot>
      </span>
      <span v-else class="block truncate text-muted-foreground">
        {{ placeholder }}
      </span>
      <Icon name="chevron-down" class="h-4 w-4 opacity-50" />
    </button>
    
    <div
      v-if="isOpen"
      class="absolute z-50 w-full mt-1 bg-popover text-popover-foreground shadow-md border rounded-md"
    >
      <slot />
    </div>
  </div>
</template> 