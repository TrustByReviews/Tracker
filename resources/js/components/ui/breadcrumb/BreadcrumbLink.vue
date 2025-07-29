<script lang="ts" setup>
import { cn } from '@/lib/utils';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { Component } from 'vue';
import type { AsTag } from 'reka-ui';

const props = withDefaults(defineProps<Omit<PrimitiveProps, 'as'> & { 
    class?: HTMLAttributes['class'];
    as?: AsTag | Component | undefined;
}>(), {
    as: 'a',
});
</script>

<template>
    <Primitive 
        :as="as || 'a'" 
        :as-child="asChild || false" 
        :class="cn('transition-colors hover:text-foreground', props.class)"
    >
        <slot />
    </Primitive>
</template>
