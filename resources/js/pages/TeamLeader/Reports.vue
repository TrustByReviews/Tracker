<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  BarChart3, 
  TrendingUp, 
  Users, 
  CheckSquare, 
  Bug,
  Calendar,
  Download,
  Filter
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/team-leader/dashboard',
    },
    {
        title: 'Reports',
        href: '/team-leader/reports',
    },
];

interface Props {
    user: any;
    reports: any[];
}

const props = defineProps<Props>();

const generateReport = (type: string) => {
    console.log('Generating report:', type);
    // Implement report generation logic
};

const downloadReport = (reportId: string) => {
    console.log('Downloading report:', reportId);
    // Implement download logic
};
</script>

<template>
    <Head title="Reports - Team Leader" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports</h1>
                    <p class="text-gray-600 dark:text-gray-400">Analyze your team and project performance</p>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" size="sm">
                        <Filter class="h-4 w-4 mr-2" />
                        Filters
                    </Button>
                </div>
            </div>

            <!-- Quick Report Generation -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="hover:shadow-lg transition-shadow cursor-pointer" @click="generateReport('team_performance')">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Team Performance</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">View Report</div>
                        <p class="text-xs text-muted-foreground">Productivity analysis</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow cursor-pointer" @click="generateReport('project_progress')">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Project Progress</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">View Report</div>
                        <p class="text-xs text-muted-foreground">Project status</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow cursor-pointer" @click="generateReport('sprint_metrics')">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sprint Metrics</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">View Report</div>
                        <p class="text-xs text-muted-foreground">Velocity and burndown</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow cursor-pointer" @click="generateReport('quality_metrics')">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Quality Metrics</CardTitle>
                        <CheckSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600">View Report</div>
                        <p class="text-xs text-muted-foreground">Bugs and defects</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Detailed Reports -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Team Performance Report -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Users class="h-5 w-5 mr-2" />
                            Team Performance
                        </CardTitle>
                        <CardDescription>Productivity analysis by developer</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Completed Tasks</span>
                                <Badge variant="outline">24</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Average Time</span>
                                <Badge variant="outline">8.5h</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Resolved Bugs</span>
                                <Badge variant="outline">12</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">QA Satisfaction</span>
                                <Badge variant="outline" class="bg-green-100 text-green-800">95%</Badge>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Button @click="generateReport('team_performance')" size="sm" variant="outline" class="w-full">
                                <Download class="h-4 w-4 mr-2" />
                                Generate Report
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Project Progress Report -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <TrendingUp class="h-5 w-5 mr-2" />
                            Project Progress
                        </CardTitle>
                        <CardDescription>Current status of all projects</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Active Projects</span>
                                <Badge variant="outline">3</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Sprints in Progress</span>
                                <Badge variant="outline">5</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Pending Tasks</span>
                                <Badge variant="outline" class="bg-orange-100 text-orange-800">8</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Overall Progress</span>
                                <Badge variant="outline" class="bg-blue-100 text-blue-800">78%</Badge>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Button @click="generateReport('project_progress')" size="sm" variant="outline" class="w-full">
                                <Download class="h-4 w-4 mr-2" />
                                Generate Report
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sprint Metrics Report -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <Calendar class="h-5 w-5 mr-2" />
                            Sprint Metrics
                        </CardTitle>
                        <CardDescription>Velocity and burndown analysis</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Average Velocity</span>
                                <Badge variant="outline">15 pts</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Current Sprint</span>
                                <Badge variant="outline">Sprint 3</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Completed Story Points</span>
                                <Badge variant="outline">12/20</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Days Remaining</span>
                                <Badge variant="outline" class="bg-yellow-100 text-yellow-800">3</Badge>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Button @click="generateReport('sprint_metrics')" size="sm" variant="outline" class="w-full">
                                <Download class="h-4 w-4 mr-2" />
                                Generate Report
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quality Metrics Report -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <CheckSquare class="h-5 w-5 mr-2" />
                            Quality Metrics
                        </CardTitle>
                        <CardDescription>Bug and defect analysis</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Total Bugs</span>
                                <Badge variant="outline">18</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Resolved Bugs</span>
                                <Badge variant="outline" class="bg-green-100 text-green-800">12</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">QA Approval Rate</span>
                                <Badge variant="outline" class="bg-blue-100 text-blue-800">92%</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">Average Resolution Time</span>
                                <Badge variant="outline">2.5 days</Badge>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Button @click="generateReport('quality_metrics')" size="sm" variant="outline" class="w-full">
                                <Download class="h-4 w-4 mr-2" />
                                Generate Report
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Reports -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <BarChart3 class="h-5 w-5 mr-2" />
                        Recent Reports
                    </CardTitle>
                    <CardDescription>Reports generated in the last 30 days</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="reports.length === 0" class="text-center py-8">
                        <BarChart3 class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No recent reports</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Generate your first report to see it here.</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="report in reports" :key="report.id" 
                             class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <BarChart3 class="h-5 w-5 text-gray-500" />
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ report.name }}</h4>
                                    <p class="text-xs text-gray-500">Generated on {{ report.created_at }}</p>
                                </div>
                            </div>
                            <Button @click="downloadReport(report.id)" size="sm" variant="outline">
                                <Download class="h-4 w-4 mr-1" />
                                Download
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
