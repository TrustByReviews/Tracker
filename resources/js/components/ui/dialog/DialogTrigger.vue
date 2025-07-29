<script setup lang="ts">
import { DialogTrigger, type DialogTriggerProps } from 'reka-ui';

const props = defineProps<DialogTriggerProps>();
</script>

<template>
    <DialogTrigger :as-child="props.asChild || false" :as="props.as || 'button'">
        <slot />
    </DialogTrigger>
</template>
