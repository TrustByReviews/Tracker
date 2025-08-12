<template>
  <Head title="Project Analytics" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Project Analytics</h1>
          <p class="text-muted-foreground">
            Comprehensive insights into project performance and trends
          </p>
        </div>
      </div>

      <!-- Key Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
            <FolderIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ analytics.total_projects }}</div>
            <p class="text-xs text-muted-foreground">
              Across all categories and priorities
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Projects</CardTitle>
            <PlayIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ analytics.active_projects }}</div>
            <p class="text-xs text-muted-foreground">
              Currently in development
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Budget</CardTitle>
            <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">${{ formatCurrency(analytics.total_budget) }}</div>
            <p class="text-xs text-muted-foreground">
              Estimated across all projects
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Average Progress</CardTitle>
            <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ analytics.average_progress }}%</div>
            <p class="text-xs text-muted-foreground">
              Across all projects
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Budget Efficiency -->
      <Card>
        <CardHeader>
          <CardTitle>Budget Efficiency</CardTitle>
          <CardDescription>
            Overview of budget utilization across projects
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ analytics.budget_efficiency }}%</div>
              <div class="text-sm text-muted-foreground">Efficiency Rate</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold">${{ formatCurrency(analytics.used_budget) }}</div>
              <div class="text-sm text-muted-foreground">Used Budget</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold">{{ analytics.completed_tasks }}</div>
              <div class="text-sm text-muted-foreground">Completed Tasks</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Performance by Category -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Performance by Category</CardTitle>
            <CardDescription>
              Project performance metrics by category
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="(data, category) in categoryPerformance" :key="category" class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 rounded-full" :class="getCategoryColor(category)"></div>
                  <span class="font-medium capitalize">{{ category }}</span>
                </div>
                <div class="text-right">
                  <div class="font-semibold">{{ data.count }} projects</div>
                  <div class="text-sm text-muted-foreground">{{ data.average_progress }}% progress</div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Performance by Priority</CardTitle>
            <CardDescription>
              Project performance metrics by priority level
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="(data, priority) in priorityPerformance" :key="priority" class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <Badge :class="getPriorityClass(priority)" class="capitalize">{{ priority }}</Badge>
                </div>
                <div class="text-right">
                  <div class="font-semibold">{{ data.count }} projects</div>
                  <div class="text-sm text-muted-foreground">{{ data.average_progress }}% progress</div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Technology Stack Analysis -->
      <Card>
        <CardHeader>
          <CardTitle>Technology Stack Analysis</CardTitle>
          <CardDescription>
            Most commonly used technologies across projects
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div v-for="(count, tech) in topTechnologies" :key="tech" class="text-center p-4 border rounded-lg">
              <div class="text-lg font-semibold">{{ tech }}</div>
              <div class="text-sm text-muted-foreground">{{ count }} projects</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Monthly Trend -->
      <Card>
        <CardHeader>
          <CardTitle>Project Creation Trend</CardTitle>
          <CardDescription>
            Monthly project creation over time
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="(count, month) in monthlyTrend" :key="month" class="flex items-center justify-between">
              <span class="font-medium">{{ formatMonth(month) }}</span>
              <div class="flex items-center space-x-2">
                <div class="w-32 bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full" 
                    :style="{ width: getTrendPercentage(count) + '%' }"
                  ></div>
                </div>
                <span class="text-sm font-medium">{{ count }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { 
  FolderIcon, 
  PlayIcon, 
  DollarSignIcon, 
  TrendingUpIcon 
} from 'lucide-vue-next'

interface Analytics {
  total_projects: number
  active_projects: number
  completed_projects: number
  paused_projects: number
  total_budget: number
  used_budget: number
  budget_efficiency: number
  average_progress: number
  total_tasks: number
  completed_tasks: number
}

interface CategoryPerformance {
  [key: string]: {
    count: number
    average_progress: number
    total_budget: number
    used_budget: number
  }
}

interface PriorityPerformance {
  [key: string]: {
    count: number
    average_progress: number
    total_budget: number
    used_budget: number
  }
}

interface TechnologyUsage {
  [key: string]: number
}

interface MonthlyTrend {
  [key: string]: number
}

interface Props {
  analytics: Analytics
  categoryPerformance: CategoryPerformance
  priorityPerformance: PriorityPerformance
  technologyUsage: TechnologyUsage
  monthlyTrend: MonthlyTrend
}

const props = defineProps<Props>()

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Reports', href: '/reports' },
  { name: 'Project Analytics', href: '/analytics/projects' }
]

// Helper functions
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getCategoryColor = (category: string) => {
  const colors = {
    web: 'bg-blue-500',
    mobile: 'bg-green-500',
    backend: 'bg-purple-500',
    iot: 'bg-orange-500',
    desktop: 'bg-red-500'
  }
  return colors[category as keyof typeof colors] || 'bg-gray-500'
}

const getPriorityClass = (priority: string) => {
  switch (priority) {
    case 'high':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'medium':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'low':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const topTechnologies = computed(() => {
  const sorted = Object.entries(props.technologyUsage)
    .sort(([,a], [,b]) => b - a)
    .slice(0, 12)
  return Object.fromEntries(sorted)
})

const getTrendPercentage = (count: number) => {
  const maxCount = Math.max(...Object.values(props.monthlyTrend))
  return maxCount > 0 ? (count / maxCount) * 100 : 0
}

const formatMonth = (monthKey: string) => {
  const [year, month] = monthKey.split('-')
  const date = new Date(parseInt(year), parseInt(month) - 1)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' })
}
</script>
