<script setup lang="ts">
import { DropdownMenuRadioGroup, useForwardPropsEmits, type DropdownMenuRadioGroupEmits, type DropdownMenuRadioGroupProps } from 'reka-ui';

const props = defineProps<DropdownMenuRadioGroupProps>();
const emits = defineEmits<DropdownMenuRadioGroupEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <DropdownMenuRadioGroup :model-value="forwarded.modelValue || ''" :as-child="forwarded.asChild || false" :as="forwarded.as || 'div'">
        <slot />
    </DropdownMenuRadioGroup>
</template>
