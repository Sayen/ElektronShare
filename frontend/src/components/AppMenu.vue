<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex justify-end">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="$emit('close')"></div>

    <!-- Panel -->
    <div class="relative bg-white w-80 h-full shadow-xl flex flex-col p-6 overflow-y-auto">
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-xl font-bold text-elektron-blue">Menu</h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-800">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <nav class="flex-grow space-y-4">
        <router-link to="/" class="block text-lg hover:text-elektron-blue" @click="$emit('close')">Dateien</router-link>

        <div class="border-t pt-4">
          <p class="text-sm text-gray-500 mb-2">Push Benachrichtigungen</p>
          <div v-if="pushStatus === 'granted'" class="text-green-600 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Aktiviert
          </div>
          <button v-else @click="enablePush" class="w-full bg-elektron-blue text-white py-2 rounded hover:bg-opacity-90">
            Aktivieren
          </button>
          <p v-if="pushMsg" class="text-xs mt-2" :class="pushError ? 'text-red-500' : 'text-green-600'">{{ pushMsg }}</p>
        </div>

        <div class="border-t pt-4 mt-4">
          <router-link to="/admin" class="block text-gray-600 hover:text-elektron-blue" @click="$emit('close')">Admin Bereich</router-link>
        </div>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { subscribeUser, getPermissionState } from '../utils/push';

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
