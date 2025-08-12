<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button
            @click="router.get('/bugs')"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Bugs
          </button>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ bug.title }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Bug Details</p>
          </div>
        </div>
        <div class="flex space-x-3">
          <button
            v-if="permissions === 'admin'"
            @click="showEditModal = true"
            class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Bug
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Status and Action Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Status Card -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status & Priority</h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                  <span
                    :class="{
                      'bg-red-100 text-red-800': bug.status === 'new',
                      'bg-orange-100 text-orange-800': bug.status === 'assigned',
                      'bg-blue-100 text-blue-800': bug.status === 'in progress',
                      'bg-green-100 text-green-800': bug.status === 'resolved',
                      'bg-purple-100 text-purple-800': bug.status === 'verified',
                      'bg-gray-100 text-gray-800': bug.status === 'closed'
                    }"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ getStatusLabel(bug.status) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Importance</span>
                  <span
                    :class="{
                      'bg-red-100 text-red-800': bug.importance === 'critical',
                      'bg-orange-100 text-orange-800': bug.importance === 'high',
                      'bg-yellow-100 text-yellow-800': bug.importance === 'medium',
                      'bg-green-100 text-green-800': bug.importance === 'low'
                    }"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ getImportanceLabel(bug.importance) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Severity</span>
                  <span
                    :class="{
                      'bg-red-100 text-red-800': bug.severity === 'critical',
                      'bg-orange-100 text-orange-800': bug.severity === 'high',
                      'bg-yellow-100 text-yellow-800': bug.severity === 'medium',
                      'bg-green-100 text-green-800': bug.severity === 'low'
                    }"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ getImportanceLabel(bug.severity) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Bug Type</span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ getBugTypeLabel(bug.bug_type) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Reproducibility</span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ getReproducibilityLabel(bug.reproducibility) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Assignment Card -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Assignment</h3>
              <div class="space-y-3">
                <div>
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</span>
                  <p class="text-sm text-gray-900 dark:text-white mt-1">
                    {{ bug.user?.name || 'Unassigned' }}
                  </p>
                </div>
                
                <!-- Assignment Actions -->
                <div class="space-y-2">
                  <!-- Self Assign Button -->
                  <button
                    v-if="!bug.user_id && (permissions === 'developer' || permissions === 'team_leader' || permissions === 'admin')"
                    @click="handleSelfAssign(bug.id)"
                    class="w-full px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Assign to Me
                  </button>
                  
                  <!-- Assign to Others (Admin/Team Leader only) -->
                  <div v-if="(permissions === 'admin' || permissions === 'team_leader') && projectUsers.length > 0">
                    <div class="flex gap-2">
                      <select
                        v-model="selectedUser"
                        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="">Select Developer</option>
                        <option
                          v-for="user in projectUsers"
                          :key="user.id"
                          :value="user.id"
                        >
                          {{ user.name }} ({{ user.email }})
                        </option>
                      </select>
                      <button
                        @click="handleAssignToUser(bug.id)"
                        :disabled="!selectedUser"
                        class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        Assign
                      </button>
                    </div>
                  </div>
                  
                  <!-- Unassign Button -->
                  <button
                    v-if="bug.user_id && (permissions === 'admin' || permissions === 'team_leader' || (permissions === 'developer' && bug.user_id === authUser?.id))"
                    @click="handleUnassign(bug.id)"
                    class="w-full px-3 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                  >
                    Unassign
                  </button>
                </div>
                
                <div>
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned By</span>
                  <p class="text-sm text-gray-900 dark:text-white mt-1">
                    {{ bug.assigned_by_user?.name || 'N/A' }}
                  </p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Date</span>
                  <p class="text-sm text-gray-900 dark:text-white mt-1">
                    {{ bug.assigned_at ? formatDate(bug.assigned_at) : 'N/A' }}
                  </p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Project</span>
                  <p class="text-sm text-gray-900 dark:text-white mt-1">
                    {{ bug.project?.name || 'N/A' }}
                  </p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Sprint</span>
                  <p class="text-sm text-gray-900 dark:text-white mt-1">
                    {{ bug.sprint?.name || 'N/A' }}
                  </p>
                </div>

              </div>
            </div>
          </div>

          <!-- Time Tracking Card -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Time Tracking</h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimated</span>
                  <span class="text-sm text-gray-900 dark:text-white">
                    {{ bug.estimated_hours }}h {{ bug.estimated_minutes }}m
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Time</span>
                  <span class="text-sm text-gray-900 dark:text-white">
                    {{ formatTime(bug.total_time_seconds) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                  <span
                    :class="{
                      'text-green-600': bug.is_working,
                      'text-gray-600': !bug.is_working
                    }"
                    class="text-sm font-medium"
                  >
                    {{ bug.is_working ? 'Working' : 'Not Working' }}
                  </span>
                </div>
                <div v-if="bug.work_started_at" class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Started At</span>
                  <span class="text-sm text-gray-900 dark:text-white">
                    {{ formatDateTime(bug.work_started_at) }}
                  </span>
                </div>
                <div v-if="bug.work_paused_at" class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Paused At</span>
                  <span class="text-sm text-gray-900 dark:text-white">
                    {{ formatDateTime(bug.work_paused_at) }}
                  </span>
                </div>
                <div v-if="bug.work_finished_at" class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Finished At</span>
                  <span class="text-sm text-gray-900 dark:text-white">
                    {{ formatDateTime(bug.work_finished_at) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Description</h3>
                <div class="prose max-w-none">
                  <p class="text-gray-700 dark:text-gray-300">{{ bug.description }}</p>
                </div>
              </div>
            </div>

            <!-- Long Description -->
            <div v-if="bug.long_description" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Detailed Description</h3>
                <div class="prose max-w-none">
                  <p class="text-gray-700 dark:text-gray-300">{{ bug.long_description }}</p>
                </div>
              </div>
            </div>

            <!-- Steps to Reproduce -->
            <div v-if="bug.steps_to_reproduce" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Steps to Reproduce</h3>
                <div class="prose max-w-none">
                  <p class="text-gray-700 dark:text-gray-300">{{ bug.steps_to_reproduce }}</p>
                </div>
              </div>
            </div>

            <!-- Expected vs Actual Behavior -->
            <div v-if="bug.expected_behavior || bug.actual_behavior" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Behavior</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-if="bug.expected_behavior">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Expected Behavior</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ bug.expected_behavior }}</p>
                  </div>
                  <div v-if="bug.actual_behavior">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Actual Behavior</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ bug.actual_behavior }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Environment Information -->
            <div v-if="bug.environment || bug.browser_info || bug.os_info" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Environment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div v-if="bug.environment">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Environment</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ bug.environment }}</p>
                  </div>
                  <div v-if="bug.browser_info">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Browser</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ bug.browser_info }}</p>
                  </div>
                  <div v-if="bug.os_info">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Operating System</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ bug.os_info }}</p>
                  </div>
                </div>
              </div>
            </div>



            <!-- Tags -->
            <div v-if="bug.tags" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tags</h3>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in bug.tags.split(',')"
                    :key="tag"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ tag.trim() }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Attachments -->
            <div v-if="bug.attachments && bug.attachments.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Attachments</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    v-for="(attachment, index) in bug.attachments"
                    :key="index"
                    class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                    @click="openAttachment(attachment)"
                  >
                    <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ getFileName(attachment) }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Action Buttons -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Actions</h3>
                <div class="space-y-3">
                  <!-- Self Assign Button -->
                  <button
                    v-if="!bug.user_id && bug.status === 'new'"
                    @click="handleSelfAssign(bug.id)"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Self Assign
                  </button>

                  <!-- Start Work Button -->
                  <button
                    v-if="bug.user_id && (bug.status === 'assigned' || (bug.status === 'in progress' && !bug.is_working && bug.total_time_seconds === 0)) && !bug.is_working"
                    @click="handleStartWork(bug.id)"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Start Work
                  </button>

                  <!-- Pause Work Button -->
                  <button
                    v-if="bug.is_working"
                    @click="handlePauseWork(bug.id)"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pause Work
                  </button>

                  <!-- Resume Work Button -->
                  <button
                    v-if="bug.status === 'in progress' && !bug.is_working && bug.total_time_seconds > 0"
                    @click="handleResumeWork(bug.id)"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Resume Work
                  </button>

                  <!-- Finish Work Button -->
                  <button
                    v-if="bug.status === 'in progress' && bug.is_working"
                    @click="handleFinishWork(bug.id)"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Finish Work
                  </button>

                  <!-- Status Change Buttons -->
                  <div v-if="permissions === 'admin'" class="space-y-2 pt-4 border-t border-gray-200">
                    <button
                      v-if="bug.status === 'resolved'"
                      @click="handleVerifyBug(bug.id)"
                      class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    >
                      Mark as Verified
                    </button>
                    <button
                      v-if="bug.status === 'verified'"
                      @click="handleCloseBug(bug.id)"
                      class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                      Close Bug
                    </button>
                    <button
                      v-if="['resolved', 'verified', 'closed'].includes(bug.status)"
                      @click="handleReopenBug(bug.id)"
                      class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    >
                      Reopen Bug
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Related Task -->
            <div v-if="bug.related_task" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Related Task</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                      <h4 class="text-lg font-semibold text-blue-900 mb-2">{{ bug.related_task.name }}</h4>
                      <p class="text-sm text-blue-700 mb-3">{{ bug.related_task.description }}</p>
                    </div>
                    <button
                      @click="router.get(`/tasks/${bug.related_task.id}`)"
                      class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors"
                    >
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                      </svg>
                      View Task
                    </button>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                      <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Status</span>
                      <p class="text-sm font-medium text-blue-900 mt-1">
                        <span
                          :class="{
                            'bg-green-100 text-green-800': bug.related_task.status === 'done',
                            'bg-blue-100 text-blue-800': bug.related_task.status === 'in progress',
                            'bg-yellow-100 text-yellow-800': bug.related_task.status === 'to do',
                            'bg-red-100 text-red-800': bug.related_task.status === 'rejected'
                          }"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        >
                          {{ getTaskStatusLabel(bug.related_task.status) }}
                        </span>
                      </p>
                    </div>
                    <div>
                      <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Priority</span>
                      <p class="text-sm font-medium text-blue-900 mt-1">
                        <span
                          :class="{
                            'bg-green-100 text-green-800': bug.related_task.priority === 'low',
                            'bg-yellow-100 text-yellow-800': bug.related_task.priority === 'medium',
                            'bg-orange-100 text-orange-800': bug.related_task.priority === 'high',
                            'bg-red-100 text-red-800': bug.related_task.priority === 'critical'
                          }"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        >
                          {{ getTaskPriorityLabel(bug.related_task.priority) }}
                        </span>
                      </p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                      <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Sprint</span>
                      <p class="text-sm text-blue-900 mt-1">{{ bug.related_task.sprint?.name || 'No sprint' }}</p>
                    </div>
                    <div>
                      <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Category</span>
                      <p class="text-sm text-blue-900 mt-1">{{ getTaskCategoryLabel(bug.related_task.category) }}</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                      <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Story Points</span>
                      <p class="text-sm text-blue-900 mt-1">{{ bug.related_task.story_points }}</p>
                    </div>
                    <div>
                      <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Estimated Time</span>
                      <p class="text-sm text-blue-900 mt-1">{{ bug.related_task.estimated_hours }}h {{ bug.related_task.estimated_minutes || 0 }}m</p>
                    </div>
                  </div>
                  
                  <div v-if="bug.related_task.user">
                    <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Assigned To</span>
                    <p class="text-sm text-blue-900 mt-1">{{ bug.related_task.user.name }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Time Logs -->
            <div v-if="bug.time_logs && bug.time_logs.length > 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Time Logs</h3>
                <div class="space-y-3">
                  <div
                    v-for="log in bug.time_logs"
                    :key="log.id"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                  >
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ log.action }}</p>
                      <p class="text-xs text-gray-500">{{ formatDateTime(log.created_at) }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm text-gray-900">{{ formatTime(log.duration_seconds) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  bug: {
    type: Object,
    required: true
  },
  permissions: {
    type: String,
    required: true
  },
  projectUsers: {
    type: Array,
    default: () => []
  }
})

