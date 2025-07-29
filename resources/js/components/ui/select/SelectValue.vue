<script setup lang="ts">
import { inject, type Ref } from 'vue'

interface Props {
  placeholder?: string
}

withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option'
})

interface SelectContext {
  isOpen: Ref<boolean>
  selectedValue: Ref<string | number | undefined>
  select: (value: string | number) => void
  disabled: boolean
}

const select = inject<SelectContext>('select')
</script>

<template>
  <span v-if="select?.selectedValue.value" class="block truncate">
    <slot :value="select.selectedValue.value">
      {{ select.selectedValue.value }}
    </slot>
  </span>
  <span v-else class="block truncate text-muted-foreground">
    {{ placeholder }}
  </span>
</template> 