<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DialogTitle, useForwardProps, type DialogTitleProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DialogTitleProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <DialogTitle :as-child="forwardedProps.asChild || false" :as="forwardedProps.as || 'h2'" :class="cn('text-lg font-semibold leading-none tracking-tight', props.class)">
        <slot />
    </DialogTitle>
</template>
