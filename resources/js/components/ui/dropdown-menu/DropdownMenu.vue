<script setup lang="ts">
import { DropdownMenuRoot, useForwardPropsEmits, type DropdownMenuRootEmits, type DropdownMenuRootProps } from 'reka-ui';

const props = defineProps<DropdownMenuRootProps>();
const emits = defineEmits<DropdownMenuRootEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <DropdownMenuRoot :default-open="forwarded.defaultOpen || false" :open="forwarded.open || false" :dir="forwarded.dir || 'ltr'" :modal="forwarded.modal || true">
        <slot />
    </DropdownMenuRoot>
</template>
