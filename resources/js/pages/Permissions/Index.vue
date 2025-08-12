<template>
    <AppLayout title="Permission Management">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Permission Management
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
                                    User Permissions
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
                                    Role Permissions
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
                                    Expired Permissions
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
                                        <h3 class="text-lg font-medium mb-4">Users</h3>
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
                                                    Permissions for {{ selectedUser.name }}
                                                </h3>
                                                <button
                                                    @click="showGrantModal = true"
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                                >
                                                    Grant Permission
                                                </button>
                                            </div>

                                            <!-- Direct Permissions -->
                                            <div>
                                                <h4 class="font-medium mb-2">Direct Permissions</h4>
                                                <div class="space-y-2">
                                                    <div
                                                        v-for="permission in userPermissions.direct_permissions"
                                                        :key="permission.id"
                                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                                    >
                                                        <div>
                                                            <div class="font-medium">{{ permission.display_name }}</div>
                                                            <div class="text-sm text-gray-500">{{ permission.module }}</div>
                                                            <div v-if="permission.pivot" class="text-xs text-gray-400">
                                                                Type: {{ permission.pivot.type }}
                                                                <span v-if="permission.pivot.expires_at">
                                                                    | Expires: {{ formatDate(permission.pivot.expires_at) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <button
                                                            @click="revokePermission(permission.name)"
                                                            class="text-red-600 hover:text-red-800 text-sm"
                                                        >
                                                            Revoke
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Role Permissions -->
                                            <div>
                                                <h4 class="font-medium mb-2">Role Permissions</h4>
                                                <div class="space-y-2">
                                                    <div
                                                        v-for="permission in userPermissions.role_permissions"
                                                        :key="`${permission.id}-${permission.role_name}`"
                                                        class="p-3 bg-blue-50 dark:bg-blue-900 rounded-lg"
                                                    >
                                                        <div class="font-medium">{{ permission.display_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ permission.module }}</div>
                                                        <div class="text-xs text-blue-600 dark:text-blue-400">
                                                            Via role: {{ permission.role_name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center text-gray-500 py-8">
                                            Select a user to view their permissions
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
                                        <div class="space-y-2 max-h-96 overflow-y-auto">
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
                                                    {{ role.permissions?.length || 0 }} permissions
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Role Permissions -->
                                    <div class="lg:col-span-2">
                                        <div v-if="selectedRole" class="space-y-4">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-lg font-medium">
                                                    Permissions for {{ selectedRole.name }} role
                                                </h3>
                                                <button
                                                    @click="showRoleModal = true"
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                                >
                                                    Update Permissions
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div
                                                    v-for="permission in rolePermissions"
                                                    :key="permission.id"
                                                    class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                                >
                                                    <div class="font-medium">{{ permission.display_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ permission.module }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center text-gray-500 py-8">
                                            Select a role to view its permissions
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expired Permissions Tab -->
                            <div v-if="activeTab === 'expired'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium">Expired Permissions</h3>
                                    <button
                                        @click="cleanupExpiredPermissions"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                    >
                                        Cleanup All Expired
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div
                                        v-for="permission in expiredPermissions"
                                        :key="permission.id"
                                        class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900 rounded-lg"
                                    >
                                        <div>
                                            <div class="font-medium">{{ permission.user?.name }}</div>
                                            <div class="text-sm text-gray-500">{{ permission.permission?.display_name }}</div>
                                            <div class="text-xs text-red-600 dark:text-red-400">
                                                Expired: {{ formatDate(permission.expires_at) }}
                                            </div>
                                        </div>
                                        <button
                                            @click="deleteExpiredPermission(permission.id)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <div v-if="expiredPermissions.length === 0" class="text-center text-gray-500 py-8">
                                    No expired permissions found
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grant Permission Modal -->
        <div v-if="showGrantModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-medium mb-4">Grant Permission</h3>
                <form @submit.prevent="grantPermission">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Permission</label>
                            <select v-model="grantForm.permission_name" class="w-full border rounded-md px-3 py-2">
                                <option value="">Select permission...</option>
                                <option v-for="permission in permissions" :key="permission.id" :value="permission.name">
                                    {{ permission.display_name }} ({{ permission.module }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Type</label>
                            <select v-model="grantForm.type" class="w-full border rounded-md px-3 py-2">
                                <option value="temporary">Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>
                        <div v-if="grantForm.type === 'temporary'">
                            <label class="block text-sm font-medium mb-1">Expires At</label>
                            <input
                                v-model="grantForm.expires_at"
                                type="datetime-local"
                                class="w-full border rounded-md px-3 py-2"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Reason</label>
                            <textarea
                                v-model="grantForm.reason"
                                class="w-full border rounded-md px-3 py-2"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            @click="showGrantModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md"
                        >
                            Grant Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Role Permissions Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-medium mb-4">Update Role Permissions</h3>
                <form @submit.prevent="updateRolePermissions">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Permissions</label>
                            <div class="max-h-64 overflow-y-auto space-y-2">
                                <label
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex items-center"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="roleForm.permission_ids"
                                        class="mr-2"
                                    />
                                    <span class="text-sm">
                                        {{ permission.display_name }} ({{ permission.module }})
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            type="button"
                            @click="showRoleModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md"
                        >
                            Update Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    permissions: Array,
    users: Array,
    roles: Array,
});

const activeTab = ref('users');
const selectedUser = ref(null);
const selectedRole = ref(null);
const userPermissions = ref({ direct_permissions: [], role_permissions: [] });
const rolePermissions = ref([]);
const expiredPermissions = ref([]);
const showGrantModal = ref(false);
const showRoleModal = ref(false);

const grantForm = ref({
    permission_name: '',
    type: 'temporary',
    reason: '',
    expires_at: '',
});

const roleForm = ref({
    permission_ids: [],
});

onMounted(async () => {
    await loadExpiredPermissions();
});

const selectUser = async (user) => {
    selectedUser.value = user;
    try {
        const response = await fetch(`/permissions/user/${user.id}`);
        const data = await response.json();
        userPermissions.value = data;
    } catch (error) {
        console.error('Error loading user permissions:', error);
    }
};

const selectRole = async (role) => {
    selectedRole.value = role;
    try {
        const response = await fetch(`/permissions/role/${role.id}`);
        const data = await response.json();
        rolePermissions.value = data.permissions;
    } catch (error) {
        console.error('Error loading role permissions:', error);
    }
};

const grantPermission = async () => {
    try {
        const response = await fetch(`/permissions/user/${selectedUser.value.id}/grant`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(grantForm.value),
        });

        if (response.ok) {
            showGrantModal.value = false;
            await selectUser(selectedUser.value);
            grantForm.value = { permission_name: '', type: 'temporary', reason: '', expires_at: '' };
        }
    } catch (error) {
        console.error('Error granting permission:', error);
    }
};

const revokePermission = async (permissionName) => {
    try {
        const response = await fetch(`/permissions/user/${selectedUser.value.id}/revoke`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ permission_name: permissionName }),
        });

        if (response.ok) {
            await selectUser(selectedUser.value);
        }
    } catch (error) {
        console.error('Error revoking permission:', error);
    }
};

const updateRolePermissions = async () => {
    try {
        const response = await fetch(`/permissions/role/${selectedRole.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(roleForm.value),
        });

        if (response.ok) {
            showRoleModal.value = false;
            await selectRole(selectedRole.value);
            roleForm.value = { permission_ids: [] };
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        if (response.ok) {
            await loadExpiredPermissions();
        }
    } catch (error) {
        console.error('Error cleaning up expired permissions:', error);
    }
};

const deleteExpiredPermission = async (permissionId) => {
    try {
        const response = await fetch(`/permissions/expired/${permissionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        if (response.ok) {
            await loadExpiredPermissions();
        }
    } catch (error) {
        console.error('Error deleting expired permission:', error);
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
