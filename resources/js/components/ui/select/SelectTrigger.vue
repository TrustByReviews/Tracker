<script setup lang="ts">
import { computed, inject, type Ref } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = defineProps<Props>()

interface SelectContext {
  isOpen: Ref<boolean>
  selectedValue: Ref<string | number | undefined>
  select: (value: string | number) => void
  disabled: boolean
  toggle: () => void
}

const select = inject<SelectContext>('select')

const classes = computed(() => {
  return cn(
    'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    {
      'ring-2 ring-ring': select?.isOpen.value
    },
    props.class
  )
})
</script>

<template>
  <button
    type="button"
    :class="classes"
    :disabled="select?.disabled || false"
    @click="select?.toggle()"
  >
    <slot />
    <Icon name="chevron-down" class="h-4 w-4 opacity-50" />
  </button>
</template> 