const showEditModal = ref(false)
const selectedUser = ref('')
const authUser = ref(null)

// Get current user from Inertia
authUser.value = usePage().props.auth?.user

// Helper functions
const getStatusLabel = (status) => {
  const labels = {
    'new': 'New',
    'assigned': 'Assigned',
    'in progress': 'In Progress',
    'resolved': 'Resolved',
    'verified': 'Verified',
    'closed': 'Closed',
    'reopened': 'Reopened'
  }
  return labels[status] || status
}

const getImportanceLabel = (importance) => {
  const labels = {
    'low': 'Low',
    'medium': 'Medium',
    'high': 'High',
    'critical': 'Critical'
  }
  return labels[importance] || importance
}

const getBugTypeLabel = (bugType) => {
  const labels = {
    'frontend': 'Frontend',
    'backend': 'Backend',
    'database': 'Database',
    'api': 'API',
    'ui_ux': 'UI/UX',
    'performance': 'Performance',
    'security': 'Security',
    'other': 'Other'
  }
  return labels[bugType] || bugType
}

const getReproducibilityLabel = (reproducibility) => {
  const labels = {
    'always': 'Always',
    'sometimes': 'Sometimes',
    'rarely': 'Rarely',
    'unable': 'Unable to Reproduce'
  }
  return labels[reproducibility] || reproducibility
}

