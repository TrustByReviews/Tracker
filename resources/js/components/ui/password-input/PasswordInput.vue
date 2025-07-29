<script setup lang="ts">
import { ref, computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'
import { cn } from '@/lib/utils'

interface Props {
  modelValue?: string
  placeholder?: string
  disabled?: boolean
  class?: string
  id?: string
  required?: boolean
  autocomplete?: string
  tabindex?: number
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  required: false
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const showPassword = ref(false)

const inputType = computed(() => {
  return showPassword.value ? 'text' : 'password'
})

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}

const classes = computed(() => {
  return cn(
    'pr-10', // Add padding for the eye icon
    props.class
  )
})
</script>

<template>
  <div class="relative">
    <Input
      :id="id || ''"
      :type="inputType"
      :value="modelValue || ''"
      :placeholder="placeholder || ''"
      :disabled="disabled || false"
      :required="required || false"
      :autocomplete="autocomplete || ''"
      :tabindex="tabindex || 0"
      :class="classes"
      @input="handleInput"
    />
    <Button
      type="button"
      variant="ghost"
      size="sm"
      class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
      @click="togglePassword"
      :disabled="disabled"
    >
      <Icon 
        :name="showPassword ? 'eye-off' : 'eye'" 
        class="h-4 w-4 text-gray-500 hover:text-gray-700" 
      />
    </Button>
  </div>
</template> 