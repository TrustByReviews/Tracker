<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DialogDescription, useForwardProps, type DialogDescriptionProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DialogDescriptionProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <DialogDescription :as-child="forwardedProps.asChild || false" :as="forwardedProps.as || 'p'" :class="cn('text-sm text-muted-foreground', props.class)">
        <slot />
    </DialogDescription>
</template>
