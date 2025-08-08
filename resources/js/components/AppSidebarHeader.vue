<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import NotificationBell from '@/components/NotificationBell.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';

interface SharedData {
  auth: {
    user: any;
  };
  [key: string]: any;
}

const page = usePage<SharedData>();
const user = page.props.auth?.user;

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs || []" />
            </template>
        </div>
        
        <!-- Campana de notificaciones para QA y Team Leader en esquina superior derecha -->
        <div v-if="user?.roles?.some((role: any) => role.value === 'qa' || role.value === 'team_leader')" class="flex items-center">
            <NotificationBell />
        </div>
    </header>
</template>
