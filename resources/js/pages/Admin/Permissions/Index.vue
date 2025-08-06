<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gestión de Permisos de Tareas Simultáneas</h1>
        <p class="mt-2 text-gray-600">Administra los permisos para trabajar en múltiples tareas simultáneamente</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Usuarios</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.total_users }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Con Permiso</p>
              <p class="text-2xl font-semibold text-green-600">{{ stats.users_with_permission }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Sin Permiso</p>
              <p class="text-2xl font-semibold text-yellow-600">{{ stats.users_without_permission }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Proyectos</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.total_projects }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Management Section -->
      <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Gestión por Equipos</h2>
          <p class="text-sm text-gray-600">Otorga o revoca permisos a equipos completos</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Proyecto</label>
              <select v-model="selectedProject" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar un proyecto...</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>
            <div class="flex items-end space-x-4">
              <button
                @click="grantPermissionToTeam"
                :disabled="!selectedProject || loading"
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Otorgar a Equipo
              </button>
              <button
                @click="revokePermissionFromTeam"
                :disabled="!selectedProject || loading"
                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Revocar de Equipo
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Usuarios</h2>
          <p class="text-sm text-gray-600">Gestiona permisos individuales</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proyectos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tareas Activas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permiso</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ user.name.charAt(0) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-red-100 text-red-800': user.roles[0]?.name === 'admin',
                      'bg-blue-100 text-blue-800': user.roles[0]?.name === 'team_leader',
                      'bg-green-100 text-green-800': user.roles[0]?.name === 'developer'
                    }">
                    {{ user.roles[0]?.name || 'Sin rol' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.projects?.length || 0 }} proyectos
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-red-100 text-red-800': user.active_tasks_count >= 3,
                      'bg-yellow-100 text-yellow-800': user.active_tasks_count === 2,
                      'bg-green-100 text-green-800': user.active_tasks_count <= 1
                    }">
                    {{ user.active_tasks_count }} activas
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': user.has_unlimited_tasks,
                      'bg-gray-100 text-gray-800': !user.has_unlimited_tasks
                    }">
                    {{ user.has_unlimited_tasks ? 'Sí' : 'No' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      v-if="!user.has_unlimited_tasks && user.roles[0]?.name !== 'admin'"
                      @click="grantPermission(user.id)"
                      :disabled="loading"
                      class="text-green-600 hover:text-green-900 disabled:opacity-50"
                    >
                      Otorgar
                    </button>
                    <button
                      v-if="user.has_unlimited_tasks && user.roles[0]?.name !== 'admin'"
                      @click="revokePermission(user.id)"
                      :disabled="loading"
                      class="text-red-600 hover:text-red-900 disabled:opacity-50"
                    >
                      Revocar
                    </button>
                    <button
                      @click="showUserHistory(user.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Historial
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Permission Grant Modal -->
    <div v-if="showGrantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Otorgar Permiso</h3>
          <form @submit.prevent="confirmGrantPermission">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Razón (opcional)</label>
              <textarea
                v-model="grantForm.reason"
                rows="3"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Explica por qué se otorga este permiso..."
              ></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de expiración (opcional)</label>
              <input
                v-model="grantForm.expires_at"
                type="datetime-local"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showGrantModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50"
              >
                Otorgar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- User History Modal -->
    <div v-if="showHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Historial de Permisos</h3>
            <button @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div v-if="userHistory.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razón</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expira</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="permission in userHistory" :key="permission.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': permission.action === 'granted',
                        'bg-red-100 text-red-800': permission.action === 'revoked'
                      }">
                      {{ permission.action === 'granted' ? 'Otorgado' : 'Revocado' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ permission.reason || 'Sin razón especificada' }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ permission.expires_at ? new Date(permission.expires_at).toLocaleDateString() : 'Sin expiración' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ new Date(permission.created_at).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No hay historial de permisos para este usuario.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

// Props
const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  projects: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Reactive data
const loading = ref(false)
const selectedProject = ref('')
const showGrantModal = ref(false)
const showHistoryModal = ref(false)
const selectedUserId = ref(null)
const userHistory = ref([])

const grantForm = ref({
  reason: '',
  expires_at: ''
})

// Methods
const grantPermission = (userId) => {
  selectedUserId.value = userId
  showGrantModal.value = true
}

const confirmGrantPermission = async () => {
  try {
    loading.value = true
    
    const response = await fetch('/admin/simultaneous-tasks/grant', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        user_id: selectedUserId.value,
        reason: grantForm.value.reason,
        expires_at: grantForm.value.expires_at
      })
    })

    const data = await response.json()

    if (data.success) {
      toast.success(data.message)
      showGrantModal.value = false
      grantForm.value = { reason: '', expires_at: '' }
      // Recargar la página para actualizar los datos
      window.location.reload()
    } else {
      toast.error(data.message)
    }
  } catch (error) {
    console.error('Error:', error)
    toast.error('Error al otorgar el permiso')
  } finally {
    loading.value = false
  }
}

const revokePermission = async (userId) => {
  if (!confirm('¿Estás seguro de que quieres revocar este permiso?')) {
    return
  }

  try {
    loading.value = true
    
    const response = await fetch('/admin/simultaneous-tasks/revoke', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        user_id: userId
      })
    })

    const data = await response.json()

    if (data.success) {
      toast.success(data.message)
      // Recargar la página para actualizar los datos
      window.location.reload()
    } else {
      toast.error(data.message)
    }
  } catch (error) {
    console.error('Error:', error)
    toast.error('Error al revocar el permiso')
  } finally {
    loading.value = false
  }
}

const grantPermissionToTeam = async () => {
  if (!selectedProject.value) {
    toast.error('Por favor selecciona un proyecto')
    return
  }

  if (!confirm('¿Estás seguro de que quieres otorgar permisos a todo el equipo?')) {
    return
  }

  try {
    loading.value = true
    
    const response = await fetch('/admin/simultaneous-tasks/grant-team', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        project_id: selectedProject.value,
        reason: 'Otorgado a todo el equipo',
        expires_at: null
      })
    })

    const data = await response.json()

    if (data.success) {
      toast.success(data.message)
      selectedProject.value = ''
      // Recargar la página para actualizar los datos
      window.location.reload()
    } else {
      toast.error(data.message)
    }
  } catch (error) {
    console.error('Error:', error)
    toast.error('Error al otorgar permisos al equipo')
  } finally {
    loading.value = false
  }
}

const revokePermissionFromTeam = async () => {
  if (!selectedProject.value) {
    toast.error('Por favor selecciona un proyecto')
    return
  }

  if (!confirm('¿Estás seguro de que quieres revocar permisos a todo el equipo?')) {
    return
  }

  try {
    loading.value = true
    
    const response = await fetch('/admin/simultaneous-tasks/revoke-team', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        project_id: selectedProject.value
      })
    })

    const data = await response.json()

    if (data.success) {
      toast.success(data.message)
      selectedProject.value = ''
      // Recargar la página para actualizar los datos
      window.location.reload()
    } else {
      toast.error(data.message)
    }
  } catch (error) {
    console.error('Error:', error)
    toast.error('Error al revocar permisos del equipo')
  } finally {
    loading.value = false
  }
}

const showUserHistory = async (userId) => {
  try {
    const response = await fetch(`/admin/simultaneous-tasks/user/${userId}/history`)
    const data = await response.json()

    if (data.success) {
      userHistory.value = data.permissions
      showHistoryModal.value = true
    } else {
      toast.error('Error al cargar el historial')
    }
  } catch (error) {
    console.error('Error:', error)
    toast.error('Error al cargar el historial')
  }
}
</script> 