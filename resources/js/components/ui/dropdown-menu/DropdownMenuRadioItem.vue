<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Circle } from 'lucide-vue-next';
import {
    DropdownMenuItemIndicator,
    DropdownMenuRadioItem,
    useForwardPropsEmits,
    type DropdownMenuRadioItemEmits,
    type DropdownMenuRadioItemProps,
} from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DropdownMenuRadioItemProps & { class?: HTMLAttributes['class'] }>();

const emits = defineEmits<DropdownMenuRadioItemEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DropdownMenuRadioItem
        :value="forwarded.value || ''"
        :disabled="forwarded.disabled || false"
        :text-value="forwarded.textValue || ''"
        :as-child="forwarded.asChild || false"
        :as="forwarded.as || 'div'"
        :class="
            cn(
                'relative flex cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
                props.class,
            )
        "
    >
        <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
            <DropdownMenuItemIndicator>
                <Circle class="h-2 w-2 fill-current" />
            </DropdownMenuItemIndicator>
        </span>
        <slot />
    </DropdownMenuRadioItem>
</template>
