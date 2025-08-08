<script setup lang="ts">
interface NavItem {
  title: string;
  href: string;
  icon?: string | any;
  external?: boolean;
  badge?: string | undefined;
  badgeColor?: string;
}

import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { 
  Folder, 
  Calendar, 
  CheckSquare, 
  BarChart3, 
  Bug, 

  Bell
} from 'lucide-vue-next';
import AppLogoIcon from './AppLogoIcon.vue';

import { computed, ref, onMounted } from 'vue';
import axios from 'axios';



// State for notifications and statistics
const notifications = ref([]);
const stats = ref({
  pendingTasks: 0,
  qaApprovedTasks: 0,
  qaApprovedBugs: 0,
  activeProjects: 0,
  activeSprints: 0
});

// Load TL data
const loadTeamLeaderData = async () => {
  try {
    const [notificationsResponse, statsResponse] = await Promise.all([
      axios.get('/api/team-leader/notifications'),
      axios.get('/api/team-leader/stats')
    ]);
    
    notifications.value = notificationsResponse.data.notifications || [];
    stats.value = statsResponse.data.stats || stats.value;
  } catch (error) {
    console.error('Error loading TL data:', error);
  }
};

onMounted(() => {
  loadTeamLeaderData();
});

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    // TL Projects
    items.push({
        title: 'My Projects',
        href: '/team-leader/projects',
        icon: Folder,
        badge: stats.value.activeProjects > 0 ? stats.value.activeProjects.toString() : undefined,
        badgeColor: 'bg-blue-500'
    });

    // Active Sprints
    items.push({
        title: 'Active Sprints',
        href: '/team-leader/sprints',
        icon: Calendar,
        badge: stats.value.activeSprints > 0 ? stats.value.activeSprints.toString() : undefined,
        badgeColor: 'bg-green-500'
    });

    // Pending tasks for review
    items.push({
        title: 'Pending Tasks',
        href: '/team-leader/review/tasks',
        icon: CheckSquare,
        badge: stats.value.pendingTasks > 0 ? stats.value.pendingTasks.toString() : undefined,
        badgeColor: 'bg-orange-500'
    });

    // Pending bugs for review
    items.push({
        title: 'Pending Bugs',
        href: '/team-leader/review/bugs',
        icon: Bug,
        badge: stats.value.qaApprovedBugs > 0 ? stats.value.qaApprovedBugs.toString() : undefined,
        badgeColor: 'bg-red-500'
    });

    // Notifications
    items.push({
        title: 'Notifications',
        href: '/team-leader/notifications',
        icon: Bell,
        badge: notifications.value.length > 0 ? notifications.value.length.toString() : undefined,
        badgeColor: 'bg-purple-500'
    });

    // Reports and Analytics
    items.push({
        title: 'Reports',
        href: '/team-leader/reports',
        icon: BarChart3,
    });

    return items;
});


</script>

<template>
         <Sidebar class="border-r w-16" collapsible="icon" variant="inset">
                 <SidebarHeader class="border-b px-2 py-4">
             <Link href="/dashboard" class="block">
                 <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                     <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
                 </div>
             </Link>
         </SidebarHeader>
        
                 <SidebarContent class="px-2 py-3">
            <!-- TL main navigation -->
            <SidebarMenu>
                <SidebarMenuButton
                    v-for="item in mainNavItems"
                    :key="item.href"
                    :as-child="true"
                >
                    <Link :href="item.href" class="w-full">
                                                 <div class="flex items-center justify-between w-full">
                             <div class="flex items-center">
                                 <component :is="item.icon" class="h-5 w-5 mr-3" />
                                 <span class="hidden">{{ item.title }}</span>
                             </div>
                             <span 
                                 v-if="item.badge" 
                                 :class="[
                                     'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white hidden',
                                     item.badgeColor || 'bg-gray-500'
                                 ]"
                             >
                                 {{ item.badge }}
                             </span>
                         </div>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenu>

            
        </SidebarContent>
        
                 <SidebarFooter class="border-t px-2 py-4">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
