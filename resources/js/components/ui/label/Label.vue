<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  htmlFor?: string
  class?: string
}

const props = defineProps<Props>()

const classes = computed(() => {
  return cn(
    'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
    props.class
  )
})
</script>

<template>
  <label v-bind="htmlFor ? { for: htmlFor } : {}" :class="classes">
    <slot />
  </label>
</template>
