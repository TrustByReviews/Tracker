<template>
  <TransitionGroup
    tag="div"
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="transform translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition duration-100 ease-in"
    leave-from-class="transform translate-y-0 opacity-100 sm:translate-x-0"
    leave-to-class="transform translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    class="fixed top-0 right-0 z-50 flex flex-col gap-2 p-4"
  >
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="pointer-events-auto w-full max-w-md overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5"
    >
      <div class="p-4">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <CheckCircle
              v-if="toast.type === 'success'"
              class="h-6 w-6 text-green-400"
              aria-hidden="true"
            />
            <AlertTriangle
              v-else-if="toast.type === 'error'"
              class="h-6 w-6 text-red-400"
              aria-hidden="true"
            />
            <Info
              v-else
              class="h-6 w-6 text-blue-400"
              aria-hidden="true"
            />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 break-words">
              {{ toast.title }}
            </p>
            <p class="mt-1 text-sm text-gray-500 break-words">
              {{ toast.message }}
            </p>
          </div>
          <div class="flex-shrink-0 ml-2">
            <button
              type="button"
              class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              @click="removeToast(toast.id)"
            >
              <span class="sr-only">Close</span>
              <X class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </TransitionGroup>
</template>

<script setup lang="ts">
import { CheckCircle, AlertTriangle, Info, X } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const { toasts, removeToast } = useToast()
</script> 