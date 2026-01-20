<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({ subscribers: 0 });
const message = ref({ title: '', body: '', url: '/' });
const isSending = ref(false);
const statusMsg = ref('');

const fetchStats = async () => {
    try {
        const response = await axios.get('/api/push.php?action=stats');
        stats.value = response.data;
    } catch (e) {
        stats.value = { subscribers: 42 }; // Mock
    }
};

const sendBroadcast = async () => {
    if(!message.value.title || !message.value.body) return;
    isSending.value = true;
    statusMsg.value = '';

    try {
        const response = await axios.post('/api/push.php?action=broadcast', message.value);
        if(response.data.success) {
            statusMsg.value = `Nachricht erfolgreich an ${response.data.sent_count} Abonnenten gesendet.`;
            message.value = { title: '', body: '', url: '/' };
        } else {
            statusMsg.value = 'Fehler beim Senden: ' + response.data.error;
        }
    } catch (e) {
        statusMsg.value = 'Netzwerkfehler.';
    } finally {
        isSending.value = false;
    }
};

onMounted(fetchStats);
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Stats -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistik</h3>
        <div class="flex items-baseline">
            <p class="text-4xl font-extrabold text-primary">{{ stats.subscribers }}</p>
            <p class="ml-2 text-sm text-gray-500">Aktive Abonnenten</p>
        </div>
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900">Abonnenten-Details</h4>
            <ul class="mt-2 text-sm text-gray-500 list-disc list-inside">
                 <!-- Mock or Real list could go here, for now just simple stat -->
                 <li>Chrome User</li>
                 <li>Mobile User</li>
            </ul>
        </div>
    </div>

    <!-- Send Form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Push-Nachricht senden</h3>
        <form @submit.prevent="sendBroadcast" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Titel</label>
                <input v-model="message.title" required type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-primary focus:border-primary" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nachricht</label>
                <textarea v-model="message.body" required rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-primary focus:border-primary"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Ziel-URL (Optional)</label>
                <input v-model="message.url" type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-primary focus:border-primary" />
            </div>

            <div v-if="statusMsg" class="text-sm p-2 rounded" :class="statusMsg.includes('Fehler') ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'">
                {{ statusMsg }}
            </div>

            <button type="submit" :disabled="isSending" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-primary-dark transition disabled:opacity-50">
                {{ isSending ? 'Sende...' : 'An alle senden' }}
            </button>
        </form>
    </div>
  </div>
</template>
