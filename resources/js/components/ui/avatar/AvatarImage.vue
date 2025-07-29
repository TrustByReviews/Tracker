<script setup lang="ts">
import { AvatarImage, type AvatarImageProps } from 'reka-ui';

const props = defineProps<AvatarImageProps>();
</script>

<template>
    <AvatarImage :src="props.src || ''" :referrer-policy="props.referrerPolicy || 'no-referrer'" :as-child="props.asChild || false" :as="props.as || 'img'" class="h-full w-full object-cover" />
</template>
