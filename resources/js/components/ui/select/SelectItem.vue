<script setup lang="ts">
import { computed, inject, type Ref } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  value: string | number
  disabled?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false
})

interface SelectContext {
  isOpen: Ref<boolean>
  selectedValue: Ref<string | number | undefined>
  select: (value: string | number) => void
  disabled: boolean
}

const select = inject<SelectContext>('select')

const isSelected = computed(() => {
  return select?.selectedValue.value === props.value
})

const classes = computed(() => {
  return cn(
    'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
    {
      'bg-accent text-accent-foreground': isSelected.value,
      'opacity-50 cursor-not-allowed': props.disabled
    },
    props.class
  )
})

const handleClick = () => {
  if (!props.disabled && select) {
    select.select(props.value)
  }
}
</script>

<template>
  <div
    :class="classes"
    @click="handleClick"
  >
    <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Icon
        v-if="isSelected"
        name="check"
        class="h-4 w-4"
      />
    </span>
    <slot />
  </div>
</template> 