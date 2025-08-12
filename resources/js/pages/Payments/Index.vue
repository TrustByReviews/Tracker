<template>
    <Head title="Payments & Reports" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                Payments & Reports
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Navigation Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="border-b border-gray-200 dark:border-gray-600">
                        <nav class="-mb-px flex space-x-8 px-6">
                            <button 
                                @click="activeTab = 'dashboard'"
                                :class="[
                                    'py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'dashboard'
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500'
                                ]"
                            >
                                Dashboard
                            </button>
                            <button 
                                @click="activeTab = 'reports'"
                                :class="[
                                    'py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'reports'
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500'
                                ]"
                            >
                                Generate Reports
                            </button>
                            <button 
                                @click="activeTab = 'rework'"
                                :class="[
                                    'py-4 px-1 border-b-2 font-medium text-sm',
                                    activeTab === 'rework'
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500'
                                ]"
                            >
                                Rework Analysis
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Dashboard -->
                <div v-if="activeTab === 'dashboard'">
                    <!-- Filtros -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filters</h3>
                            <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                    <input 
                                        v-model="filters.start_date" 
                                        type="date" 
                                        class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                    <input 
                                        v-model="filters.end_date" 
                                        type="date" 
                                        class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button 
                                        type="submit" 
                                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        Apply Filters
                                    </button>
                                </div>
                                <div class="flex items-end">
                                    <Link 
                                        :href="route('payments.dashboard')" 
                                        class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center"
                                    >
                                        Reset
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- General Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Reports</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.total_reports }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Payment</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">${{ formatNumber(statistics.total_payment) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Hours</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.total_hours }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Reports</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.pending_reports }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Developers</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ statistics.active_developers }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Reports -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Reports</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Developer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    <tr v-for="report in recentReports" :key="report.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ report.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ formatDate(report.week_start_date) }} - {{ formatDate(report.week_end_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ report.total_hours }} hrs
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-300">
                                            ${{ formatNumber(report.total_payment) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                getStatusBadgeClass(report.status)
                                            ]">
                                                {{ report.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link 
                                                :href="route('payments.reports.show', report.id)" 
                                                class="text-blue-600 hover:text-blue-900 mr-3"
                                            >
                                                View
                                            </Link>
                                            <button 
                                                v-if="report.status === 'pending'"
                                                @click="approveReport(report.id)"
                                                class="text-green-600 hover:text-green-900 mr-3"
                                            >
                                                Approve
                                            </button>
                                            <button 
                                                v-if="report.status === 'approved'"
                                                @click="markAsPaid(report.id)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Mark Paid
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Reports -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Reports</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table v-if="pendingReports.length > 0" class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Developer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    <tr v-for="report in pendingReports" :key="report.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ report.user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ formatDate(report.week_start_date) }} - {{ formatDate(report.week_end_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ report.total_hours }} hrs
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            ${{ formatNumber(report.total_payment) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link 
                                                :href="route('payments.reports.show', report.id)" 
                                                class="text-blue-600 hover:text-blue-900 mr-3"
                                            >
                                                View
                                            </Link>
                                            <button 
                                                @click="approveReport(report.id)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Approve
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div v-else class="text-center py-8">
                                <p class="text-gray-500">No pending reports.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Generate Reports -->
                <div v-if="activeTab === 'reports'">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Generate Payment Reports</h3>
                            
                            <form @submit.prevent="generateReport" class="space-y-6">
                                <!-- Report Type Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Report Type
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                            <input 
                                                type="radio" 
                                                value="developers" 
                                                v-model="reportType"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600"
                                            >
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">By Developers</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">All selected developers</p>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                            <input 
                                                type="radio" 
                                                value="project" 
                                                v-model="reportType"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600"
                                            >
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">By Project</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Filter by specific project</p>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                            <input 
                                                type="radio" 
                                                value="role" 
                                                v-model="reportType"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600"
                                            >
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">By Roleeeeeee</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Filter by user role</p>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                            <input 
                                                type="radio" 
                                                value="week" 
                                                v-model="reportType"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600"
                                            >
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">By Week</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Specific week of month</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Project Selection (when report type is project) -->
                                <div v-if="reportType === 'project'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Project
                                    </label>
                                    <select 
                                        v-model="selectedProject" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">All Projects</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Roleeeeeee Selection (when report type is role) -->
                                <div v-if="reportType === 'role'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Role
                                    </label>
                                    <select 
                                        v-model="selectedRole" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">All Roles</option>
                                        <option value="admin">Admin</option>
                                        <option value="team_leader">Team Leader</option>
                                        <option value="developer">Developer</option>
                                        <option value="qa">QA Tester</option>
                                    </select>
                                </div>

                                <!-- Week Selection (when report type is week) -->
                                <div v-if="reportType === 'week'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Week of Month
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                                Month
                                            </label>
                                            <select 
                                                v-model="selectedMonth" 
                                                @change="updateWeekOptions"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            >
                                                <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                                                    {{ month.label }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                                Week
                                            </label>
                                            <select 
                                                v-model="selectedWeek" 
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            >
                                                <option v-for="week in availableWeeks" :key="week.value" :value="week.value">
                                                    {{ week.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                                                 <!-- Developer Selection -->
                                 <div>
                                     <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                         Select Developers
                                     </label>
                                     
                                     <!-- Show loading indicator when loading users -->
                                     <div v-if="loadingUsers" class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                                         <div class="flex items-center">
                                             <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                             </svg>
                                             <span class="text-blue-800 dark:text-blue-200">Loading users for selected project...</span>
                                         </div>
                                     </div>
                                     
                                     <!-- Show message when no users found -->
                                     <div v-else-if="reportType === 'project' && selectedProject && projectUsers.length === 0" class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                                         <p class="text-yellow-800 dark:text-yellow-200">
                                             No users found for the selected project. Please select a different project or role.
                                         </p>
                                     </div>
                                     
                                     <div class="flex gap-2 mb-2">
                                         <button 
                                             type="button" 
                                             @click="selectAll"
                                             class="px-3 py-1 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800"
                                         >
                                             Select All
                                         </button>
                                         <button 
                                             type="button" 
                                             @click="deselectAll"
                                             class="px-3 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                         >
                                             Deselect All
                                         </button>
                                     </div>
                                     
                                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4 dark:bg-gray-700">
                                         <!-- Show project users when project is selected -->
                                         <label 
                                             v-if="reportType === 'project' && selectedProject"
                                             v-for="developer in projectUsers" 
                                             :key="`project-${developer.id}`"
                                             class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                                         >
                                             <input 
                                                 type="checkbox" 
                                                 :value="developer.id" 
                                                 v-model="selectedDevelopers"
                                                 class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                                             >
                                             <div class="flex-1 min-w-0">
                                                 <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ developer.name }}</p>
                                                 <p class="text-xs text-gray-500 dark:text-gray-400">{{ developer.email }}</p>
                                                 <p class="text-xs text-gray-500 dark:text-gray-400">${{ developer.hour_value }}/hr</p>
                                                 <p class="text-xs text-gray-500 dark:text-gray-400">{{ developer.roles.join(', ') }}</p>
                                             </div>
                                         </label>
                                         
                                         <!-- Show all developers when no project is selected -->
                                         <label 
                                             v-else
                                             v-for="developer in developers" 
                                             :key="`all-${developer.id}`"
                                             class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                                         >
                                             <input 
                                                 type="checkbox" 
                                                 :value="developer.id" 
                                                 v-model="selectedDevelopers"
                                                 class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                                             >
                                             <div class="flex-1 min-w-0">
                                                 <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ developer.name }}</p>
                                                 <p class="text-xs text-gray-500 dark:text-gray-400">{{ developer.email }}</p>
                                                 <p class="text-xs text-gray-500 dark:text-gray-400">${{ developer.hour_value }}/hr</p>
                                             </div>
                                         </label>
                                     </div>
                                 </div>

                                <!-- Date Range Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Time Period
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Quick Selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                                Quick Selection
                                            </label>
                                            <select 
                                                v-model="quickPeriod" 
                                                @change="setQuickPeriod"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                            >
                                                <option value="">Custom Range</option>
                                                <option value="this_week">This Week (Mon-Sun)</option>
                                                <option value="last_week">Last Week (Mon-Sun)</option>
                                                <option value="this_month">This Month</option>
                                                <option value="last_month">Last Month</option>
                                                <option value="last_3_months">Last 3 Months</option>
                                                <option value="last_6_months">Last 6 Months</option>
                                                <option value="this_year">This Year</option>
                                                <option value="last_year">Last Year</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Custom Date Range -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                                Custom Range
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <input 
                                                    type="date" 
                                                    v-model="startDate"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                                    placeholder="Start Date"
                                                >
                                                <input 
                                                    type="date" 
                                                    v-model="endDate"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                                    placeholder="End Date"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Selected Period Display -->
                                    <div v-if="startDate || endDate" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            <span class="font-medium">Selected Period:</span> 
                                            {{ formatDisplayDate(startDate) }} - {{ formatDisplayDate(endDate) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Format Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Report Format
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">


                                        <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                               :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': format === 'excel' }">
                                            <input type="radio" v-model="format" value="excel" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">Excel</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Download as .xlsx file with table formatting and charts</p>
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

                                        <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                                               :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': format === 'view' }">
                                            <input type="radio" v-model="format" value="view" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">View in System</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Show report in browser</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Email Field (only for email format) -->
                                <div v-if="format === 'email'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email Address
                                    </label>
                                    <input 
                                        type="email" 
                                        v-model="email" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Enter email address"
                                    >
                                </div>

                                <!-- Generate Button -->
                                <div class="flex justify-end">
                                    <button 
                                        type="submit" 
                                        :disabled="!isFormValid || loadingReport"
                                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                                    >
                                        <svg v-if="loadingReport" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ loadingReport ? 'Generating...' : 'Generate Report' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Developer Summary Table -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
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
                                            ${{ formatCurrency(developer.hour_value) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ developer.completed_tasks }}/{{ developer.total_tasks }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ formatTime(developer.total_hours * 3600) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            ${{ formatCurrency(developer.total_earnings) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Report in System -->
                    <div v-if="showReport && reportData" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Report</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Generated: {{ reportData.generated_at }}
                                        <span v-if="reportData.period.start && reportData.period.end">
                                            | Period: {{ formatDisplayDate(reportData.period.start) }} - {{ formatDisplayDate(reportData.period.end) }}
                                        </span>
                                    </p>
                                </div>
                                <button 
                                    @click="showReport = false; reportData = null"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Report Summary -->
                        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Developers</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ reportData.developers.length }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Hours</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(reportData.totalHours) }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Earnings</p>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">${{ formatNumber(reportData.totalEarnings) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Details by Developer -->
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
                                            Completed Tasks
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total Hours
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total Earnings
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="developer in reportData.developers" :key="developer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
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
                                            ${{ formatNumber(developer.hour_value) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ developer.completed_tasks }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ formatNumber(developer.total_hours) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                            ${{ formatNumber(developer.total_earnings) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Task Details -->
                        <div v-if="reportData.developers.some(d => d.tasks.length > 0)" class="p-6 border-t border-gray-200 dark:border-gray-600">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Details</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Developer
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Task
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Project
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Hours
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Earnings
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Completed Date
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="developer in reportData.developers" :key="developer.id">
                                            <template v-for="task in developer.tasks" :key="task.name">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ developer.name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ task.name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ task.project }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ formatNumber(task.hours) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                                    ${{ formatNumber(task.earnings) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ task.completed_at ? formatDisplayDate(task.completed_at) : 'N/A' }}
                                                </td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Rework Analysis -->
            <div v-if="activeTab === 'rework'">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Rework Analysis</h3>
                        
                        <!-- Project Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Project
                            </label>
                            <select 
                                v-model="selectedProject" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select a project</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Start Date
                                </label>
                                <input 
                                    v-model="startDate" 
                                    type="date" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    End Date
                                </label>
                                <input 
                                    v-model="endDate" 
                                    type="date" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                >
                            </div>
                        </div>

                        <!-- Load Button -->
                        <div class="mb-6">
                            <button 
                                @click="loadReworkData"
                                :disabled="!selectedProject || loadingRework"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="loadingRework">Loading...</span>
                                <span v-else>Load Rework Data</span>
                            </button>
                        </div>

                        <!-- Rework Statistics -->
                        <div v-if="reworkStats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Rework Tasks</h4>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ reworkStats.total_tasks || 0 }}</p>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-red-600 dark:text-red-400">Total Rework Bugs</h4>
                                <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ reworkStats.total_bugs || 0 }}</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Total Rework Hours</h4>
                                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ formatNumber(reworkStats.total_hours || 0) }}</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-green-600 dark:text-green-400">Total Rework Cost</h4>
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">${{ formatNumber(reworkStats.total_cost || 0) }}</p>
                            </div>
                        </div>

                        <!-- Rework Items Table -->
                        <div v-if="reworkItems.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Rework Items</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Developer
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Item Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Rework Type
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Hours
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Cost
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Reason
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="item in reworkItems" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ item.developer_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ item.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ item.type }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ item.rework_type }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ formatNumber(item.hours) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                                ${{ formatNumber(item.cost) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                <div class="max-w-xs truncate" :title="item.rework_reason">
                                                    {{ item.rework_reason }}
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- No Data Message -->
                        <div v-else-if="!loadingRework && selectedProject" class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No rework data found for the selected project and date range.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    statistics: Object,
    recentReports: Array,
    pendingReports: Array,
    filters: Object,
    developers: Array,
    totalEarnings: Number,
    totalHours: Number,
});

const activeTab = ref('dashboard');

// Filtros para el dashboard
const filters = ref({
    start_date: '',
    end_date: ''
});

// Variables para generación de reportes
const selectedDevelopers = ref([]);
const startDate = ref('');
const endDate = ref('');
const quickPeriod = ref('');
const format = ref('excel');
const email = ref('');

// Nuevas variables para tipos de reporte
const reportType = ref('developers');
const selectedProject = ref('');
const selectedRole = ref('');
const selectedMonth = ref('');
const selectedWeek = ref('');

// Variables para mostrar reporte
const reportData = ref(null);
const showReport = ref(false);
const loadingReport = ref(false);

// Variables para proyectos y semanas
const projects = ref([]);
const availableMonths = ref([]);
const availableWeeks = ref([]);

// Variables para usuarios filtrados por proyecto
const projectUsers = ref([]);
const loadingUsers = ref(false);

// Variables para análisis de rework
const reworkStats = ref(null);
const reworkItems = ref([]);
const loadingRework = ref(false);

const isFormValid = computed(() => {
    return selectedDevelopers.value.length > 0 && 
           (startDate.value || quickPeriod.value) && 
           (endDate.value || quickPeriod.value);
});

// Función para inicializar las opciones de meses y semanas
const initializeDateOptions = () => {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    
    // Generate month options (last 12 months)
    availableMonths.value = [];
    for (let i = 0; i < 12; i++) {
        const month = new Date(currentYear, currentMonth - i, 1);
        availableMonths.value.push({
            value: month.toISOString().slice(0, 7), // YYYY-MM
            label: month.toLocaleDateString('en-US', { year: 'numeric', month: 'long' })
        });
    }
    
    // Set current month as default
    selectedMonth.value = availableMonths.value[0].value;
    updateWeekOptions();
};

// Function to update week options based on selected month
const updateWeekOptions = () => {
    if (!selectedMonth.value) return;
    
    const [year, month] = selectedMonth.value.split('-');
    const firstDay = new Date(parseInt(year), parseInt(month) - 1, 1);
    const lastDay = new Date(parseInt(year), parseInt(month), 0);
    const daysInMonth = lastDay.getDate();
    
    availableWeeks.value = [];
    
    // Calculate weeks of the month
    let weekStart = 1;
    let weekNumber = 1;
    
    while (weekStart <= daysInMonth) {
        const weekEnd = Math.min(weekStart + 6, daysInMonth);
        availableWeeks.value.push({
            value: weekNumber,
            label: `Week ${weekNumber} (${weekStart}-${weekEnd})`
        });
        weekStart += 7;
        weekNumber++;
    }
    
    // Set first week as default
    if (availableWeeks.value.length > 0) {
        selectedWeek.value = availableWeeks.value[0].value;
    }
};

// Function to get start and end dates of selected week
const getWeekDates = () => {
    if (!selectedMonth.value || !selectedWeek.value) return { start: '', end: '' };
    
    const [year, month] = selectedMonth.value.split('-');
    const weekNumber = parseInt(selectedWeek.value);
    const weekStart = (weekNumber - 1) * 7 + 1;
    const weekEnd = Math.min(weekStart + 6, new Date(parseInt(year), parseInt(month), 0).getDate());
    
    const startDate = new Date(parseInt(year), parseInt(month) - 1, weekStart);
    const endDate = new Date(parseInt(year), parseInt(month) - 1, weekEnd);
    
    return {
        start: startDate.toISOString().slice(0, 10),
        end: endDate.toISOString().slice(0, 10)
    };
};

// Function to load projects
const loadProjects = async () => {
    try {
        console.log('Loading projects...');
        const response = await fetch('/api/projects', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        });
        
        console.log('Response status:', response.status);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Projects data:', data);
            projects.value = data.projects || [];
            console.log('Projects loaded:', projects.value.length);
        } else {
            console.error('Error response:', response.status, response.statusText);
            const errorText = await response.text();
            console.error('Error details:', errorText);
        }
    } catch (error) {
        console.error('Error loading projects:', error);
    }
};

// Function to load rework data
const loadReworkData = async () => {
    if (!selectedProject.value) {
        reworkItems.value = [];
        reworkStats.value = null;
        return;
    }

    try {
        loadingRework.value = true;
        
        const params = new URLSearchParams({
            project_id: selectedProject.value,
            start_date: startDate.value || new Date(Date.now() - 6 * 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            end_date: endDate.value || new Date().toISOString().split('T')[0]
        });
        
        const response = await fetch(`/api/payments/rework?${params}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            reworkStats.value = data.stats;
            reworkItems.value = data.items || [];
        } else {
            console.error('Error loading rework data:', response.status);
            reworkItems.value = [];
            reworkStats.value = null;
        }
    } catch (error) {
        console.error('Error loading rework data:', error);
        reworkItems.value = [];
        reworkStats.value = null;
    } finally {
        loadingRework.value = false;
    }
};

// Function to load users by project
const loadUsersByProject = async (projectId, role = null) => {
    if (!projectId) {
        projectUsers.value = [];
        return;
    }

    try {
        loadingUsers.value = true;
        console.log('Loading users for project:', projectId, 'role:', role);
        
        const params = new URLSearchParams({ project_id: projectId });
        if (role) {
            params.append('role', role);
        }
        
        const response = await fetch(`/api/projects/users?${params}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        });
        
        console.log('Users response status:', response.status);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Users data:', data);
            projectUsers.value = data.users || [];
            console.log('Users loaded:', projectUsers.value.length);
        } else {
            console.error('Error response:', response.status, response.statusText);
            const errorText = await response.text();
            console.error('Error details:', errorText);
            projectUsers.value = [];
        }
    } catch (error) {
        console.error('Error loading users:', error);
        projectUsers.value = [];
    } finally {
        loadingUsers.value = false;
    }
};

const selectAll = () => {
    if (reportType.value === 'project' && selectedProject.value) {
        selectedDevelopers.value = projectUsers.value.map(d => d.id);
    } else {
        selectedDevelopers.value = props.developers.map(d => d.id);
    }
};

const deselectAll = () => {
    selectedDevelopers.value = [];
};

const setQuickPeriod = () => {
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
    
    switch (quickPeriod.value) {
        case 'this_week':
            startDate.value = startOfWeek.toISOString().split('T')[0];
            endDate.value = new Date(startOfWeek.getTime() + 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            break;
        case 'last_week':
            const lastWeekStart = new Date(startOfWeek.getTime() - 7 * 24 * 60 * 60 * 1000);
            startDate.value = lastWeekStart.toISOString().split('T')[0];
            endDate.value = new Date(lastWeekStart.getTime() + 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            break;
        case 'this_month':
            startDate.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate.value = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'last_month':
            startDate.value = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
            endDate.value = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
            break;
        case 'last_3_months':
            startDate.value = new Date(today.getFullYear(), today.getMonth() - 3, 1).toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'last_6_months':
            startDate.value = new Date(today.getFullYear(), today.getMonth() - 6, 1).toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'this_year':
            startDate.value = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'last_year':
            startDate.value = new Date(today.getFullYear() - 1, 0, 1).toISOString().split('T')[0];
            endDate.value = new Date(today.getFullYear() - 1, 11, 31).toISOString().split('T')[0];
            break;
        default:
            startDate.value = '';
            endDate.value = '';
    }
};

const formatDisplayDate = (dateString) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const generateReport = () => {
    if (!isFormValid.value) {
        alert('Please select developers and date range');
        return;
    }

    // Determine dates based on report type
    let reportStartDate = startDate.value;
    let reportEndDate = endDate.value;
    
    if (reportType.value === 'week') {
        const weekDates = getWeekDates();
        reportStartDate = weekDates.start;
        reportEndDate = weekDates.end;
    }

    const data = {
        developer_ids: selectedDevelopers.value,
        start_date: reportStartDate,
        end_date: reportEndDate,
        report_type: reportType.value,
        project_id: selectedProject.value || null,
        role: selectedRole.value || null,
        month: selectedMonth.value || null,
        week: selectedWeek.value || null
    };

    console.log('Generating report with data:', data);

    if (format.value === 'excel') {
        // Use fetch for direct Excel download
        loadingReport.value = true;
        console.log('Starting Excel download...');
        console.log('Data to send:', data);
        fetch('/api/download-excel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/octet-stream',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Network response was not ok');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `payment_report_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Reset form after successful generation
            selectedDevelopers.value = [];
            startDate.value = '';
            endDate.value = '';
            quickPeriod.value = '';
            email.value = '';
            reportType.value = 'developers';
            selectedProject.value = '';
            selectedRole.value = '';
            selectedMonth.value = '';
            selectedWeek.value = '';
        })
        .catch(error => {
            console.error('Detailed error:', error);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            
            // Show more detailed error
            let errorMonthsage = 'Error generating Excel report';
            if (error.message) {
                errorMonthsage += ': ' + error.message;
            }
            alert(errorMonthsage);
        })
        .finally(() => {
            loadingReport.value = false;
        });
    } else if (format.value === 'pdf') {
        // Use fetch for direct PDF download
        loadingReport.value = true;
        console.log('Starting PDF download...');
        console.log('Data to send:', data);
        fetch('/api/download-pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/pdf',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Network response was not ok');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `payment_report_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Reset form after successful generation
            selectedDevelopers.value = [];
            startDate.value = '';
            endDate.value = '';
            quickPeriod.value = '';
            email.value = '';
            reportType.value = 'developers';
            selectedProject.value = '';
            selectedRole.value = '';
            selectedMonth.value = '';
            selectedWeek.value = '';
        })
        .catch(error => {
            console.error('Detailed error:', error);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            
            // Show more detailed error
            let errorMonthsage = 'Error generating PDF report';
            if (error.message) {
                errorMonthsage += ': ' + error.message;
            }
            alert(errorMonthsage);
        })
        .finally(() => {
            loadingReport.value = false;
        });
    } else if (format.value === 'view') {
        // Use fetch to show report in system
        loadingReport.value = true;
        fetch('/api/show-report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok');
        })
        .then(data => {
            if (data.success) {
                reportData.value = data.data;
                showReport.value = true;
            } else {
                alert('Error generating report: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating report');
        })
        .finally(() => {
            loadingReport.value = false;
        });
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${hours}:${minutes.toString().padStart(2, '0')}`;
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric' 
    });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-blue-100 text-blue-800';
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const applyFilters = () => {
    router.get('/payments', filters.value);
};

const approveReport = (reportId) => {
    router.post(route('payments.reports.approve', reportId));
};

const markAsPaid = (reportId) => {
    router.post(route('payments.reports.mark-paid', reportId));
};

// Watch for report type changes to load projects when needed
watch(reportType, (newType) => {
    if (newType === 'project') {
        loadProjects();
    } else {
        // Clear project users when switching away from project type
        projectUsers.value = [];
        selectedDevelopers.value = [];
    }
});

// Watch for project selection changes
watch(selectedProject, (newProjectId) => {
    if (newProjectId && reportType.value === 'project') {
        loadUsersByProject(newProjectId, selectedRole.value);
    } else {
        projectUsers.value = [];
        selectedDevelopers.value = [];
    }
});

// Watch for role selection changes
watch(selectedRole, (newRole) => {
    if (selectedProject.value && reportType.value === 'project') {
        loadUsersByProject(selectedProject.value, newRole);
    }
});

// Watch for active tab changes to load rework data
watch(activeTab, (newTab) => {
    if (newTab === 'rework' && selectedProject.value) {
        loadReworkData();
    }
});

// Watch for project selection in rework tab
watch(selectedProject, (newProjectId) => {
    if (activeTab.value === 'rework' && newProjectId) {
        loadReworkData();
    }
});

// Initialize options when component mounts
onMounted(() => {
    initializeDateOptions();
    // Load projects immediately if report type is already set to project
    if (reportType.value === 'project') {
        loadProjects();
    }
});
</script> 
