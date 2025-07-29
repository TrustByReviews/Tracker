<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Payment Reports',
        href: '/payment-reports',
    },
];

interface Developer {
    id: string;
    name: string;
    email: string;
    hour_value: number;
    total_tasks: number;
    completed_tasks: number;
    total_hours: number;
    total_earnings: number;
    assigned_projects: number;
}

interface Props {
    developers: Developer[];
    totalEarnings: number;
    totalHours: number;
}

const props = defineProps<Props>();

const selectedDevelopers = ref<string[]>([]);
const startDate = ref('');
const endDate = ref('');
const format = ref('csv');
const email = ref('');

const isFormValid = computed(() => {
    return selectedDevelopers.value.length > 0 && format.value;
});

const selectAll = () => {
    selectedDevelopers.value = props.developers.map(d => d.id);
};

const deselectAll = () => {
    selectedDevelopers.value = [];
};

const generateReport = () => {
    const data = {
        developer_ids: selectedDevelopers.value,
        start_date: startDate.value || null,
        end_date: endDate.value || null,
        format: format.value,
        email: email.value,
    };

    if (format.value === 'email') {
        router.post('/payment-reports/generate', data);
    } else {
        router.post('/payment-reports/generate', data, {
            preserveScroll: true,
            onSuccess: () => {
                // Reset form after successful generation
                selectedDevelopers.value = [];
                startDate.value = '';
                endDate.value = '';
                email.value = '';
            }
        });
    }
};

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head title="Payment Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-lg p-6 text-white">
                <h1 class="text-2xl font-bold mb-2">Payment Reports</h1>
                <p class="text-emerald-100">Generate payment reports for developers</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Developers</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ developers.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Earnings</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ formatCurrency(totalEarnings) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Hours</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ totalHours }}h</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Generator -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generate Payment Report</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Select developers and configure report options</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Developer Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Select Developers</h4>
                            <div class="space-x-2">
                                <button @click="selectAll" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    Select All
                                </button>
                                <button @click="deselectAll" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                    Deselect All
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="developer in developers" :key="developer.id" 
                                 class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                 :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': selectedDevelopers.includes(developer.id) }"
                                 @click="selectedDevelopers.includes(developer.id) ? selectedDevelopers = selectedDevelopers.filter(id => id !== developer.id) : selectedDevelopers.push(developer.id)">
                                
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           :checked="selectedDevelopers.includes(developer.id)"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ developer.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            {{ developer.email }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            ${{ developer.hour_value }}/hr • {{ developer.total_earnings ? formatCurrency(developer.total_earnings) : '$0' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Start Date (Optional)
                            </label>
                            <input type="date" v-model="startDate" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                End Date (Optional)
                            </label>
                            <input type="date" v-model="endDate" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    <!-- Format Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Report Format
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': format === 'csv' }">
                                <input type="radio" v-model="format" value="csv" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">CSV</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Download as .csv file</p>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': format === 'pdf' }">
                                <input type="radio" v-model="format" value="pdf" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">PDF</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Download as .pdf file</p>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                   :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': format === 'email' }">
                                <input type="radio" v-model="format" value="email" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Email</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Send via email</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Email Field (only for email format) -->
                    <div v-if="format === 'email'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Address
                        </label>
                        <input type="email" v-model="email" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Enter email address">
                    </div>

                    <!-- Generate Button -->
                    <div class="flex justify-end">
                        <button @click="generateReport" 
                                :disabled="!isFormValid"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Developers Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Developer Summary</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Overview of all developers and their earnings</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Developer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Hour Rate
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tasks
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Hours
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Earnings
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="developer in developers" :key="developer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ developer.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ developer.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ developer.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${{ developer.hour_value }}/hr
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ developer.completed_tasks }}/{{ developer.total_tasks }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ developer.total_hours }}h
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ formatCurrency(developer.total_earnings) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 