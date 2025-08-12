<script setup lang="ts">
import UpdateUserModal from './UpdateUserModal.vue';

/**
 * CardUser Component
 * 
 * Displays user information in a card format with status indicators
 * and provides access to user update functionality.
 * 
 * Features:
 * - User status display with color coding
 * - Truncated name display for long names
 * - User information display (email, hour value, work time)
 * - Update user modal integration
 * 
 * @component
 * @example
 * <CardUser :user="userData" />
 */

/**
 * User status types
 */
type UserStatus = 'active' | 'inactive' | 'completed' | 'cancelled' | 'paused';

/**
 * User interface definition
 */
interface User {
    id: string;
    name: string;
    status: UserStatus;
    email: string;
    hour_value: number;
    work_time: string;
    nickname?: string;
    password?: string;
}

/**
 * Component props
 */
const props = defineProps<{
    user: User;
}>();

/**
 * Get CSS classes for user status badge
 * Returns appropriate color classes based on user status
 * 
 * @param {UserStatus} status - User status
 * @returns {string} CSS classes for status badge
 */
function getStatusClass(status: UserStatus): string {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-600';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'paused':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-600';
    }
}

/**
 * Truncate user name if it's too long
 * Limits name display to 15 characters with ellipsis
 * 
 * @param {string} name - User name
 * @returns {string} Truncated name if necessary
 */
function truncateName(name: string): string {
    return name.length > 15 ? `${name.slice(0, 15)}...` : name;
}
</script>

<template>
    <div class="max-w-xs mx-auto mt-6">
        <div class="bg-white rounded-2xl shadow-md p-6 border-gray-200">
            <!-- User header with name and status -->
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl font-bold text-gray-800">
                    {{ truncateName(props.user.name) }}
                </h2>
                <span
                    class="px-3 py-1 text-sm rounded-full capitalize"
                    :class="getStatusClass(props.user.status)"
                >
                    {{ props.user.status }}
                </span>
            </div>
            
            <!-- User information -->
            <p class="text-gray-600 m-1">
                <span class="font-bold text-black">Email:</span>
                {{ props.user.email }}
            </p>
            <p class="text-gray-600 m-1">
                <span class="font-bold text-black">Hour value: </span>
                {{ props.user.hour_value }}
            </p>
            <p class="text-gray-600 m-1 capitalize">
               <span class="font-bold text-black">Work time:</span>
               {{ props.user.work_time }}
            </p>
            
            <!-- Update user modal -->
            <div class="w-full flex justify-end">
                <UpdateUserModal :user="props.user" />
            </div>
        </div>
    </div>
</template>
</template>