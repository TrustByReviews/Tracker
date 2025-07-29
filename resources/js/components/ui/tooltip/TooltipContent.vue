<script setup lang="ts">
import { cn } from '@/lib/utils';
import { TooltipContent, TooltipPortal, useForwardPropsEmits, type TooltipContentEmits, type TooltipContentProps } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = withDefaults(defineProps<TooltipContentProps & { class?: HTMLAttributes['class'] }>(), {
    sideOffset: 4,
});

const emits = defineEmits<TooltipContentEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <TooltipPortal>
        <TooltipContent
            :force-mount="forwarded.forceMount || false"
            :aria-label="forwarded.ariaLabel || ''"
            :as-child="forwarded.asChild || false"
            :as="forwarded.as || 'div'"
            :side="forwarded.side || 'top'"
            :align="forwarded.align || 'center'"
            :side-offset="forwarded.sideOffset || 4"
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
                    'z-50 overflow-hidden rounded-md border bg-popover px-3 py-1.5 text-sm text-popover-foreground shadow-md animate-in fade-in-0 zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
                    props.class,
                )
            "
        >
            <slot />
        </TooltipContent>
    </TooltipPortal>
</template>
