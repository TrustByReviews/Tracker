<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { PrimitiveProps } from 'reka-ui';
import { Primitive } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { Component } from 'vue';
import type { AsTag } from 'reka-ui';

const props = defineProps<
    Omit<PrimitiveProps, 'as'> & {
        class?: HTMLAttributes['class'];
        as?: AsTag | Component | undefined;
    }
>();
</script>

<template>
    <Primitive
        data-sidebar="group-action"
        :as="as || 'button'"
        :as-child="asChild || false"
        :class="
            cn(
                'absolute right-3 top-3.5 flex aspect-square w-5 items-center justify-center rounded-md p-0 text-sidebar-foreground outline-none ring-sidebar-ring transition-transform hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0',
                'after:absolute after:-inset-2 after:md:hidden',
                'group-data-[collapsible=icon]:hidden',
                props.class,
            )
        "
    >
        <slot />
    </Primitive>
</template>
