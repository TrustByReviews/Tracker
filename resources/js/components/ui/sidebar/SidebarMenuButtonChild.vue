<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { Component } from 'vue';
import type { AsTag } from 'reka-ui';
import { sidebarMenuButtonVariants, type SidebarMenuButtonVariants } from '.';

export interface SidebarMenuButtonProps extends Omit<PrimitiveProps, 'as'> {
    variant?: SidebarMenuButtonVariants['variant'];
    size?: SidebarMenuButtonVariants['size'];
    isActive?: boolean;
    class?: HTMLAttributes['class'];
    as?: AsTag | Component | undefined;
}

const props = withDefaults(defineProps<SidebarMenuButtonProps>(), {
    as: 'button',
    variant: 'default',
    size: 'default',
});
</script>

<template>
    <Primitive
        data-sidebar="menu-button"
        :data-size="size"
        :data-active="isActive"
        :class="cn(sidebarMenuButtonVariants({ variant, size }), props.class)"
        :as="as || 'button'"
        :as-child="asChild || false"
        v-bind="$attrs"
    >
        <slot />
    </Primitive>
</template>
