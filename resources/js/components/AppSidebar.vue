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
import { BookOpen, Folder, LayoutGrid, Users, Calendar, CheckSquare, Shield, BarChart3, Key, Activity, Bug, CheckCircle, Bell } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';
import TeamLeaderSidebar from './TeamLeaderSidebar.vue';

// Debug: Check if component is imported
console.log('TeamLeaderSidebar component:', TeamLeaderSidebar);

const { hasPermission, canAccessModule, user } = usePermissions();

// Debug: Log user data
console.log('AppSidebar - User data:', user.value);
console.log('AppSidebar - User roles:', user.value?.roles);
console.log('AppSidebar - Is Team Leader:', user.value?.roles?.some(role => role.value === 'team_leader'));

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    // Get user roles to check if user is QA, Admin, or Team Leader
    const userRolees = user.value?.roles?.map(role => role.value) || [];
    const isQa = userRolees.includes('qa');
    const isAdmin = userRolees.includes('admin');
    const isTeamLeader = userRolees.includes('team_leader');

    // QA specific navigation - return early to avoid duplicates
    if (isQa) {
        // Projects (read-only for QA)
        items.push({
            title: 'Projects',
            href: '/projects',
            icon: Folder,
        });
        
        // Sprints (read-only for QA)
        items.push({
            title: 'Sprints',
            href: '/sprints',
            icon: Calendar,
        });
        
        // Tasks (QA can edit tasks)
        items.push({
            title: 'Tasks',
            href: '/tasks',
            icon: CheckSquare,
        });
        
        // Bugs (QA can edit bugs)
        items.push({
            title: 'Bugs',
            href: '/bugs',
            icon: Bug,
        });
        
        // Finished Items (QA can approve/reject)
        items.push({
            title: 'Finished Items',
            href: '/qa/finished-items',
            icon: CheckCircle,
        });
        
        // Notifications
        items.push({
            title: 'Notifications',
            href: '/qa/notifications',
            icon: Bell,
        });
        
        return items;
    }

    // Team Leader specific navigation - return early to avoid duplicates
    if (isTeamLeader) {
        // Return empty array as we'll use TeamLeaderSidebar component
        return [];
    }

    // Admin specific navigation - give access to everything
    if (isAdmin) {
        // Simultaneous Tasks Permissions (for admins)
        items.push({
            title: 'Permissions Tasks',
            href: '/admin/simultaneous-tasks',
            icon: Key,
        });

        // Projects
        items.push({
            title: 'Project',
            href: '/projects',
            icon: Folder,
        });

        // Users
        items.push({
            title: 'User',
            href: '/users',
            icon: Users,
        });

        // Sprints
        items.push({
            title: 'Sprints',
            href: '/sprints',
            icon: Calendar,
        });

        // Tasks
        items.push({
            title: 'Tasks',
            href: '/tasks',
            icon: CheckSquare,
        });

        // Bugs
        items.push({
            title: 'Bugs',
            href: '/bugs',
            icon: Bug,
        });

        // Payments (for all users) - Main section
        items.push({
            title: 'Payments & Reports',
            href: '/payments',
            icon: BarChart3,
        });

        // Developer Activity Dashboard
        items.push({
            title: 'Developer Activity',
            href: '/developer-activity',
            icon: Activity,
        });

        // Permissions (only for users who can manage permissions)
        items.push({
            title: 'Permissions',
            href: '/permissions',
            icon: Shield,
        });

        return items;
    }

    // For non-admin, non-qa users, check permissions
    // Simultaneous Tasks Permissions (for admins)
    if (hasPermission('admin.permissions')) {
        items.push({
            title: 'Permissions Tasks',
            href: '/admin/simultaneous-tasks',
            icon: Key,
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

    // Bugs
    if (canAccessModule('bugs')) {
        items.push({
            title: 'Bugs',
            href: '/bugs',
            icon: Bug,
        });
    }

    // Payments (for all users) - Main section
    if (hasPermission('payments.view')) {
        items.push({
            title: 'Payments & Reports',
            href: '/payments',
            icon: BarChart3,
        });
    }

    // Developer Activity Dashboard
    if (hasPermission('developer-activity.view')) {
        items.push({
            title: 'Developer Activity',
            href: '/developer-activity',
            icon: Activity,
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
    <!-- Team Leader Sidebar -->
    
    <!-- Team Leader Sidebar -->
    <TeamLeaderSidebar v-if="user?.roles?.some(role => role.value === 'team_leader')" />
    
    <!-- Default Sidebar for other users -->
    <Sidebar v-else collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <Link href="/dashboard">
                        <SidebarMenuButton size="lg">
                            <AppLogo />
                        </SidebarMenuButton>
                    </Link>
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
