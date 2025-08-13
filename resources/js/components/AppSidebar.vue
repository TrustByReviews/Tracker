<script setup lang="ts">
/**
 * AppSidebar Component
 * 
 * This component provides the main navigation sidebar for the application.
 * It dynamically renders different navigation menus based on user roles and permissions.
 * 
 * Features:
 * - Role-based navigation (Admin, Team Leader, Developer, QA)
 * - Dynamic menu generation based on user permissions
 * - Integration with TeamLeaderSidebar for team leader specific navigation
 * - Responsive design with collapsible functionality
 * - Permission-based access control
 * 
 * @author System
 * @version 1.0
 */

/**
 * Navigation item interface definition
 * Defines the structure for navigation menu items
 */
interface NavItem {
  title: string;      // Display text for the navigation item
  href: string;       // URL or route for the navigation item
  icon?: string | any; // Icon component for the navigation item
  external?: boolean; // Whether the link opens in a new tab
}

// Import required components and utilities
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
    import { BookOpen, Folder, LayoutGrid, Users, Calendar, CheckSquare, Shield, BarChart3, Activity, Bug, CheckCircle, Bell, TrendingUp } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePermissions } from '@/composables/usePermissions';
import { computed } from 'vue';
import TeamLeaderSidebar from './TeamLeaderSidebar.vue';

// Debug: Check if TeamLeaderSidebar component is properly imported
console.log('TeamLeaderSidebar component:', TeamLeaderSidebar);

// Initialize permissions composable
const { hasPermission, canAccessModule, user } = usePermissions();

// Debug: Log user data for development purposes
console.log('AppSidebar - User data:', user.value);
console.log('AppSidebar - User roles:', user.value?.roles);
console.log('AppSidebar - Is Team Leader:', user.value?.roles?.some(role => role.value === 'team_leader'));

