import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export interface Permission {
  id: string
  name: string
  display_name: string
  description: string
  module: string
  is_active: boolean
}

export interface User {
  id: string
  name: string
  email: string
  roles?: {
    id: string
    name: string
    permissions?: Permission[]
  }[]
  directPermissions?: Permission[]
  rolePermissions?: Permission[]
}

export function usePermissions() {
  const page = usePage()
  
  const user = computed<User | null>(() => {
    return page.props.auth?.user || null
  })

  const hasPermission = (permissionName: string): boolean => {
    if (!user.value) return false
    
    // Check direct permissions
    if (user.value.directPermissions?.some(p => p.name === permissionName)) {
      return true
    }
    
    // Check role permissions (iterate through all roles)
    if (user.value.roles?.some(role => 
      role.permissions?.some(p => p.name === permissionName)
    )) {
      return true
    }
    
    return false
  }

  const hasAnyPermission = (permissionNames: string[]): boolean => {
    return permissionNames.some(permission => hasPermission(permission))
  }

  const hasAllPermissions = (permissionNames: string[]): boolean => {
    return permissionNames.every(permission => hasPermission(permission))
  }

  const canAccessModule = (module: string): boolean => {
    if (!user.value) return false
    
    // Check if user has any permission for this module
    const allPermissions = [
      ...(user.value.directPermissions || []),
      ...(user.value.roles?.flatMap(role => role.permissions || []) || [])
    ]
    
    return allPermissions.some(p => p.module === module && p.is_active)
  }

  const getUserPermissions = (): Permission[] => {
    if (!user.value) return []
    
    const allPermissions = [
      ...(user.value.directPermissions || []),
      ...(user.value.roles?.flatMap(role => role.permissions || []) || [])
    ]
    
    // Remove duplicates based on id
    return allPermissions.filter((permission, index, self) => 
      index === self.findIndex(p => p.id === permission.id)
    )
  }

  return {
    user,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    canAccessModule,
    getUserPermissions
  }
} 