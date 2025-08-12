<script setup lang="ts">
import { ref } from 'vue';
import Button from './ui/button/Button.vue';
import Dialog from './ui/dialog/Dialog.vue';
import DialogContent from './ui/dialog/DialogContent.vue';
import DialogTitle from './ui/dialog/DialogTitle.vue';
import { router, useForm } from '@inertiajs/vue3';
import Input from './ui/input/Input.vue';
import { useToast } from '@/composables/useToast';
import { error as logError, userLogger } from '@/utils/logger';

/**
 * UpdateUserModal Component
 * 
 * Modal component for updating user information with form validation
 * and error handling. Provides a comprehensive interface for user management.
 * 
 * Features:
 * - User information editing form
 * - Status and work time management
 * - Password update capability
 * - Form validation and error handling
 * - Toast notifications for user feedback
 * 
 * @component
 * @example
 * <UpdateUserModal :user="userData" />
 */

/**
 * User interface definition
 */
interface User {
    id: string;
    name: string;
    status: string;
    email: string;
    password?: string;
    nickname?: string;
    hour_value: number;
    work_time: string;
}

/**
 * Component props
 */
const props = defineProps<{
    user: User;
}>();

/**
 * Modal open state
 */
const open = ref(false);

/**
 * Toast notification utilities
 */
const { success, error } = useToast();

/**
 * Form data for user update
 */
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    nickname: props.user.nickname,
    password: '',
    hour_value: props.user.hour_value,
    work_time: props.user.work_time,
    status: props.user.status
});

/**
 * Submit form to update user
 * Handles form submission with error handling and logging
 */
const submit = () => {
    try {
        form.put(`/users/${props.user.id}`, {
            onSuccess: () => {
                success('User updated', `User ${props.user.name} has been updated successfully`);
                userLogger.userAction(props.user.id, 'user_updated', { 
                    name: props.user.name,
                    status: form.status 
                });
                form.reset();
                open.value = false;
                router.reload();
            },
            onError: () => {
                error('Update error', 'There was a problem updating the user. Please verify the data.');
                logError('User update failed', { 
                    userId: props.user.id, 
                    errors: form.errors 
                });
            }
        });
    } catch (err) {
        error('Unexpected error', 'An unexpected error occurred while updating the user.');
        logError('Unexpected error during user update', { 
            userId: props.user.id, 
            error: err 
        });
    }
};

/**
 * Check if form inputs should be disabled
 * Disables inputs for inactive users
 * 
 * @returns {boolean} True if inputs should be disabled
 */
const disableInput = (): boolean => {
    return props.user.status === 'active' ? false : true;
};

/**
 * Work time options for the select dropdown
 */
const workTimeOptions = [
    { value: 'part time', label: 'Part Time' },
    { value: 'full', label: 'Full Time' }
];

/**
 * Status options for the select dropdown
 */
const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' }
];
</script>

<template>
    <div>
        <!-- Open modal button -->
        <Button @click="open = true" class="bg-blue-500 mt-3 text-white hover:bg-blue-600">
            View more
        </Button>

        <!-- Modal dialog -->
        <Dialog :open="open" @update:open="open = $event">
            <DialogContent class="max-w-md p-6 bg-white rounded-lg shadow-lg">
                <DialogTitle class="text-lg font-bold mb-4 text-gray-800">
                    {{ props.user.name }}
                </DialogTitle>
                
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Name field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <Input 
                            v-model="form.name" 
                            class="w-full border-gray-300 text-black bg-white" 
                            :disabled="disableInput()" 
                        />
                    </div>
                    
                    <!-- Nickname field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nickname</label>
                        <Input 
                            :model-value="form.nickname || ''" 
                            @update:model-value="(value) => form.nickname = String(value)" 
                            class="w-full border-gray-300 text-black bg-white capitalize" 
                            :disabled="disableInput()" 
                        />
                    </div>
                    
                    <!-- Email field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <Input 
                            v-model="form.email" 
                            class="w-full border-gray-300 text-black bg-white" 
                            :disabled="disableInput()" 
                        />
                    </div>
                    
                    <!-- Password field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <Input 
                            v-model="form.password" 
                            placeholder="If you forget your password, enter a new one." 
                            class="w-full border-gray-300 text-black bg-white" 
                            :disabled="disableInput()" 
                        />
                    </div>
                    
                    <!-- Hour value field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hour Value</label>
                        <Input 
                            v-model="form.hour_value" 
                            class="w-full border-gray-300 text-black bg-white" 
                            :disabled="disableInput()" 
                        />
                    </div>
                    
                    <!-- Status and work time fields -->
                    <div class="flex space-x-4 justify-start">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                v-model="form.status"
                                :class="['border h-9 border-gray-300 rounded bg-white px-2']"
                            >
                                <option value="" disabled>Select Status</option>
                                <option 
                                    v-for="option in statusOptions" 
                                    :key="option.value" 
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Work Time</label>
                            <select
                                v-model="form.work_time"
                                :class="['border h-9 border-gray-300 rounded bg-white px-2']"
                                :disabled="disableInput()"
                            >
                                <option value="" disabled>Select Work Time</option>
                                <option 
                                    v-for="option in workTimeOptions" 
                                    :key="option.value" 
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex justify-end gap-2">
                        <Button 
                            type="button" 
                            variant="secondary" 
                            @click="open = false" 
                            class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                        >
                            Close
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="bg-blue-500 text-white hover:bg-blue-600"
                        >
                            Update
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>