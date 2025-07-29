<script setup lang="ts">
import { NavigationMenuItem, type NavigationMenuItemProps } from 'reka-ui'

const props = defineProps<NavigationMenuItemProps>()
</script>

<template>
  <NavigationMenuItem :value="props.value || ''" :as-child="props.asChild || false" :as="props.as || 'li'">
    <slot />
  </NavigationMenuItem>
</template>
