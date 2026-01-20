<template>
  <div class="min-h-screen flex flex-col">
    <AppHeader @toggle-menu="menuOpen = !menuOpen" />

    <AppMenu :isOpen="menuOpen" @close="menuOpen = false" />

    <main class="flex-grow container mx-auto px-4 py-6">
      <router-view></router-view>
    </main>

    <footer class="bg-gray-200 text-center py-4 text-sm text-gray-600">
      &copy; {{ new Date().getFullYear() }} Elektron AG
    </footer>

    <!-- Push Prompt Modal -->
    <div v-if="showPushPrompt" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
       <div class="fixed inset-0 bg-black bg-opacity-50" @click="showPushPrompt = false"></div>
       <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm relative z-10">
          <h3 class="text-lg font-bold text-elektron-blue mb-2">Benachrichtigungen aktivieren?</h3>
          <p class="text-gray-600 mb-4">Erhalten Sie Updates zu neuen Dateien und wichtigen Meldungen.</p>
          <div class="flex space-x-3">
             <button @click="dismissPush" class="flex-1 border border-gray-300 py-2 rounded text-gray-700 hover:bg-gray-50">Nein, danke</button>
             <button @click="acceptPush" class="flex-1 bg-elektron-blue text-white py-2 rounded hover:bg-opacity-90">Aktivieren</button>
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppHeader from './components/AppHeader.vue';
import AppMenu from './components/AppMenu.vue';
import { shouldAskForPush, subscribeUser } from './utils/push';

const menuOpen = ref(false);
const showPushPrompt = ref(false);

async function checkPush() {
  if (await shouldAskForPush()) {
    showPushPrompt.value = true;
  }
}

function dismissPush() {
  showPushPrompt.value = false;
  localStorage.setItem('push_prompt_dismissed', 'true');
}

async function acceptPush() {
  try {
    await subscribeUser();
    showPushPrompt.value = false;
  } catch (e) {
    console.error(e);
    alert("Fehler beim Aktivieren: " + e.message);
  }
}

onMounted(() => {
  checkPush();
});
</script>
