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
        data-sidebar="group-label"
        :as="as || 'div'"
        :as-child="asChild || false"
        :class="
            cn(
                'flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opa] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0',
                'group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0',
                props.class,
            )
        "
    >
        <slot />
    </Primitive>
</template>