const getTaskStatusLabel = (status) => {
  const labels = {
    'to do': 'To Do',
    'in progress': 'In Progress',
    'done': 'Done',
    'rejected': 'Rejected'
  }
  return labels[status] || status
}

const getTaskPriorityLabel = (priority) => {
  const labels = {
    'low': 'Low',
    'medium': 'Medium',
    'high': 'High',
    'critical': 'Critical'
  }
  return labels[priority] || priority
}

const getTaskCategoryLabel = (category) => {
  const labels = {
    'frontend': 'Frontend',
    'backend': 'Backend',
    'full stack': 'Full Stack',
    'design': 'Design',
    'deployment': 'Deployment',
    'fixes': 'Fixes',
    'testing': 'Testing',
    'documentation': 'Documentation',
    'database': 'Database',
    'api': 'API',
    'security': 'Security',
    'performance': 'Performance'
  }
  return labels[category] || category
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const formatTime = (seconds) => {
  if (!seconds) return '0h 0m 0s'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hours}h ${minutes}m ${secs}s`
}

const getFileName = (attachment) => {
  if (typeof attachment === 'string') {
    return attachment.split('/').pop()
  }
  return attachment.name || 'File'
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const openAttachment = (attachment) => {
  if (typeof attachment === 'string') {
    window.open(attachment, '_blank')
  } else if (attachment.path) {
    window.open(`/storage/${attachment.path}`, '_blank')
  }
}

// Action handlers
const handleSelfAssign = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/self-assign`)
    router.reload()
  } catch (error) {
    console.error('Error self-assigning bug:', error)
  }
}

