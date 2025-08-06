<template>
  <AppLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Developer Activity Dashboard</h1>
        <p class="mt-2 text-gray-600">Monitor developer work patterns and time zones</p>
      </div>

      <!-- Time Zone Info -->
      <div class="bg-card rounded-lg border p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Time Zone Information</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">Colombia</div>
            <div class="text-lg font-bold">{{ timezoneInfo.colombia_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.colombia_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">Italy</div>
            <div class="text-lg font-bold">{{ timezoneInfo.italy_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.italy_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">Spain</div>
            <div class="text-lg font-bold">{{ timezoneInfo.spain_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.spain_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">Mexico</div>
            <div class="text-lg font-bold">{{ timezoneInfo.mexico_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.mexico_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">Argentina</div>
            <div class="text-lg font-bold">{{ timezoneInfo.argentina_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.argentina_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">Brazil</div>
            <div class="text-lg font-bold">{{ timezoneInfo.brazil_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.brazil_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">USA (East)</div>
            <div class="text-lg font-bold">{{ timezoneInfo.usa_east_time }}</div>
            <div class="text-xs text-muted-foreground">{{ timezoneInfo.usa_east_offset }}</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-sm text-muted-foreground font-medium">UTC</div>
            <div class="text-lg font-bold">{{ timezoneInfo.utc_time }}</div>
            <div class="text-xs text-muted-foreground">Universal Time</div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-card rounded-lg border p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-2">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Developer</label>
            <select
              v-model="filters.developer_id"
              class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring"
            >
              <option value="">All Developers</option>
              <option v-for="dev in developers" :key="dev.id" :value="dev.id">
                {{ dev.name }}
              </option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="applyFilters"
              class="w-full bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Team Overview -->
      <div class="bg-card rounded-lg border p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Team Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ teamOverview.total_developers }}</div>
            <div class="text-sm text-muted-foreground">Total Developers</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ teamOverview.team_preferred_work_time }}</div>
            <div class="text-sm text-muted-foreground">Preferred Work Time</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ getTotalActivity() }}</div>
            <div class="text-sm text-muted-foreground">Total Activities</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ getAverageSessionDuration() }}</div>
            <div class="text-sm text-muted-foreground">Avg Session (min)</div>
          </div>
        </div>

        <!-- Activity by Period Chart -->
        <div class="mb-6">
          <h3 class="text-lg font-medium mb-4">Activity by Time Period</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(count, period) in teamOverview.team_activity_by_period" :key="period" class="text-center">
              <div class="text-2xl font-bold">{{ count }}</div>
              <div class="text-sm text-muted-foreground capitalize">{{ period }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Individual Developer Stats -->
      <div v-if="developerStats" class="bg-card rounded-lg border p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Developer: {{ developerStats.developer }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ developerStats.total_sessions }}</div>
            <div class="text-sm text-muted-foreground">Total Sessions</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ developerStats.avg_session_duration_minutes }}</div>
            <div class="text-sm text-muted-foreground">Avg Session (min)</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ developerStats.task_activities }}</div>
            <div class="text-sm text-muted-foreground">Task Activities</div>
          </div>
          <div class="text-center p-4 bg-muted rounded-lg">
            <div class="text-2xl font-bold">{{ developerStats.preferred_work_time }}</div>
            <div class="text-sm text-muted-foreground">Preferred Time</div>
          </div>
        </div>

        <!-- Developer Activity by Period -->
        <div class="mb-6">
          <h3 class="text-lg font-medium mb-4">Activity by Time Period</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(count, period) in developerStats.activity_by_period" :key="period" class="text-center">
              <div class="text-2xl font-bold">{{ count }}</div>
              <div class="text-sm text-muted-foreground capitalize">{{ period }}</div>
            </div>
          </div>
        </div>

        <!-- Most Active Hours -->
        <div v-if="Object.keys(developerStats.most_active_hours).length > 0">
          <h3 class="text-lg font-medium mb-4">Most Active Hours</h3>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div v-for="(count, hour) in developerStats.most_active_hours" :key="hour" class="text-center">
              <div class="text-2xl font-bold">{{ count }}</div>
              <div class="text-sm text-muted-foreground">{{ formatHour(hour) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Patterns -->
      <div class="bg-card rounded-lg border p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Daily Activity Patterns</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-border">
            <thead class="bg-muted">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Morning</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Afternoon</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Evening</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Night</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr v-for="(patterns, date) in dailyPatterns" :key="date">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ formatDate(date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ getActivityCount(patterns, 'morning') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ getActivityCount(patterns, 'afternoon') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ getActivityCount(patterns, 'evening') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ getActivityCount(patterns, 'night') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ getTotalForDate(patterns) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Export Options -->
      <div class="bg-card rounded-lg border p-6">
        <h2 class="text-xl font-semibold mb-4">Export Report</h2>
        <div class="flex space-x-4">
          <button
            @click="exportReport('excel')"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Export to Excel
          </button>
          <button
            @click="exportReport('pdf')"
            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Export to PDF
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'

const props = defineProps({
  developers: Array,
  teamOverview: Object,
  developerStats: Object,
  dailyPatterns: Object,
  timezoneInfo: Object,
  filters: Object
})

const filters = ref(props.filters)

const applyFilters = () => {
  router.get('/developer-activity', filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const exportReport = (format) => {
  const params = {
    format,
    ...filters.value
  }
  
  // Create a temporary form to download the file
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/developer-activity/export'
  
  // Add CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const csrfInput = document.createElement('input')
  csrfInput.type = 'hidden'
  csrfInput.name = '_token'
  csrfInput.value = csrfToken
  form.appendChild(csrfInput)
  
  // Add parameters
  Object.keys(params).forEach(key => {
    if (params[key]) {
      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = key
      input.value = params[key]
      form.appendChild(input)
    }
  })
  
  document.body.appendChild(form)
  form.submit()
  document.body.removeChild(form)
}

const getTotalActivity = () => {
  return Object.values(props.teamOverview.team_activity_by_period).reduce((sum, count) => sum + count, 0)
}

const getAverageSessionDuration = () => {
  const totalDuration = props.teamOverview.developers.reduce((sum, dev) => sum + dev.avg_session_duration_minutes, 0)
  return Math.round(totalDuration / props.teamOverview.developers.length)
}

const formatHour = (hour) => {
  return `${hour}:00`
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const getActivityCount = (patterns, period) => {
  const pattern = patterns.find(p => p.time_period === period)
  return pattern ? pattern.activity_count : 0
}

const getTotalForDate = (patterns) => {
  return patterns.reduce((sum, pattern) => sum + pattern.activity_count, 0)
}
</script> 