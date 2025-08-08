<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  Settings, 
  Bell, 
  User, 
  Shield,
  Palette,
  Globe,
  Save
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/team-leader/dashboard',
    },
    {
        title: 'Settings',
        href: '/team-leader/settings',
    },
];

interface Props {
    user: any;
}

const props = defineProps<Props>();

const saveSettings = () => {
    console.log('Saving settings...');
    // Implement save logic
};
</script>

<template>
    <Head title="Settings - Team Leader" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
                    <p class="text-gray-600 dark:text-gray-400">Customize your experience as Team Leader</p>
                </div>
                <Button @click="saveSettings" size="sm">
                    <Save class="h-4 w-4 mr-2" />
                    Save Changes
                </Button>
            </div>

            <!-- Settings Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Profile Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <User class="h-5 w-5 mr-2" />
                            Profile
                        </CardTitle>
                        <CardDescription>Configure your personal information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Name
                            </label>
                            <input 
                                type="text" 
                                :value="user.name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Your name"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input 
                                type="email" 
                                :value="user.email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="your@email.com"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Roleeeeeeeeee
                            </label>
                            <div class="flex items-center space-x-2">
                                <Badge variant="outline" class="bg-blue-100 text-blue-800">
                                    Team Leader
                                </Badge>
                                <span class="text-xs text-gray-500">Roleeeeeeeeee assigned by administrator</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Notification Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Bell class="h-5 w-5 mr-2" />
                            Notifications
                        </CardTitle>
                        <CardDescription>Configure your notification preferences</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Email Notifications</h4>
                                <p class="text-xs text-gray-500">Receive notifications by email</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Push Notifications</h4>
                                <p class="text-xs text-gray-500">Real-time notifications in browser</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Tasks Approved by QA</h4>
                                <p class="text-xs text-gray-500">Notifications when QA approves tasks</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Bugs Approved by QA</h4>
                                <p class="text-xs text-gray-500">Notifications when QA approves bugs</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                    </CardContent>
                </Card>

                <!-- Security Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Shield class="h-5 w-5 mr-2" />
                            Security
                        </CardTitle>
                        <CardDescription>Configure your account security</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Current Password
                            </label>
                            <input 
                                type="password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="••••••••"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                New Password
                            </label>
                            <input 
                                type="password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="••••••••"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Confirm New Password
                            </label>
                            <input 
                                type="password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="••••••••"
                            />
                        </div>
                        <Button variant="outline" size="sm" class="w-full">
                            Change Password
                        </Button>
                    </CardContent>
                </Card>

                <!-- Appearance Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Palette class="h-5 w-5 mr-2" />
                            Appearance
                        </CardTitle>
                        <CardDescription>Customize the interface appearance</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Theme
                            </label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="system">System</option>
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Language
                            </label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="es">Spanish</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Show Badges</h4>
                                <p class="text-xs text-gray-500">Show counters in sidebar</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Animations</h4>
                                <p class="text-xs text-gray-500">Enable interface animations</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                    </CardContent>
                </Card>

                <!-- Team Leader Specific Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Settings class="h-5 w-5 mr-2" />
                            Team Leader Settings
                        </CardTitle>
                        <CardDescription>Specific settings for your role</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Active Tasks Limit per Developer
                            </label>
                            <input 
                                type="number" 
                                value="3"
                                min="1"
                                max="10"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <p class="text-xs text-gray-500 mt-1">Maximum number of active tasks per developer</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Automatic Review</h4>
                                <p class="text-xs text-gray-500">Automatically review tasks approved by QA</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Team Notifications</h4>
                                <p class="text-xs text-gray-500">Receive team activity notifications</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Automatic Reports</h4>
                                <p class="text-xs text-gray-500">Generate automatic reports weekly</p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        </div>
                    </CardContent>
                </Card>

                <!-- System Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Globe class="h-5 w-5 mr-2" />
                            System Info
                        </CardTitle>
                        <CardDescription>Information about your account and system</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">User ID</span>
                            <span class="text-sm text-gray-500">{{ user.id }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Member Since</span>
                            <span class="text-sm text-gray-500">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Access</span>
                            <span class="text-sm text-gray-500">{{ new Date().toLocaleDateString() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">System Version</span>
                            <span class="text-sm text-gray-500">v1.0.0</span>
                        </div>
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Button variant="outline" size="sm" class="w-full">
                                View Activity Logs
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <Button @click="saveSettings" size="lg">
                    <Save class="h-4 w-4 mr-2" />
                    Save Settings
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
