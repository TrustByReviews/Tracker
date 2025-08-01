<script setup lang="ts">
import { cn } from '@/lib/utils';
import { DropdownMenuSubContent, useForwardPropsEmits, type DropdownMenuSubContentEmits, type DropdownMenuSubContentProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DropdownMenuSubContentProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<DropdownMenuSubContentEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DropdownMenuSubContent
        :force-mount="forwarded.forceMount || false"
        :as-child="forwarded.asChild || false"
        :as="forwarded.as || 'div'"
        :side-offset="forwarded.sideOffset || 0"
        :align-offset="forwarded.alignOffset || 0"
        :avoid-collisions="forwarded.avoidCollisions || true"
        :collision-boundary="forwarded.collisionBoundary || []"
        :collision-padding="forwarded.collisionPadding || 0"
        :arrow-padding="forwarded.arrowPadding || 0"
        :sticky="forwarded.sticky || 'partial'"
        :hide-when-detached="forwarded.hideWhenDetached || false"
        :update-position-strategy="forwarded.updatePositionStrategy || 'optimized'"
        :loop="forwarded['loop'] || false"
        :class="
            cn(
                'z-50 min-w-32 overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-lg data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
                props.class,
            )
        "
    >
        <slot />
    </DropdownMenuSubContent>
</template>
