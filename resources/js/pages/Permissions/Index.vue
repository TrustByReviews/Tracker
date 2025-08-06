<template>
    <AppLayout title="Gestión de Permisos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestión de Permisos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="-mb-px flex space-x-8">
                                <button
                                    @click="activeTab = 'users'"
                                    :class="[
                                        'py-2 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'users'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                    ]"
                                >
                                    Permisos de Usuarios
                                </button>
                                <button
                                    @click="activeTab = 'roles'"
                                    :class="[
                                        'py-2 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'roles'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                    ]"
                                >
                                    Permisos de Roles
                                </button>
                                <button
                                    @click="activeTab = 'expired'"
                                    :class="[
                                        'py-2 px-1 border-b-2 font-medium text-sm',
                                        activeTab === 'expired'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                    ]"
                                >
                                    Permisos Expirados
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="mt-6">
                            <!-- Users Tab -->
                            <div v-if="activeTab === 'users'" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Users List -->
                                    <div class="lg:col-span-1">
                                        <h3 class="text-lg font-medium mb-4">Usuarios</h3>
                                        <div class="space-y-2 max-h-96 overflow-y-auto">
                                            <div
                                                v-for="user in users"
                                                :key="user.id"
                                                @click="selectUser(user)"
                                                :class="[
                                                    'p-3 rounded-lg cursor-pointer transition-colors',
                                                    selectedUser?.id === user.id
                                                        ? 'bg-indigo-100 dark:bg-indigo-900 border border-indigo-300 dark:border-indigo-700'
                                                        : 'bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600'
                                                ]"
                                            >
                                                <div class="font-medium">{{ user.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    Roles: {{ user.roles.map(r => r.name).join(', ') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- User Permissions -->
                                    <div class="lg:col-span-2">
                                        <div v-if="selectedUser" class="space-y-4">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-lg font-medium">
                                                    Permisos de {{ selectedUser.name }}
                                                </h3>
                                                <button
                                                    @click="showGrantModal = true"
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                                >
                                                    Otorgar Permiso
                                                </button>
                                            </div>

                                            <!-- Direct Permissions -->
                                            <div>
                                                <h4 class="font-medium mb-2">Permisos Directos</h4>
                                                <div class="space-y-2">
                                                    <div
                                                        v-for="permission in userDirectPermissions"
                                                        :key="permission.id"
                                                        class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                                    >
                                                        <div>
                                                            <div class="font-medium">{{ permission.display_name }}</div>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ permission.description }}
                                                            </div>
                                                                                                                         <div class="text-xs text-gray-400 dark:text-gray-500">
                                                                 Tipo: {{ permission.pivot?.type }}
                                                                 <span v-if="permission.pivot?.expires_at">
                                                                     | Expira: {{ formatDate(permission.pivot.expires_at) }}
                                                                 </span>
                                                             </div>
                                                        </div>
                                                        <button
                                                            @click="revokePermission(permission.name)"
                                                            class="text-red-600 hover:text-red-800 text-sm"
                                                        >
                                                            Revocar
                                                        </button>
                                                    </div>
                                                    <div v-if="userDirectPermissions.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                                                        No tiene permisos directos
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Role Permissions -->
                                            <div>
                                                <h4 class="font-medium mb-2">Permisos por Roles</h4>
                                                <div class="space-y-2">
                                                    <div
                                                        v-for="permission in userRolePermissions"
                                                        :key="permission.id"
                                                        class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                                    >
                                                        <div class="font-medium">{{ permission.display_name }}</div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ permission.description }}
                                                        </div>
                                                        <div class="text-xs text-blue-600 dark:text-blue-400">
                                                            Via rol: {{ permission.role_name }}
                                                        </div>
                                                    </div>
                                                    <div v-if="userRolePermissions.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                                                        No tiene permisos por roles
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                                            Selecciona un usuario para ver sus permisos
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Roles Tab -->
                            <div v-if="activeTab === 'roles'" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Roles List -->
                                    <div class="lg:col-span-1">
                                        <h3 class="text-lg font-medium mb-4">Roles</h3>
                                        <div class="space-y-2">
                                            <div
                                                v-for="role in roles"
                                                :key="role.id"
                                                @click="selectRole(role)"
                                                :class="[
                                                    'p-3 rounded-lg cursor-pointer transition-colors',
                                                    selectedRole?.id === role.id
                                                        ? 'bg-indigo-100 dark:bg-indigo-900 border border-indigo-300 dark:border-indigo-700'
                                                        : 'bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600'
                                                ]"
                                            >
                                                <div class="font-medium">{{ role.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ role.permissions.length }} permisos
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Role Permissions -->
                                    <div class="lg:col-span-2">
                                        <div v-if="selectedRole" class="space-y-4">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-lg font-medium">
                                                    Permisos del Rol: {{ selectedRole.name }}
                                                </h3>
                                                <button
                                                    @click="showRoleModal = true"
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                                >
                                                    Editar Permisos
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div
                                                    v-for="permission in selectedRole.permissions"
                                                    :key="permission.id"
                                                    class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                                >
                                                    <div class="font-medium">{{ permission.display_name }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ permission.description }}
                                                    </div>
                                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                        Módulo: {{ permission.module }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                                            Selecciona un rol para ver sus permisos
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expired Tab -->
                            <div v-if="activeTab === 'expired'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium">Permisos Expirados</h3>
                                    <button
                                        @click="cleanupExpiredPermissions"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                    >
                                        Limpiar Expirados
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div
                                        v-for="permission in expiredPermissions"
                                        :key="permission.id"
                                        class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
                                    >
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="font-medium">{{ permission.user.name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Permiso: {{ permission.permission.display_name }}
                                                </div>
                                                <div class="text-xs text-red-600 dark:text-red-400">
                                                    Expiró: {{ formatDate(permission.expires_at) }}
                                                </div>
                                            </div>
                                            <button
                                                @click="deleteExpiredPermission(permission.id)"
                                                class="text-red-600 hover:text-red-800 text-sm"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="expiredPermissions.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                                        No hay permisos expirados
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grant Permission Modal -->
        <div v-if="showGrantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Otorgar Permiso</h3>
                    <form @submit.prevent="grantPermission">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Permiso</label>
                                <select v-model="grantForm.permission_name" class="w-full rounded-md border-gray-300 text-gray-900 bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" class="text-gray-500">Seleccionar permiso</option>
                                    <option v-for="permission in permissions" :key="permission.id" :value="permission.name" class="text-gray-900 bg-white">
                                        {{ permission.display_name }} ({{ permission.module }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select v-model="grantForm.type" class="w-full rounded-md border-gray-300 text-gray-900 bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="temporary" class="text-gray-900 bg-white">Temporal</option>
                                    <option value="permanent" class="text-gray-900 bg-white">Permanente</option>
                                </select>
                            </div>

                            <div v-if="grantForm.type === 'temporary'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Expiración</label>
                                <input
                                    v-model="grantForm.expires_at"
                                    type="datetime-local"
                                    class="w-full rounded-md border-gray-300 text-gray-900 bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Razón (opcional)</label>
                                <textarea
                                    v-model="grantForm.reason"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 text-gray-900 bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Razón por la que se otorga el permiso..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button
                                type="button"
                                @click="showGrantModal = false"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Otorgar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Role Permissions Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Permisos del Rol: {{ selectedRole?.name }}</h3>
                    <form @submit.prevent="updateRolePermissions">
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <div
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="flex items-center p-2 hover:bg-gray-50 rounded-md"
                            >
                                <input
                                    :id="permission.id"
                                    v-model="rolePermissionsForm.permission_ids"
                                    :value="permission.id"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label :for="permission.id" class="ml-2 text-sm text-gray-900 cursor-pointer flex-1">
                                    <div class="font-medium">{{ permission.display_name }}</div>
                                    <div class="text-gray-600">{{ permission.description }}</div>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button
                                type="button"
                                @click="showRoleModal = false"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';


interface Permission {
    id: string;
    name: string;
    display_name: string;
    description: string;
    module: string;
    pivot?: {
        type: string;
        expires_at: string;
        reason: string;
    };
    role_name?: string;
}

interface User {
    id: string;
    name: string;
    email: string;
    roles: Array<{ id: string; name: string }>;
    directPermissions?: Permission[];
    rolePermissions?: Permission[];
}

interface Role {
    id: string;
    name: string;
    permissions: Permission[];
}

interface Props {
    permissions: Permission[];
    users: User[];
    roles: Role[];
}

const props = defineProps<Props>();

// Reactive data
const activeTab = ref('users');
const selectedUser = ref<User | null>(null);
const selectedRole = ref<Role | null>(null);
const showGrantModal = ref(false);
const showRoleModal = ref(false);
const expiredPermissions = ref<any[]>([]);

// Forms
const grantForm = ref({
    permission_name: '',
    type: 'temporary',
    reason: '',
    expires_at: '',
});

const rolePermissionsForm = ref({
    permission_ids: [] as string[],
});

// Computed
const userDirectPermissions = computed(() => {
    if (!selectedUser.value) return [];
    return selectedUser.value.directPermissions || [];
});

const userRolePermissions = computed(() => {
    if (!selectedUser.value) return [];
    return selectedUser.value.rolePermissions || [];
});

// Methods
const selectUser = (user: User) => {
    selectedUser.value = user;
    loadUserPermissions(user.id);
};

const selectRole = (role: Role) => {
    selectedRole.value = role;
    rolePermissionsForm.value.permission_ids = role.permissions.map(p => p.id);
};

const loadUserPermissions = async (userId: string) => {
    try {
        const response = await fetch(`/permissions/user/${userId}`);
        const data = await response.json();
        if (selectedUser.value) {
            selectedUser.value.directPermissions = data.direct_permissions;
            selectedUser.value.rolePermissions = data.role_permissions;
        }
    } catch (error) {
        console.error('Error loading user permissions:', error);
    }
};

const grantPermission = async () => {
    if (!selectedUser.value) return;

    try {
        const response = await fetch(`/permissions/user/${selectedUser.value.id}/grant`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(grantForm.value),
        });

        if (response.ok) {
            showGrantModal.value = false;
            grantForm.value = {
                permission_name: '',
                type: 'temporary',
                reason: '',
                expires_at: '',
            };
            loadUserPermissions(selectedUser.value.id);
        }
    } catch (error) {
        console.error('Error granting permission:', error);
    }
};

const revokePermission = async (permissionName: string) => {
    if (!selectedUser.value) return;

    try {
        const response = await fetch(`/permissions/user/${selectedUser.value.id}/revoke`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ permission_name: permissionName }),
        });

        if (response.ok) {
            loadUserPermissions(selectedUser.value.id);
        }
    } catch (error) {
        console.error('Error revoking permission:', error);
    }
};

const updateRolePermissions = async () => {
    if (!selectedRole.value) return;

    try {
        const response = await fetch(`/permissions/role/${selectedRole.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(rolePermissionsForm.value),
        });

        if (response.ok) {
            showRoleModal.value = false;
            // Reload the page to get updated role data
            router.reload();
        }
    } catch (error) {
        console.error('Error updating role permissions:', error);
    }
};

const loadExpiredPermissions = async () => {
    try {
        const response = await fetch('/permissions/expired');
        const data = await response.json();
        expiredPermissions.value = data.expired_permissions;
    } catch (error) {
        console.error('Error loading expired permissions:', error);
    }
};

const cleanupExpiredPermissions = async () => {
    try {
        const response = await fetch('/permissions/expired', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            loadExpiredPermissions();
        }
    } catch (error) {
        console.error('Error cleaning up expired permissions:', error);
    }
};

const deleteExpiredPermission = async (permissionId: string) => {
    try {
        const response = await fetch(`/permissions/expired/${permissionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            loadExpiredPermissions();
        }
    } catch (error) {
        console.error('Error deleting expired permission:', error);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

// Lifecycle
onMounted(() => {
    loadExpiredPermissions();
});
</script>

<style scoped>
/* Estilos específicos para mejorar el contraste en los dropdowns */
select option {
    background-color: white;
    color: #111827; /* text-gray-900 */
    padding: 8px 12px;
}

select option:hover {
    background-color: #f3f4f6; /* bg-gray-100 */
}

select option:checked {
    background-color: #3b82f6; /* bg-blue-500 */
    color: white;
}

/* Mejorar la legibilidad de los checkboxes */
input[type="checkbox"]:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

/* Estilos para el hover en las opciones de permisos */
.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}
</style> 