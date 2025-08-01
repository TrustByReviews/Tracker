<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LogOut, Settings, User } from 'lucide-vue-next';
import type { User as UserType } from '@/types';

interface Props {
    user: UserType;
}

const props = defineProps<Props>();
const isOpen = ref(false);

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
    console.log('Menu toggled:', isOpen.value);
    console.log('isOpen value:', isOpen.value);
    console.log('Button clicked!');
};

const closeMenu = () => {
    isOpen.value = false;
};

// Cerrar menú al hacer clic fuera
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.user-menu')) {
        closeMenu();
    }
};

import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    console.log('UserMenu mounted, user:', props.user);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="user-menu relative flex justify-center">
        <!-- Botón del menú - tamaño perfecto -->
        <button
            @click="toggleMenu"
            class="w-8 h-8 text-gray-200 bg-gray-800 border border-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-indigo-500 cursor-pointer"
            type="button"
            :title="user.name"
        >
            <div class="w-full h-full flex items-center justify-center">
                <User class="h-4 w-4" />
            </div>
        </button>

        <!-- Menú desplegable - CENTRADO PERFECTO -->
        <div
            v-if="isOpen"
            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-8 bg-gray-800 border border-gray-600 rounded-md shadow-xl z-50"
        >
            <!-- Opciones del menú - centrado perfecto -->
            <div>
                <!-- Settings -->
                <Link
                    :href="route('profile.edit')"
                    class="w-8 h-8 text-white hover:bg-gray-700 transition-colors duration-200"
                    @click="closeMenu"
                    :title="'Settings'"
                >
                    <div class="w-full h-full flex items-center justify-center">
                        <Settings class="h-4 w-4" />
                    </div>
                </Link>

                <!-- Logout -->
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-8 h-8 text-white hover:bg-gray-700 transition-colors duration-200"
                    @click="closeMenu"
                    :title="'Log out'"
                >
                    <div class="w-full h-full flex items-center justify-center">
                        <LogOut class="h-4 w-4" />
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.user-menu {
    position: relative;
}

.user-menu button {
    cursor: pointer !important;
    min-width: 32px;
    min-height: 32px;
    padding: 0 !important;
    margin: 0 !important;
}

.user-menu button:hover {
    background-color: #f9fafb;
    transform: scale(1.02);
    transition: all 0.15s ease;
}

.user-menu button:focus {
    outline: 1px solid #3b82f6;
    outline-offset: 1px;
}

/* Menú simple y funcional */
.user-menu > div:last-child {
    display: block !important;
    min-width: 32px;
    max-width: 32px;
    background: #1f2937 !important;
    border: 1px solid #4b5563 !important;
    border-radius: 6px !important;
}

.user-menu > div:last-child a,
.user-menu > div:last-child button {
    border-radius: 4px;
    margin: 0 !important;
    padding: 0 !important;
    transition: all 0.15s ease;
    min-width: 32px;
    min-height: 32px;
}

.user-menu > div:last-child a:hover,
.user-menu > div:last-child button:hover {
    background-color: #374151 !important;
    transform: scale(1.02);
}

/* Centrado perfecto de íconos */
.user-menu svg {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    flex-shrink: 0;
}

.user-menu .w-full.h-full {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
</style> 