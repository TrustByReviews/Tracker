<script setup lang="ts">
import { DropdownMenuTrigger, useForwardProps, type DropdownMenuTriggerProps } from 'reka-ui';

const props = defineProps<DropdownMenuTriggerProps>();

const forwardedProps = useForwardProps(props);
</script>

<template>
    <DropdownMenuTrigger 
        class="outline-none" 
        :disabled="forwardedProps.disabled || false"
        :as-child="forwardedProps.asChild || false"
        :as="forwardedProps.as || 'button'"
    >
        <slot />
    </DropdownMenuTrigger>
</template>
