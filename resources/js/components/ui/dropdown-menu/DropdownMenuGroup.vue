<script setup lang="ts">
import { DropdownMenuGroup, type DropdownMenuGroupProps } from 'reka-ui';

const props = defineProps<DropdownMenuGroupProps>();
</script>

<template>
    <DropdownMenuGroup :as-child="props.asChild || false" :as="props.as || 'div'">
        <slot />
    </DropdownMenuGroup>
</template>
