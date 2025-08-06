<template>
  <select
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    @input="$emit('change', $event)"
    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  >
    <slot />
  </select>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  }
})

defineEmits(['update:modelValue', 'change'])
</script> 