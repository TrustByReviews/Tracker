<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { PrimitiveProps } from 'reka-ui';
import { Primitive } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { Component } from 'vue';
import type { AsTag } from 'reka-ui';

const props = withDefaults(
    defineProps<
        Omit<PrimitiveProps, 'as'> & {
            size?: 'sm' | 'md';
            isActive?: boolean;
            class?: HTMLAttributes['class'];
            as?: AsTag | Component | undefined;
        }
    >(),
    {
        as: 'a',
        size: 'md',
    },
);
</script>

<template>
    <Primitive
        data-sidebar="menu-sub-button"
        :as="as || 'a'"
        :as-child="asChild || false"
        :data-size="size"
        :data-active="isActive"
        :class="
            cn(
                'flex h-7 min-w-0 -translate-x-px items-center gap-2 overflow-hidden rounded-md px-2 text-sidebar-foreground outline-none ring-sidebar-ring hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent active:text-sidebar-accent-foreground disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 [&>svg]:text-sidebar-accent-foreground',
                'data-[active=true]:bg-sidebar-accent data-[active=true]:text-sidebar-accent-foreground',
                size === 'sm' && 'text-xs',
                size === 'md' && 'text-sm',
                'group-data-[collapsible=icon]:hidden',
                props.class,
            )
        "
    >
        <slot />
    </Primitive>
</template>
