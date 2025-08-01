<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
// TEMPORARILY DISABLED - Login as User functionality
// import AdminLoggedAsUserBanner from '@/components/AdminLoggedAsUserBanner.vue';
import Toast from '@/components/ui/toast/Toast.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <!-- TEMPORARILY DISABLED - Login as User functionality -->
            <!-- <AdminLoggedAsUserBanner /> -->
            <AppSidebarHeader :breadcrumbs="breadcrumbs || []" />
            <slot name="header" />
            <slot />
        </AppContent>
    </AppShell>
    <Toast />
</template>
