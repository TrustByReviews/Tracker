<template>
  <div v-if="showDebug" class="fixed bottom-4 right-4 bg-black text-white p-4 rounded-lg max-w-md max-h-96 overflow-auto z-50">
    <div class="flex justify-between items-center mb-2">
      <h3 class="font-bold text-sm">Debug Panel</h3>
      <button @click="showDebug = false" class="text-white hover:text-gray-300">×</button>
    </div>
    <div class="text-xs space-y-2">
      <div>
        <strong>Props:</strong>
        <pre class="mt-1 text-xs overflow-auto">{{ JSON.stringify(props, null, 2) }}</pre>
      </div>
    </div>
  </div>
  
  <!-- Toggle button -->
  <button 
    v-if="!showDebug"
    @click="showDebug = true"
    class="fixed bottom-4 right-4 bg-blue-500 text-white p-2 rounded-full z-50 hover:bg-blue-600"
    title="Show Debug Panel"
  >
    🐛
  </button>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  data: any;
}>();

const showDebug = ref(false);
</script>
