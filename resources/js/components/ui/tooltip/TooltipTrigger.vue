<script setup lang="ts">
import { TooltipTrigger, type TooltipTriggerProps } from 'reka-ui';

const props = defineProps<TooltipTriggerProps>();
</script>

<template>
    <TooltipTrigger :as-child="props.asChild || false" :as="props.as || 'button'">
        <slot />
    </TooltipTrigger>
</template>
