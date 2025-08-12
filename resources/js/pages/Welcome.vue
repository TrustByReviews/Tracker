<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

/**
 * Welcome Component
 * 
 * This is the landing page component that serves as the main entry point for the application.
 * It provides an attractive and informative introduction to the project management system,
 * highlighting key features and encouraging user registration/login.
 * 
 * Features:
 * - Modern gradient design with glassmorphism effects
 * - Feature showcase with icons and descriptions
 * - Call-to-action buttons for login/registration
 * - Responsive design for all devices
 * - Smooth animations and hover effects
 * 
 * @component
 * @example
 * <Welcome 
 *   :canLogin="true"
 *   :canRegister="true"
 * />
 */

/**
 * Component props for welcome page functionality
 */
defineProps<{
    canLogin?: boolean;      // Whether login functionality is available
    canRegister?: boolean;   // Whether registration functionality is available
}>();
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Navigation -->
        <nav class="relative z-10 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white">Tracker</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <Link v-if="canLogin" :href="route('login')" 
                          class="text-white hover:text-blue-300 transition-colors">
                        Log in
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative z-10 px-6 py-20">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6">
                    Advanced Project Management
                    <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                        & Time Tracking
                    </span>
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-4xl mx-auto">
                    Complete development workflow solution with role-based dashboards, automated payment tracking, 
                    QA testing workflows, and comprehensive reporting. Built for modern development teams.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link v-if="canLogin" :href="route('login')"
                          class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105">
                        Get Started
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </Link>
                    
                    <a href="#features" 
                       class="inline-flex items-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-all">
                        Explore Features
                    </a>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="relative z-10 px-6 py-20 bg-white/5 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        Complete Development Workflow
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        From project planning to payment processing, everything you need to manage development teams efficiently.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1: Role-Based Dashboards -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20 hover:bg-white/20 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Role-Based Dashboards</h3>
                        <p class="text-gray-300">
                            Customized dashboards for Admins, Team Leaders, Developers, and QA testers with role-specific features and permissions.
                        </p>
                    </div>

                    <!-- Feature 2: Advanced Time Tracking -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20 hover:bg-white/20 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Advanced Time Tracking</h3>
                        <p class="text-gray-300">
                            Real-time work tracking with start/pause/resume functionality, automatic time calculations, and efficiency metrics.
                        </p>
                    </div>

                    <!-- Feature 3: Automated Payment System -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20 hover:bg-white/20 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Automated Payment System</h3>
                        <p class="text-gray-300">
                            Weekly payment reports, automatic calculations based on hourly rates, Excel/PDF exports, and email notifications.
                        </p>
                    </div>

                    <!-- Feature 4: QA Testing Workflow -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20 hover:bg-white/20 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">QA Testing Workflow</h3>
                        <p class="text-gray-300">
                            Complete QA testing pipeline with task/bug approval workflows, testing time tracking, and automated notifications.
                        </p>
                    </div>

                    <!-- Feature 5: Real-time Notifications -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20 hover:bg-white/20 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.19 4.19A2 2 0 006 3h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Real-time Notifications</h3>
                        <p class="text-gray-300">
                            Instant notifications for task assignments, QA approvals, payment reports, and system updates.
                        </p>
                    </div>

                    <!-- Feature 6: Comprehensive Reporting -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20 hover:bg-white/20 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-4">Comprehensive Reporting</h3>
                        <p class="text-gray-300">
                            Detailed analytics, performance metrics, project summaries, and exportable reports in multiple formats.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Features Section -->
        <div class="relative z-10 px-6 py-20">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        Latest Enhancements
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        Recent updates and improvements to make your development workflow even more efficient.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Enhancement 1: Bug Management -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-white">Enhanced Bug Management</h3>
                                <p class="text-gray-400 text-sm">Complete bug lifecycle tracking</p>
                            </div>
                        </div>
                        <ul class="text-gray-300 space-y-2">
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Bug creation with detailed reporting</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Environment and browser tracking</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>QA testing workflow integration</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Automatic time tracking for bug fixes</li>
                        </ul>
                    </div>

                    <!-- Enhancement 2: Advanced Permissions -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-white">Advanced Permissions</h3>
                                <p class="text-gray-400 text-sm">Granular access control system</p>
                            </div>
                        </div>
                        <ul class="text-gray-300 space-y-2">
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Role-based access control</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Project-specific permissions</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Simultaneous task limits</li>
                            <li class="flex items-center"><span class="w-2 h-2 bg-green-400 rounded-full mr-3"></span>Admin override capabilities</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="relative z-10 px-6 py-20">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Ready to Transform Your Development Workflow?
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    Join development teams who have already improved their project management, time tracking, and payment processing with Tracker.
                </p>
                <Link v-if="canLogin" :href="route('login')"
                      class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg text-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105">
                    Start Your Free Trial
                    <svg class="ml-2 w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 px-6 py-12 border-t border-white/10">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">Tracker</span>
                    </div>
                    
                    <div class="text-gray-400 text-sm">
                        © {{ new Date().getFullYear() }} Tracker. Advanced Project Management & Time Tracking System.
                    </div>
                </div>
            </div>
        </footer>

        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute top-40 left-40 w-80 h-80 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
