<script setup lang="ts">
import { cn } from '@/lib/utils';
import {
    DropdownMenuContent,
    DropdownMenuPortal,
    useForwardPropsEmits,
    type DropdownMenuContentEmits,
    type DropdownMenuContentProps,
} from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<DropdownMenuContentProps & { class?: HTMLAttributes['class'] }>(), {
    sideOffset: 4,
});
const emits = defineEmits<DropdownMenuContentEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DropdownMenuPortal>
        <DropdownMenuContent
            :force-mount="forwarded.forceMount || false"
            :as-child="forwarded.asChild || false"
            :as="forwarded.as || 'div'"
            :side="forwarded.side || 'bottom'"
            :side-offset="forwarded.sideOffset || 4"
            :align="forwarded.align || 'center'"
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
                    'z-50 min-w-32 overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
                    props.class,
                )
            "
        >
            <slot />
        </DropdownMenuContent>
    </DropdownMenuPortal>
</template>
