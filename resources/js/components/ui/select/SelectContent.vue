<script setup lang="ts">
import { inject, type Ref } from 'vue'

interface SelectContext {
  isOpen: Ref<boolean>
  selectedValue: Ref<string | number | undefined>
  select: (value: string | number) => void
  disabled: boolean
  toggle: () => void
}

const select = inject<SelectContext>('select')
</script>

<template>
  <div
    v-if="select?.isOpen.value"
    class="absolute z-50 w-full mt-1 bg-popover text-popover-foreground shadow-md border rounded-md max-h-60 overflow-auto"
  >
    <slot />
  </div>
</template> 