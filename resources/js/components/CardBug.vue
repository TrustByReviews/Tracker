<template>
  <div class="w-full max-w-xs flex flex-col justify-between p-5 rounded-xl border-l-4 shadow transition hover:shadow-md"
       :class="getBorderColor() + ' bg-gradient-to-br from-white to-gray-50'">
    
    <!-- Header con estado y prioridad -->
    <div class="flex items-start justify-between mb-3">
      <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 break-words">
        <Bug :class="getStatusColor()" class="h-5 w-5" />
        {{ bug.title.length > 20
          ? bug.title.slice(0, 20) + '...'
          : bug.title }}
      </h2>
      
      <!-- Indicador de prioridad -->
      <div class="flex items-center gap-1" :class="getPriorityColor()">
        <AlertTriangle class="h-4 w-4" />
        <span class="text-xs font-medium">{{ bug.priority }}</span>
      </div>
    </div>

    <!-- Description -->
    <p class="text-sm text-gray-600 mt-2 italic break-words">
      {{ bug.description.length > 40 
        ? bug.description.slice(0, 40) + '...'
        : bug.description }}
    </p>

    <!-- Asignación -->
    <div class="mt-3 flex items-center gap-2">
      <div class="flex-shrink-0">
        <Avatar class="h-6 w-6">
          <AvatarImage :src="bug.user?.avatar" :alt="bug.user?.name" />
          <AvatarFallback>{{ getInitials(bug.user?.name) }}</AvatarFallback>
        </Avatar>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs text-gray-600 truncate">
          {{ bug.user?.name || 'Unassigned' }}
        </p>
      </div>
    </div>

    <!-- Estado y Progreso -->
    <div class="mt-3">
      <div class="flex justify-between text-xs text-gray-600 mb-1">
        <span>Status</span>
        <Badge :class="getStatusBadgeColor()">
          {{ formatStatus(bug.status) }}
        </Badge>
      </div>
    </div>

    <!-- Dates y acciones -->
    <div class="mt-3">
      <div class="text-xs text-gray-600 flex items-center gap-2">
        <Calendar class="h-4 w-4 text-gray-400" />
        {{ formatDate(bug.created_at) }}
      </div>
      <div class="flex justify-end mt-2">
        <Button 
          variant="outline" 
          size="sm" 
          @click="showDetails"
          class="text-xs"
        >
          View Details
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Bug, AlertTriangle, Calendar } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';

interface User {
  id: string;
  name: string;
  avatar?: string;
}

interface Bug {
  id: string;
  title: string;
  description: string;
  status: string;
  priority: string;
  created_at: string;
  user?: User;
}

const props = defineProps<{
  bug: Bug;
  permissions: string;
  project_id: string;
  sprint_id: string;
}>();

const getInitials = (name?: string) => {
  if (!name) return 'NA';
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const formatStatus = (status: string) => {
  return status.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ');
};

const getStatusColor = () => {
  switch (props.bug.status) {
    case 'resolved':
    case 'verified':
    case 'closed':
      return 'text-green-600';
    case 'in_progress':
      return 'text-blue-600';
    case 'rejected':
      return 'text-red-600';
    default:
      return 'text-gray-600';
  }
};

const getBorderColor = () => {
  switch (props.bug.status) {
    case 'resolved':
    case 'verified':
    case 'closed':
      return 'border-green-500';
    case 'in_progress':
      return 'border-blue-500';
    case 'rejected':
      return 'border-red-500';
    default:
      return 'border-gray-500';
  }
};

const getStatusBadgeColor = () => {
  switch (props.bug.status) {
    case 'resolved':
    case 'verified':
    case 'closed':
      return 'bg-green-100 text-green-800';
    case 'in_progress':
      return 'bg-blue-100 text-blue-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const getPriorityColor = () => {
  switch (props.bug.priority) {
    case 'critical':
      return 'text-red-600';
    case 'high':
      return 'text-orange-600';
    case 'medium':
      return 'text-yellow-600';
    default:
      return 'text-gray-600';
  }
};

const showDetails = () => {
  window.location.href = `/bugs/${props.bug.id}`;
};
</script>
