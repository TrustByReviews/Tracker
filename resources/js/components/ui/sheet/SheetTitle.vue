<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DialogTitle, type DialogTitleProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DialogTitleProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});
</script>

<template>
    <DialogTitle :class="cn('text-lg font-semibold text-foreground', props.class)" :as-child="delegatedProps.asChild || false" :as="delegatedProps.as || 'h2'">
        <slot />
    </DialogTitle>
</template>
