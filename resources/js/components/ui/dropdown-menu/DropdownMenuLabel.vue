<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DropdownMenuLabel, useForwardProps, type DropdownMenuLabelProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DropdownMenuLabelProps & { class?: HTMLAttributes['class']; inset?: boolean }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <DropdownMenuLabel :as-child="forwardedProps.asChild || false" :as="forwardedProps.as || 'div'" :inset="forwardedProps.inset || false" :class="cn('px-2 py-1.5 text-sm font-semibold', inset && 'pl-8', props.class)">
        <slot />
    </DropdownMenuLabel>
</template>
