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

const props = defineProps<DialogContentProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<DialogContentEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DialogPortal>
        <DialogOverlay
            class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
        >
            <DialogContent
                :force-mount="forwarded.forceMount || false"
                :trap-focus="forwarded.trapFocus || false"
                :disable-outside-pointer-events="forwarded.disableOutsidePointerEvents || false"
                :as-child="forwarded.asChild || false"
                :as="forwarded.as || 'div'"
                :class="
                    cn(
                        'relative z-50 my-8 grid w-full max-w-lg gap-4 border border-border bg-background p-6 shadow-lg duration-200 sm:rounded-lg md:w-full',
                        props.class,
                    )
                "
                @pointer-down-outside="
                    (event) => {
                        const originalEvent = event.detail.originalEvent;
                        const target = originalEvent.target as HTMLElement;
                        if (originalEvent.offsetX > target.clientWidth || originalEvent.offsetY > target.clientHeight) {
                            event.preventDefault();
                        }
                    }
                "
            >
                <slot />

                <DialogClose class="absolute right-3 top-3 rounded-md p-0.5 transition-colors hover:bg-secondary">
                    <X class="h-4 w-4" />
                    <span class="sr-only">Close</span>
                </DialogClose>
            </DialogContent>
        </DialogOverlay>
    </DialogPortal>
</template>
