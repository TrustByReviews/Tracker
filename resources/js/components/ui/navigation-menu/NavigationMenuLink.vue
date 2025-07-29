<script setup lang="ts">
import {
  NavigationMenuLink,
  type NavigationMenuLinkEmits,
  type NavigationMenuLinkProps,
  useForwardPropsEmits,
} from 'reka-ui'

const props = defineProps<NavigationMenuLinkProps>()
const emits = defineEmits<NavigationMenuLinkEmits>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <NavigationMenuLink :active="forwarded.active || false" :as-child="forwarded.asChild || false" :as="forwarded.as || 'a'">
    <slot />
  </NavigationMenuLink>
</template>
