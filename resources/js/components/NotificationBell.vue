<template>
  <div class="relative">
    <!-- Campana de notificaciones -->
    <button
      @click="toggleNotifications"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg"
    >
      <Bell class="w-6 h-6" />
      
      <!-- Contador de notificaciones -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Popup de notificaciones -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-y-auto"
    >
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
          <button
            @click="markAllAsRead"
            class="text-sm text-blue-600 hover:text-blue-800"
          >
            Marcar todas como leídas
          </button>
        </div>
      </div>

      <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500">
        No hay notificaciones
      </div>

      <div v-else>
        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
          :class="{ 'bg-blue-50': !notification.read_at }"
        >
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
              <div
                class="w-2 h-2 rounded-full"
                :class="{
                  'bg-blue-500': !notification.read_at,
                  'bg-gray-300': notification.read_at
                }"
              ></div>
            </div>
            
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">
                {{ notification.title }}
              </p>
              <p class="text-sm text-gray-600 mt-1">
                {{ notification.message }}
              </p>
              
              <!-- Información adicional -->
              <div class="mt-2 text-xs text-gray-500">
                <div class="flex items-center space-x-2">
                  <span>{{ formatTime(notification.created_at) }}</span>
                  <span v-if="notification.data?.developer_name">
                    • {{ notification.data.developer_name }}
                  </span>
                </div>
                
                <div v-if="notification.data?.project_name" class="mt-1">
                  Project: {{ notification.data.project_name }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="p-4 border-t border-gray-200">
        <Link
          :href="route('team-leader.notifications')"
          class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
          Ver todas las notificaciones
        </Link>
      </div>
    </div>

    <!-- Overlay para cerrar al hacer click fuera -->
    <div
      v-if="isOpen"
      @click="closeNotifications"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Bell } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

interface Notification {
  id: string
  title: string
  message: string
  type: string
  data: any
  read_at: string | null
  created_at: string
}

const isOpen = ref(false)
const notifications = ref<Notification[]>([])
const unreadCount = ref(0)

// Obtener notificaciones
const fetchNotifications = async () => {
  try {
    const response = await fetch(route('api.team-leader.notifications'))
    const data = await response.json()
    notifications.value = data.notifications || []
    unreadCount.value = data.unread_count || 0
  } catch (error) {
    console.error('Error fetching notifications:', error)
  }
}

// Marcar como leída
const markAsRead = async (notificationId: string) => {
  try {
    await fetch(route('team-leader.notifications.read', notificationId), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    // Update estado local
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

// Marcar todas como leídas
const markAllAsRead = async () => {
  try {
    await fetch(route('team-leader.notifications.read-all'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    // Update estado local
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
    unreadCount.value = 0
  } catch (error) {
    console.error('Error marking all notifications as read:', error)
  }
}

// Manejar click en notificación
const handleNotificationClick = async (notification: Notification) => {
  // Marcar como leída
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }
  
  // Navegar a la vista unificada
  router.visit(route('team-leader.review.tasks'))
  
  closeNotifications()
}

// Formatear tiempo
const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Ahora'
  if (diffInMinutes < 60) return `Hace ${diffInMinutes} min`
  if (diffInMinutes < 1440) return `Hace ${Math.floor(diffInMinutes / 60)}h`
  return date.toLocaleDateString()
}

// Toggle popup
const toggleNotifications = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchNotifications()
  }
}

// Close popup
const closeNotifications = () => {
  isOpen.value = false
}

// Close con ESC
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeNotifications()
  }
}

// Polling para nuevas notificaciones
let pollingInterval: number | null = null

onMounted(() => {
  fetchNotifications()
  
  // Polling cada 30 segundos
  pollingInterval = window.setInterval(() => {
    fetchNotifications()
  }, 30000)
  
  // Event listener para ESC
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
  document.removeEventListener('keydown', handleKeydown)
})
</script> 