<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue'
// import { type BreadcrumbItem } from '@/types'
import { Head, usePage, router } from '@inertiajs/vue3'

import CreateUserModal from '@/components/CreateUserModal.vue'
import { useToast } from '@/composables/useToast'
import { watch, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import Badge from '@/components/ui/badge/Badge.vue'
import Icon from '@/components/Icon.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'



type UserStatus = 'active' | 'inactive' | 'completed' | 'cancelled' | 'paused'

interface User {
  id: string,
  name: string,
  email: string,
  nickname: string,
  hour_value: number,
  work_time: string,
  status: UserStatus,
  avatar?: string,
  roles: any[],
  projects?: any[],
  tasks?: any[]
}

const props = defineProps<{
  users: {
    data: User[],
    current_page: number,
    last_page: number,
    per_page: number,
    total: number,
    from: number,
    to: number,
    links: any[]
  },
  stats?: {
    total: number,
    active: number,
    developers: number,
    admins: number
  },
  filters?: {
    search: string,
    status: string,
    role: string,
    per_page: number
  }
}>()

const { success } = useToast()
const page = usePage()

// Los roles ahora se cargan correctamente desde el middleware

// Filtros reactivos
const search = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')
const roleFilter = ref(props.filters?.role || '')
const perPage = ref(props.filters?.per_page || 10)

// Watch for flash messages from the server
watch(() => page.props['flash'], (flash: any) => {
  if (flash?.success) {
    success('Success', flash.success)
  }
}, { immediate: true })

// Aplicar filtros
const applyFilters = () => {
  router.get('/users', {
    search: search.value,
    status: statusFilter.value,
    role: roleFilter.value,
    per_page: perPage.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

// Limpiar filtros
const clearFilters = () => {
  search.value = ''
  statusFilter.value = ''
  roleFilter.value = ''
  perPage.value = 10
  applyFilters()
}

// Navegar a página
const goToPage = (pageNumber: number) => {
  router.get('/users', {
    search: search.value,
    status: statusFilter.value,
    role: roleFilter.value,
    per_page: perPage.value,
    page: pageNumber
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'inactive':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'paused':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'active':
      return 'check-circle'
    case 'inactive':
      return 'x-circle'
    case 'paused':
      return 'pause-circle'
    default:
      return 'circle'
  }
}

const getRoleBadge = (roles: any[]) => {
  if (!roles || roles.length === 0) return { label: 'No Role', class: 'bg-gray-100 text-gray-800' }
  
  const role = roles[0]
  switch (role.value) {
    case 'admin':
      return { label: 'Admin', class: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400' }
    case 'developer':
      return { label: 'Developer', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' }
    default:
      return { label: role.value, class: 'bg-gray-100 text-gray-800' }
  }
}

const getUserStats = (user: User) => {
  const projects = user.projects?.length || 0
  const totalTasks = user.tasks?.length || 0
  const completedTasks = user.tasks?.filter((task: any) => task.status === 'done').length || 0
  const inProgressTasks = user.tasks?.filter((task: any) => task.status === 'in progress').length || 0

  return {
    projects,
    totalTasks,
    completedTasks,
    inProgressTasks
  }
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}
</script>

<template>
  <Head title="Users" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Users</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Manage and track your development team
          </p>
        </div>
        <CreateUserModal />
      </div>
    </template>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Users</CardTitle>
          <Icon name="users" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats?.total || 0 }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active Users</CardTitle>
          <Icon name="user-check" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats?.active || 0 }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Developers</CardTitle>
          <Icon name="code" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats?.developers || 0 }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Admins</CardTitle>
          <Icon name="shield" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats?.admins || 0 }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <CardHeader>
        <CardTitle class="text-lg">Filters</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Label for="search">Search</Label>
            <Input
              id="search"
              v-model="search"
              placeholder="Search by name, email..."
              @keyup.enter="applyFilters"
            />
          </div>
          
          <div>
            <Label for="status">Status</Label>
            <Select v-model="statusFilter">
              <SelectTrigger>
                <SelectValue placeholder="All statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All statuses</SelectItem>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
                <SelectItem value="paused">Paused</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="role">Role</Label>
            <Select v-model="roleFilter">
              <SelectTrigger>
                <SelectValue placeholder="All roles" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All roles</SelectItem>
                <SelectItem value="admin">Admin</SelectItem>
                <SelectItem value="developer">Developer</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="per_page">Per Page</Label>
            <Select v-model="perPage">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="10">10</SelectItem>
                <SelectItem :value="25">25</SelectItem>
                <SelectItem :value="50">50</SelectItem>
                <SelectItem :value="100">100</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="flex gap-2 mt-4">
          <Button @click="applyFilters" variant="default">
            <Icon name="search" class="h-4 w-4 mr-2" />
            Apply Filters
          </Button>
          <Button @click="clearFilters" variant="outline">
            <Icon name="x" class="h-4 w-4 mr-2" />
            Clear
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Users Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="user in users.data" :key="user.id" class="hover:shadow-lg transition-shadow">
        <CardHeader>
          <div class="flex items-center space-x-4">
            <Avatar class="h-12 w-12">
              <AvatarImage :src="user.avatar || ''" :alt="user.name" />
              <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
            </Avatar>
            <div class="flex-1 min-w-0">
              <h3 class="text-lg font-semibold truncate">{{ user.name }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ user.email }}</p>
            </div>
          </div>
        </CardHeader>

        <CardContent>
          <div class="space-y-3">
            <!-- Status and Role -->
            <div class="flex gap-2">
              <Badge :class="getStatusClass(user.status)">
                <Icon :name="getStatusIcon(user.status)" class="h-3 w-3 mr-1" />
                {{ user.status }}
              </Badge>
              <Badge :class="getRoleBadge(user.roles).class">
                {{ getRoleBadge(user.roles).label }}
              </Badge>
            </div>

            <!-- User Info -->
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Hour Rate:</span>
                <span class="font-medium">{{ formatCurrency(user.hour_value) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Work Time:</span>
                <span class="font-medium">{{ user.work_time }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Nickname:</span>
                <span class="font-medium">{{ user.nickname }}</span>
              </div>
            </div>

            <!-- User Stats -->
            <div class="grid grid-cols-2 gap-4 pt-2 border-t">
              <div class="text-center">
                <div class="text-lg font-semibold">{{ getUserStats(user).projects }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Projects</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold">{{ getUserStats(user).completedTasks }}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Completed Tasks</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 pt-2">
              <Button variant="outline" size="sm" class="flex-1">
                <Icon name="eye" class="h-4 w-4 mr-1" />
                View
              </Button>
              <Button variant="outline" size="sm" class="flex-1">
                <Icon name="edit" class="h-4 w-4 mr-1" />
                Edit
              </Button>
                    <!-- TEMPORARILY DISABLED - Login as User functionality -->
      <!-- 
      <Button 
        v-if="(page.props as any)['auth']?.user?.roles?.some((role: any) => role.name === 'admin')"
        variant="secondary" 
        size="sm" 
        class="flex-1"
        @click="router.get(route('admin.login-as-user', user.id))"
      >
        <Icon name="log-in" class="h-4 w-4 mr-1" />
        Login As
      </Button>
      -->
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Pagination -->
    <div v-if="users.last_page > 1" class="mt-8">
      <Card>
        <CardContent class="py-4">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
            </div>
            
            <div class="flex items-center space-x-2">
              <Button
                variant="outline"
                size="sm"
                :disabled="users.current_page === 1"
                @click="goToPage(users.current_page - 1)"
              >
                <Icon name="chevron-left" class="h-4 w-4" />
                Previous
              </Button>

              <div class="flex items-center space-x-1">
                <Button
                  v-for="link in users.links"
                  :key="link.label"
                  variant="outline"
                  size="sm"
                  :class="{
                    'bg-primary text-primary-foreground': link.active,
                    'opacity-50 cursor-not-allowed': !link.url
                  }"
                  :disabled="!link.url"
                  @click="link.url ? goToPage(parseInt(link.label)) : null"
                >
                  {{ link.label }}
                </Button>
              </div>

              <Button
                variant="outline"
                size="sm"
                :disabled="users.current_page === users.last_page"
                @click="goToPage(users.current_page + 1)"
              >
                Next
                <Icon name="chevron-right" class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- No Users Message -->
    <div v-if="users.data.length === 0" class="text-center py-12">
      <Icon name="users" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users found</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        {{ search || statusFilter || roleFilter ? 'Try adjusting your filters.' : 'Get started by creating your first user.' }}
      </p>
      <CreateUserModal />
    </div>
  </AppLayout>
</template>
