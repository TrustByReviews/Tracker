<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DialogDescription, type DialogDescriptionProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DialogDescriptionProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});
</script>

<template>
    <DialogDescription :class="cn('text-sm text-muted-foreground', props.class)" :as-child="delegatedProps.asChild || false" :as="delegatedProps.as || 'p'">
        <slot />
    </DialogDescription>
</template>
