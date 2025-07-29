<script setup lang="ts">
import { AvatarFallback, type AvatarFallbackProps } from 'reka-ui';

const props = defineProps<AvatarFallbackProps>();
</script>

<template>
    <AvatarFallback :delay-ms="props.delayMs || 600" :as-child="props.asChild || false" :as="props.as || 'span'">
        <slot />
    </AvatarFallback>
</template>
