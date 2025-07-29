<script setup lang="ts">
import { CollapsibleTrigger, type CollapsibleTriggerProps } from 'reka-ui';

const props = defineProps<CollapsibleTriggerProps>();
</script>

<template>
    <CollapsibleTrigger :as-child="props.asChild || false" :as="props.as || 'button'">
        <slot />
    </CollapsibleTrigger>
</template>
