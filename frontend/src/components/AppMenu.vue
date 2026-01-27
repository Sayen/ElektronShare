<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex justify-end">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="$emit('close')"></div>

    <!-- Panel -->
    <div class="relative bg-white dark:bg-gray-800 w-80 h-full shadow-xl flex flex-col p-6 overflow-y-auto transition-colors">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-bold text-elektron-blue">Menu</h2>
        <button @click="$emit('close')" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <nav class="flex-grow space-y-4">
        <router-link to="/" class="block text-lg hover:text-elektron-blue dark:text-gray-200" @click="$emit('close')">Dateien</router-link>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Push Benachrichtigungen</p>
          <div v-if="pushStatus === 'granted'" class="text-green-600 dark:text-green-400 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Aktiviert
          </div>
          <button v-else @click="enablePush" class="w-full bg-elektron-blue text-white py-2 rounded hover:bg-opacity-90">
            Aktivieren
          </button>
          <p v-if="pushMsg" class="text-xs mt-2" :class="pushError ? 'text-red-500' : 'text-green-600 dark:text-green-400'">{{ pushMsg }}</p>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4 flex justify-between items-center">
          <span class="text-gray-600 dark:text-gray-300">Dark Mode</span>
          <button @click="globalState.toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg v-if="globalState.darkMode" class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg v-else class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
          </button>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
          <router-link to="/admin" class="block text-gray-600 dark:text-gray-300 hover:text-elektron-blue" @click="$emit('close')">Admin Bereich</router-link>
        </div>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { subscribeUser, getPermissionState } from '../utils/push';
import { globalState } from '../state';

defineProps({
  isOpen: Boolean
});
defineEmits(['close']);

const pushStatus = ref('default');
const pushMsg = ref('');
const pushError = ref(false);

async function checkStatus() {
  pushStatus.value = await getPermissionState();
}

async function enablePush() {
  pushMsg.value = "Registriere...";
  pushError.value = false;
  try {
    await subscribeUser();
    pushStatus.value = 'granted';
    pushMsg.value = "Erfolgreich registriert!";
  } catch (e) {
    console.error(e);
    pushError.value = true;
    pushMsg.value = "Fehler: " + e.message;
  }
}

onMounted(() => {
  checkStatus();
});
</script>
