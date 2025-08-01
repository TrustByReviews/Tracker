<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, Settings, Shield } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User;
}

const props = defineProps<Props>();
const page = usePage();

    // TEMPORARILY DISABLED - Login as User functionality
    // Verificar si el usuario actual es admin y está logueado como otro usuario
    // const isAdminLoggedAsUser = computed(() => {
    //     const authUser = (page.props as any)['auth']?.user;
    //     return authUser?.roles?.some((role: any) => role.name === 'admin') && 
    //            authUser?.id !== props.user?.id;
    // });
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    
    <!-- TEMPORARILY DISABLED - Login as User functionality -->
    <!-- Return to Admin button (only show when admin is logged as another user) -->
    <!-- 
    <DropdownMenuGroup v-if="isAdminLoggedAsUser">
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('admin.return-to-admin')" as="button">
                <Shield class="mr-2 h-4 w-4" />
                Return to Admin
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator v-if="isAdminLoggedAsUser" />
    -->
    
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
