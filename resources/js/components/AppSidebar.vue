<script setup lang="ts">
interface NavItem {
  title: string;
  href: string;
  icon?: string | any;
  external?: boolean;
}
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
// import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, Calendar, CheckSquare, Shield, BarChart3, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';

const { hasPermission, canAccessModule } = usePermissions();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    // Admin Dashboard (para admins)
    if (hasPermission('admin.dashboard')) {
        items.push({
            title: 'Admin Panel',
            href: '/admin/dashboard',
            icon: Settings,
        });
    }

    // Projects
    if (canAccessModule('projects')) {
        items.push({
            title: 'Project',
            href: '/projects',
            icon: Folder,
        });
    }

    // Users
    if (hasPermission('users.view')) {
        items.push({
            title: 'User',
            href: '/users',
            icon: Users,
        });
    }

    // Sprints
    if (canAccessModule('sprints')) {
        items.push({
            title: 'Sprints',
            href: '/sprints',
            icon: Calendar,
        });
    }

    // Tasks
    if (canAccessModule('tasks')) {
        items.push({
            title: 'Tasks',
            href: '/tasks',
            icon: CheckSquare,
        });
    }

    // Payments (for all users) - Main section
    if (hasPermission('payments.view')) {
        items.push({
            title: 'Payment Reports',
            href: '/payments/dashboard',
            icon: BarChart3,
        });
    }

    // Permissions (only for users who can manage permissions)
    if (hasPermission('permissions.manage')) {
        items.push({
            title: 'Permissions',
            href: '/permissions',
            icon: Shield,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    // Removed external links for cleaner admin interface
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    //     external: true,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits',
    //     icon: BookOpen,
    //     external: true,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
