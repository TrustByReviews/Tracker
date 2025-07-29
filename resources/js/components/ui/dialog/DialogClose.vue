<script setup lang="ts">
import { DialogClose, type DialogCloseProps } from 'reka-ui';

const props = defineProps<DialogCloseProps>();
</script>

<template>
    <DialogClose :as-child="props.asChild || false" :as="props.as || 'button'">
        <slot />
    </DialogClose>
</template>
