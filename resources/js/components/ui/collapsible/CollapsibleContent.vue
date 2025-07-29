<script setup lang="ts">
import { CollapsibleContent, type CollapsibleContentProps } from 'reka-ui';

const props = defineProps<CollapsibleContentProps>();
</script>

<template>
    <CollapsibleContent
        :force-mount="props.forceMount || false"
        :as-child="props.asChild || false"
        :as="props.as || 'div'"
        class="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down overflow-hidden transition-all"
    >
        <slot />
    </CollapsibleContent>
</template>
