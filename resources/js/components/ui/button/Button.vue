<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { Component } from 'vue';
import type { AsTag } from 'reka-ui';
import { buttonVariants, type ButtonVariants } from '.';

interface Props extends Omit<PrimitiveProps, 'as'> {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    as?: AsTag | Component | undefined;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
});
</script>

<template>
    <Primitive 
        :as="as || 'button'" 
        :as-child="asChild || false" 
        :class="cn(buttonVariants({ variant, size }), props.class)"
    >
        <slot />
    </Primitive>
</template>
