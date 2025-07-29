<script setup lang="ts">
import type { CollapsibleRootEmits, CollapsibleRootProps } from 'reka-ui';
import { CollapsibleRoot, useForwardPropsEmits } from 'reka-ui';

const props = defineProps<CollapsibleRootProps>();
const emits = defineEmits<CollapsibleRootEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <CollapsibleRoot v-slot="{ open }" :default-open="forwarded.defaultOpen || false" :open="forwarded.open || false" :disabled="forwarded.disabled || false" :unmount-on-hide="forwarded.unmountOnHide || false" :as-child="forwarded.asChild || false" :as="forwarded.as || 'div'">
        <slot :open="open" />
    </CollapsibleRoot>
</template>