/**
 * Computed property that generates navigation items based on user role
 * 
 * This function creates different navigation menus for different user types:
 * - QA users: Projects, Sprints, Tasks, Bugs, Finished Items, Notifications
 * - Team Leaders: Uses TeamLeaderSidebar component
 * - Admins: Full access to all modules including permissions management
 * - Developers: Standard development workflow items
 * 
 * @returns {NavItem[]} Array of navigation items for the current user
 */
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    // Extract user roles for role-based navigation logic
    const userRoles = user.value?.roles?.map(role => role.value) || [];
    const isQa = userRoles.includes('qa');
    const isAdmin = userRoles.includes('admin');
    const isTeamLeader = userRoles.includes('team_leader');
    const isDeveloper = userRoles.includes('developer');
    const isClient = userRoles.includes('client');

    // Client specific navigation - return early to avoid duplicates
    if (isClient) {
        // Proyectos del cliente
        items.push({
            title: 'Mis Proyectos',
            href: '/client/projects',
            icon: Folder,
        });
        
        // Tareas del cliente
        items.push({
            title: 'Mis Tareas',
            href: '/client/tasks',
            icon: CheckSquare,
        });
        
        // Sprints del cliente
        items.push({
            title: 'Mis Sprints',
            href: '/client/sprints',
            icon: Calendar,
        });
        
        // Sugerencias del cliente
        items.push({
            title: 'Mis Sugerencias',
            href: '/client/suggestions',
            icon: BookOpen,
        });
        
        return items;
    }

    // QA specific navigation - return early to avoid duplicates
    if (isQa) {
        // Projects (read-only access for QA)
        items.push({
            title: 'Projects',
            href: '/projects',
            icon: Folder,
        });
        
        // Sprints (read-only access for QA)
        items.push({
            title: 'Sprints',
            href: '/sprints',
            icon: Calendar,
        });
        
        // Tasks (QA can edit and review tasks)
        items.push({
            title: 'Tasks',
            href: '/tasks',
            icon: CheckSquare,
        });
        
        // Bugs (QA can edit and review bugs)
        items.push({
            title: 'Bugs',
            href: '/bugs',
            icon: Bug,
        });
        
        // Finished Items (QA can approve/reject completed work)
        items.push({
            title: 'Finished Items',
            href: '/qa/finished-items',
            icon: CheckCircle,
        });
        
        // Notifications (QA specific notifications)
        items.push({
            title: 'Notifications',
            href: '/qa/notifications',
            icon: Bell,
        });
        
        return items;
    }

    // Team Leader specific navigation - return empty array as we'll use TeamLeaderSidebar component
    if (isTeamLeader) {
        // Return empty array as we'll use TeamLeaderSidebar component
        return [];
    }

    // Admin specific navigation - give access to everything
    if (isAdmin) {
        // Projects management
        items.push({
            title: 'Project',
            href: '/projects',
            icon: Folder,
        });

        // User management
        items.push({
            title: 'User',
            href: '/users',
            icon: Users,
        });

        // Sprint management
        items.push({
            title: 'Sprints',
            href: '/sprints',
            icon: Calendar,
        });

        // Task management
        items.push({
            title: 'Tasks',
            href: '/tasks',
            icon: CheckSquare,
        });

        // Bug management
        items.push({
            title: 'Bugs',
            href: '/bugs',
            icon: Bug,
        });

        // Payments and reports (only for users with payment permissions)
        if (canAccessModule('payments')) {
            items.push({
                title: 'Payments & Reports',
                href: '/payments',
                icon: BarChart3,
            });
        }

        // Analytics Dashboard
        items.push({
            title: 'Analytics',
            href: '/analytics/projects',
            icon: TrendingUp,
        });

        // Developer Activity Dashboard
        items.push({
            title: 'Developer Activity',
            href: '/developer-activity',
            icon: Activity,
        });

        // Permissions management (only for users who can manage permissions)
        items.push({
            title: 'Permissions',
            href: '/permissions',
            icon: Shield,
        });

        return items;
    }

    // Developer default navigation (fallback when no explicit permissions are assigned)
    if (isDeveloper) {
        items.push({ title: 'Projects', href: '/projects', icon: Folder });
        items.push({ title: 'Sprints', href: '/sprints', icon: Calendar });
        items.push({ title: 'Tasks', href: '/tasks', icon: CheckSquare });
        items.push({ title: 'Bugs', href: '/bugs', icon: Bug });
        // Optional: Payments for visibility if module exists
        // items.push({ title: 'Payments & Reports', href: '/payments', icon: BarChart3 });
    }



    if (canAccessModule('projects') && !items.some(i => i.href === '/projects')) {
        items.push({ title: 'Projects', href: '/projects', icon: Folder });
    }

    if (canAccessModule('users') && !items.some(i => i.href === '/users')) {
        items.push({ title: 'Users', href: '/users', icon: Users });
    }

    if (canAccessModule('sprints') && !items.some(i => i.href === '/sprints')) {
        items.push({ title: 'Sprints', href: '/sprints', icon: Calendar });
    }

    if (canAccessModule('tasks') && !items.some(i => i.href === '/tasks')) {
        items.push({ title: 'Tasks', href: '/tasks', icon: CheckSquare });
    }

    if (canAccessModule('bugs') && !items.some(i => i.href === '/bugs')) {
        items.push({ title: 'Bugs', href: '/bugs', icon: Bug });
    }

    if (canAccessModule('payments') && !items.some(i => i.href === '/payments')) {
        items.push({ title: 'Payments & Reports', href: '/payments', icon: BarChart3 });
    }

    if (canAccessModule('developer-activity') && !items.some(i => i.href === '/developer-activity')) {
        items.push({ title: 'Developer Activity', href: '/developer-activity', icon: Activity });
    }

    if (canAccessModule('permissions') && !items.some(i => i.href === '/permissions')) {
        items.push({ title: 'Permissions', href: '/permissions', icon: Shield });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    // Removed external links for cleaner admin interface
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
                    <Link :href="user?.roles?.some(role => role.value === 'client') ? '/client/dashboard' : '/dashboard'">
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
