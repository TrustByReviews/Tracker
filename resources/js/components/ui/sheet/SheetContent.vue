<script setup lang="ts">
import { cn } from '@/lib/utils';
import { X } from 'lucide-vue-next';
import {
    DialogClose,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    useForwardPropsEmits,
    type DialogContentEmits,
    type DialogContentProps,
} from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';
import { sheetVariants, type SheetVariants } from '.';

interface SheetContentProps extends DialogContentProps {
    class?: HTMLAttributes['class'];
    side?: SheetVariants['side'];
}

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<SheetContentProps>();

const emits = defineEmits<DialogContentEmits>();

const delegatedProps = computed(() => {
    const { class: _, side, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DialogPortal>
        <DialogOverlay
            class="fixed inset-0 z-50 bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
        />
        <DialogContent :class="cn(sheetVariants({ side }), props.class)" :force-mount="forwarded.forceMount || false" :trap-focus="forwarded.trapFocus || false" :disable-outside-pointer-events="forwarded.disableOutsidePointerEvents || false" :as-child="forwarded.asChild || false" :as="forwarded.as || 'div'">
            <slot />

            <DialogClose
                class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-secondary"
            >
                <X class="h-4 w-4 text-muted-foreground" />
            </DialogClose>
        </DialogContent>
    </DialogPortal>
</template>
