<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronRight } from 'lucide-vue-next';
import { DropdownMenuSubTrigger, useForwardProps, type DropdownMenuSubTriggerProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DropdownMenuSubTriggerProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <DropdownMenuSubTrigger
        :disabled="forwardedProps.disabled || false"
        :text-value="forwardedProps.textValue || ''"
        :as-child="forwardedProps.asChild || false"
        :as="forwardedProps.as || 'div'"
        :class="
            cn(
                'flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none focus:bg-accent data-[state=open]:bg-accent',
                props.class,
            )
        "
    >
        <slot />
        <ChevronRight class="ml-auto h-4 w-4" />
    </DropdownMenuSubTrigger>
</template>
