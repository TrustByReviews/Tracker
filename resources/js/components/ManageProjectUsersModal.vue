<template>
  <Dialog :open="isOpen" @close="$emit('close')">
    <DialogContent class="max-w-4xl max-h-[80vh] overflow-y-auto">
      <DialogTitle class="text-xl font-semibold text-gray-900">
        Manage Project Users
      </DialogTitle>
      
      <div class="space-y-6">
        <!-- Current Users Section -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Current Project Users</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="user in currentUsers" :key="user.id" 
                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
              <div class="flex items-center space-x-3">
                <Avatar class="h-8 w-8">
                  <AvatarImage :src="user.avatar" :alt="user.name" />
                  <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                </Avatar>
                <div>
                  <p class="font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>
                  <div class="flex space-x-1 mt-1">
                    <Badge v-for="role in user.roles" :key="role.id" 
                           :class="getRoleBadgeClass(role.name)" class="text-xs">
                      {{ role.name }}
                    </Badge>
                  </div>
                </div>
              </div>
              <Button 
                variant="outline" 
                size="sm"
                @click="removeUser(user)"
                class="text-red-600 hover:text-red-700"
              >
                <Icon name="x" class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>

        <!-- Add Users Section -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Add Users to Project</h3>
          
          <!-- Role Filter -->
          <div class="mb-4">
            <Label for="role-filter">Filter by Role</Label>
            <Select v-model="selectedRole" @update:model-value="filterUsers">
              <SelectTrigger>
                <SelectValue placeholder="All roles" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All roles</SelectItem>
                <SelectItem value="developer">Developers</SelectItem>
                <SelectItem value="qa">QA Testers</SelectItem>
                <SelectItem value="team_leader">Team Leaders</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Available Users -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto">
            <div v-for="user in availableUsers" :key="user.id" 
                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
              <div class="flex items-center space-x-3">
                <Avatar class="h-8 w-8">
                  <AvatarImage :src="user.avatar" :alt="user.name" />
                  <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                </Avatar>
                <div>
                  <p class="font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>
                  <div class="flex space-x-1 mt-1">
                    <Badge v-for="role in user.roles" :key="role.id" 
                           :class="getRoleBadgeClass(role.name)" class="text-xs">
                      {{ role.name }}
                    </Badge>
                  </div>
                </div>
              </div>
              <Button 
                variant="outline" 
                size="sm"
                @click="addUser(user)"
                class="text-green-600 hover:text-green-700"
              >
                <Icon name="plus" class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter class="mt-6">
        <Button variant="outline" @click="$emit('close')">
          Cancel
        </Button>
        <Button @click="saveChanges" :disabled="isLoading">
          <Icon v-if="isLoading" name="loader-2" class="h-4 w-4 mr-2 animate-spin" />
          Save Changes
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Dialog, DialogContent, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import Icon from '@/components/Icon.vue'
import { router } from '@inertiajs/vue3'

interface User {
  id: string
  name: string
  email: string
  avatar?: string
  roles: Array<{
    id: string
    name: string
  }>
}

interface Props {
  isOpen: boolean
  project: any
  currentUsers: User[]
  allUsers: User[]
}

const props = defineProps<Props>()
const emit = defineEmits(['close'])

const selectedRole = ref('')
const isLoading = ref(false)
const usersToAdd = ref<string[]>([])
const usersToRemove = ref<string[]>([])

// Filter available users (not in project)
const availableUsers = computed(() => {
  let filtered = props.allUsers.filter(user => 
    !props.currentUsers.some(currentUser => currentUser.id === user.id)
  )
  
  if (selectedRole.value) {
    filtered = filtered.filter(user => 
      user.roles.some(role => role.name === selectedRole.value)
    )
  }
  
  return filtered
})

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getRoleBadgeClass = (roleName: string) => {
  switch (roleName) {
    case 'admin':
      return 'bg-red-100 text-red-800'
    case 'team_leader':
      return 'bg-blue-100 text-blue-800'
    case 'developer':
      return 'bg-green-100 text-green-800'
    case 'qa':
      return 'bg-purple-100 text-purple-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const addUser = (user: User) => {
  usersToAdd.value.push(user.id)
  usersToRemove.value = usersToRemove.value.filter(id => id !== user.id)
}

const removeUser = (user: User) => {
  usersToRemove.value.push(user.id)
  usersToAdd.value = usersToAdd.value.filter(id => id !== user.id)
}

const saveChanges = async () => {
  isLoading.value = true
  
  try {
    await router.put(`/projects/${props.project.id}/users`, {
      users_to_add: usersToAdd.value,
      users_to_remove: usersToRemove.value
    })
    
    emit('close')
  } catch (error) {
    console.error('Error updating project users:', error)
  } finally {
    isLoading.value = false
  }
}

const filterUsers = () => {
  // The computed property will automatically filter based on selectedRole
}
</script>
