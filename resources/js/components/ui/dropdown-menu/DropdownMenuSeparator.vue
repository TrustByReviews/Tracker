<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DropdownMenuSeparator, type DropdownMenuSeparatorProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<
    DropdownMenuSeparatorProps & {
        class?: HTMLAttributes['class'];
    }
>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});
</script>

<template>
    <DropdownMenuSeparator :as-child="delegatedProps.asChild || false" :as="delegatedProps.as || 'div'" :class="cn('-mx-1 my-1 h-px bg-muted', props.class)" />
</template>