const handleAssignToUser = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/assign`, { user_id: selectedUser.value })
    selectedUser.value = ''
    router.reload()
  } catch (error) {
    console.error('Error assigning bug to user:', error)
  }
}

const handleUnassign = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/unassign`)
    router.reload()
  } catch (error) {
    console.error('Error unassigning bug:', error)
  }
}

const handleStartWork = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/start-work`)
    router.reload()
  } catch (error) {
    console.error('Error starting work:', error)
  }
}

const handlePauseWork = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/pause-work`)
    router.reload()
  } catch (error) {
    console.error('Error pausing work:', error)
  }
}

const handleResumeWork = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/resume-work`)
    router.reload()
  } catch (error) {
    console.error('Error resuming work:', error)
  }
}

const handleFinishWork = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/finish-work`)
    router.reload()
  } catch (error) {
    console.error('Error finishing work:', error)
  }
}

const handleVerifyBug = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/verify`)
    router.reload()
  } catch (error) {
    console.error('Error verifying bug:', error)
  }
}

const handleCloseBug = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/close`)
    router.reload()
  } catch (error) {
    console.error('Error closing bug:', error)
  }
}

const handleReopenBug = async (bugId) => {
  try {
    await router.post(`/bugs/${bugId}/reopen`)
    router.reload()
  } catch (error) {
    console.error('Error reopening bug:', error)
  }
}
</script> 