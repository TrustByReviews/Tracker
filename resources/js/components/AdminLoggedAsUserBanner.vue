<!-- TEMPORARILY DISABLED - Login as User functionality -->
<!-- 
<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, Shield } from 'lucide-vue-next';

const page = usePage();

// Verificar si el usuario actual es admin y está logueado como otro usuario
const isAdminLoggedAsUser = computed(() => {
    const user = (page.props as any)['auth']?.user;
    const adminOriginalUserId = (page.props as any)['admin_original_user_id'];
    
    // Solo mostrar el banner si hay una sesión de admin_original_user_id activa
    const isAdmin = user?.roles?.some((role: any) => role.name === 'admin');
    const isImpersonating = !!adminOriginalUserId;
    
    return isAdmin && isImpersonating;
});

const currentUser = computed(() => (page.props as any)['auth']?.user);
</script>

<template>
    <div v-if="isAdminLoggedAsUser" class="bg-amber-50 border-b border-amber-200 dark:bg-amber-900/20 dark:border-amber-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3">
                <div class="flex items-center">
                    <AlertTriangle class="h-5 w-5 text-amber-600 dark:text-amber-400 mr-2" />
                    <span class="text-sm text-amber-800 dark:text-amber-200">
                        You are currently logged in as <strong>{{ currentUser?.name }}</strong> 
                        ({{ currentUser?.email }})
                    </span>
                </div>
                <Link
                    :href="route('admin.return-to-admin')"
                    class="inline-flex items-center px-3 py-1.5 border border-amber-300 text-xs font-medium rounded-md text-amber-700 bg-amber-100 hover:bg-amber-200 dark:bg-amber-800 dark:text-amber-200 dark:border-amber-600 dark:hover:bg-amber-700 transition-colors"
                >
                    <Shield class="h-4 w-4 mr-1" />
                    Return to Admin
                </Link>
            </div>
        </div>
    </div>
</template>
--> 