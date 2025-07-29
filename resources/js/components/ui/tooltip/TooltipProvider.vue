<script setup lang="ts">
import { TooltipProvider, type TooltipProviderProps } from 'reka-ui';

const props = defineProps<TooltipProviderProps>();
</script>

<template>
    <TooltipProvider :delay-duration="props.delayDuration || 700" :skip-delay-duration="props.skipDelayDuration || 300" :disable-hoverable-content="props.disableHoverableContent || false" :disable-closing-trigger="props.disableClosingTrigger || false" :disabled="props.disabled || false" :ignore-non-keyboard-focus="props.ignoreNonKeyboardFocus || false">
        <slot />
    </TooltipProvider>
</template>
