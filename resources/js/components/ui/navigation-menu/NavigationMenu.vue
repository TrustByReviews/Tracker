<script setup lang="ts">
import { cn } from '@/lib/utils'
import {
  NavigationMenuRoot,
  type NavigationMenuRootEmits,
  type NavigationMenuRootProps,
  useForwardPropsEmits,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import NavigationMenuViewport from './NavigationMenuViewport.vue'

const props = defineProps<NavigationMenuRootProps & { class?: HTMLAttributes['class'] }>()

const emits = defineEmits<NavigationMenuRootEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <NavigationMenuRoot
    :model-value="forwarded.modelValue || ''"
    :default-value="forwarded.defaultValue || ''"
    :dir="forwarded.dir || 'ltr'"
    :orientation="forwarded.orientation || 'horizontal'"
    :delay-duration="forwarded.delayDuration || 200"
    :skip-delay-duration="forwarded.skipDelayDuration || 300"
    :value="forwarded['value'] || ''"
    :as="forwarded.as || 'nav'"
    :class="cn('relative z-10 flex max-w-max flex-1 items-center justify-center', props.class)"
  >
    <slot />
    <NavigationMenuViewport />
  </NavigationMenuRoot>
</template>
