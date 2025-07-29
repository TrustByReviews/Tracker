<script setup lang="ts">
import { TooltipRoot, useForwardPropsEmits, type TooltipRootEmits, type TooltipRootProps } from 'reka-ui';

const props = defineProps<TooltipRootProps>();
const emits = defineEmits<TooltipRootEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <TooltipRoot :default-open="forwarded.defaultOpen || false" :open="forwarded.open || false" :delay-duration="forwarded.delayDuration || 700" :disable-hoverable-content="forwarded.disableHoverableContent || false" :disable-closing-trigger="forwarded.disableClosingTrigger || false" :disabled="forwarded.disabled || false" :ignore-non-keyboard-focus="forwarded.ignoreNonKeyboardFocus || false">
        <slot />
    </TooltipRoot>
</template>
