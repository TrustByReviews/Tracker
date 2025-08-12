<script setup lang="ts">
interface SharedData {
  sidebarOpen: boolean;
  user: any;
  [key: string]: any;
}
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import SimpleUserMenu from './SimpleUserMenu.vue';
import { ref } from 'vue';

const page = usePage<SharedData>();
const user = (page.props as any).auth?.user as User;
const { isMobile, state } = useSidebar();

// Debug logs
console.log('NavUser - User data:', user);
console.log('NavUser - Sidebar state:', state.value);
console.log('NavUser - Is mobile:', isMobile.value);

// Roles are loaded correctly from the middleware
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <div class="p-2">
                <SimpleUserMenu :user="user" />
            </div>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
