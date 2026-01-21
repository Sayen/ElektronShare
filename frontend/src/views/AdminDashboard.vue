<template>
  <div>
    <h1 class="text-3xl font-bold mb-6 text-elektron-blue">Admin Dashboard</h1>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
      <!-- Tabs -->
      <div class="flex border-b bg-gray-50">
        <button @click="currentTab = 'files'" :class="{'border-b-2 border-elektron-blue text-elektron-blue bg-white': currentTab === 'files'}" class="flex-1 py-4 px-6 text-center font-medium hover:bg-white transition">Dateimanager</button>
        <button @click="currentTab = 'push'" :class="{'border-b-2 border-elektron-blue text-elektron-blue bg-white': currentTab === 'push'}" class="flex-1 py-4 px-6 text-center font-medium hover:bg-white transition">Push Nachrichten</button>
        <button @click="currentTab = 'settings'" :class="{'border-b-2 border-elektron-blue text-elektron-blue bg-white': currentTab === 'settings'}" class="flex-1 py-4 px-6 text-center font-medium hover:bg-white transition">Einstellungen</button>
      </div>

      <div class="p-6">
        <!-- Files Tab -->
        <div v-if="currentTab === 'files'">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-xl font-bold">Inhalt verwalten</h2>
                <div class="space-x-2">
                    <button @click="showCreateFolder = true" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Neuer Ordner</button>
                </div>
            </div>

             <div class="mb-4 text-sm text-gray-500">
                Aktueller Ordner: <strong>{{ currentFolder?.name || 'Home' }}</strong>
                <button v-if="parentId" @click="loadFolder(parentId)" class="ml-4 text-elektron-blue underline">Nach oben</button>
             </div>

            <div class="border-2 border-dashed border-gray-300 rounded p-6 text-center mb-6" @dragover.prevent @drop.prevent="handleDrop">
                <p class="text-gray-500 mb-2">Dateien hierher ziehen oder auswählen</p>
                <input type="file" ref="fileInput" @change="handleFileSelect" class="hidden">
                <button @click="$refs.fileInput.click()" class="text-elektron-blue underline">Durchsuchen</button>
            </div>

             <div class="mb-6" v-if="currentFolder">
                <label class="block font-bold mb-2">Ordner Beschreibung (Markdown)</label>
                <textarea v-model="currentFolderDescription" class="w-full border p-2 rounded h-32 font-mono"></textarea>
                <button @click="updateFolder" class="mt-2 bg-green-600 text-white px-4 py-2 rounded">Speichern</button>
             </div>

            <ul class="divide-y">
                <li v-for="folder in folders" :key="'f'+folder.id" class="py-3 flex justify-between items-center hover:bg-gray-50 px-2">
                    <div @click="loadFolder(folder.id)" class="cursor-pointer flex items-center">
                         <svg class="w-6 h-6 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        {{ folder.name }}
                    </div>
                    <button @click="deleteItem('folder', folder.id)" class="text-red-500 hover:text-red-700">Löschen</button>
                </li>
                 <li v-for="file in files" :key="'fil'+file.id" class="py-3 flex justify-between items-center hover:bg-gray-50 px-2">
                    <div class="flex items-center">
                         <svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        {{ file.filename }}
                    </div>
                    <button @click="deleteItem('file', file.id)" class="text-red-500 hover:text-red-700">Löschen</button>
                </li>
            </ul>

            <div v-if="showCreateFolder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded shadow w-96">
                    <h3 class="font-bold mb-4">Neuer Ordner</h3>
                    <input v-model="newFolderName" class="w-full border p-2 mb-4" placeholder="Name">
                    <div class="flex justify-end space-x-2">
                        <button @click="showCreateFolder = false" class="text-gray-500">Abbrechen</button>
                        <button @click="createFolder" class="bg-elektron-blue text-white px-4 py-2 rounded">Erstellen</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="currentTab === 'push'">
            <h2 class="text-xl font-bold mb-4">Push Nachricht senden</h2>

            <div class="bg-blue-50 p-4 rounded mb-6 text-sm text-blue-800">
                <span class="font-bold">{{ pushStats.length }}</span> Abonnenten registriert.
            </div>

            <form @submit.prevent="sendPush" class="max-w-lg">
                <div class="mb-4">
                    <label class="block font-bold mb-2">Titel</label>
                    <input v-model="pushForm.title" class="w-full border p-2 rounded" required>
                </div>
                 <div class="mb-4">
                    <label class="block font-bold mb-2">Nachricht</label>
                    <textarea v-model="pushForm.body" class="w-full border p-2 rounded h-24" required></textarea>
                </div>
                 <div class="mb-4">
                    <label class="block font-bold mb-2">Link (Optional)</label>
                    <input v-model="pushForm.url" class="w-full border p-2 rounded" placeholder="/">
                </div>
                <button type="submit" class="bg-elektron-blue text-white px-6 py-2 rounded hover:bg-opacity-90">Senden</button>
            </form>
        </div>

        <div v-if="currentTab === 'settings'">
             <h2 class="text-xl font-bold mb-4">Einstellungen</h2>

             <div class="max-w-sm">
                <h3 class="font-bold mb-2">Admin Passwort ändern</h3>
                <input v-model="newPassword" type="password" class="w-full border p-2 mb-2" placeholder="Neues Passwort">
                <button @click="changePassword" class="bg-gray-800 text-white px-4 py-2 rounded">Ändern</button>
             </div>

             <button @click="logout" class="mt-10 text-red-500 underline">Abmelden</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const currentTab = ref('files');

const currentFolder = ref(null);
const folders = ref([]);
const files = ref([]);
const parentId = ref(null);
const currentFolderDescription = ref('');

const showCreateFolder = ref(false);
const newFolderName = ref('');

const pushStats = ref([]);
const pushForm = ref({ title: '', body: '', url: '/' });

const newPassword = ref('');

async function loadFolder(id) {
    try {
        const url = id ? `/api/files.php?folder_id=${id}` : '/api/files.php';
        const res = await axios.get(url);
        currentFolder.value = res.data.current_folder;
        folders.value = res.data.folders;
        files.value = res.data.files;
        parentId.value = res.data.parent_id;
        currentFolderDescription.value = currentFolder.value?.description || '';
    } catch (e) {
        console.error(e);
    }
}

async function createFolder() {
    if(!newFolderName.value) return;
    try {
        const params = new URLSearchParams();
        params.append('action', 'create_folder');
        params.append('name', newFolderName.value);
        if(currentFolder.value?.id) params.append('parent_id', currentFolder.value.id);

        await axios.post('/api/files.php', params);

        showCreateFolder.value = false;
        newFolderName.value = '';
        loadFolder(currentFolder.value?.id);
    } catch (e) {
        alert(e);
    }
}

async function handleFileSelect(e) {
    const file = e.target.files[0];
    if(file) uploadFile(file);
}

function handleDrop(e) {
    const file = e.dataTransfer.files[0];
    if(file) uploadFile(file);
}

async function uploadFile(file) {
    const formData = new FormData();
    formData.append('action', 'upload_file');
    formData.append('file', file);
    formData.append('folder_id', currentFolder.value.id);

    try {
        await axios.post('/api/files.php', formData);
        loadFolder(currentFolder.value.id);
    } catch (e) {
        alert("Upload failed");
    }
}

async function updateFolder() {
    const params = new URLSearchParams();
    params.append('action', 'update_folder');
    params.append('id', currentFolder.value.id);
    params.append('description', currentFolderDescription.value);

    try {
        await axios.post('/api/files.php', params);
        alert("Gespeichert");
        loadFolder(currentFolder.value.id);
    } catch (e) {
        alert("Fehler");
    }
}

async function deleteItem(type, id) {
    if(!confirm("Wirklich löschen?")) return;
    try {
        await axios.delete('/api/files.php', { data: { type, id } });
        loadFolder(currentFolder.value.id);
    } catch (e) {
        alert("Fehler");
    }
}

async function loadPushStats() {
    try {
        const res = await axios.get('/api/push.php?action=list');
        pushStats.value = res.data.subscriptions || [];
    } catch (e) {}
}

async function sendPush() {
    if(!confirm("Push an alle senden?")) return;
    try {
        await axios.post('/api/push.php', {
            action: 'send',
            ...pushForm.value
        });
        alert("Gesendet!");
        pushForm.value.title = '';
        pushForm.value.body = '';
    } catch (e) {
        alert("Fehler beim Senden");
    }
}

async function changePassword() {
    try {
        await axios.post('/api/auth.php', {
            action: 'change_password',
            new_password: newPassword.value
        });
        alert("Passwort geändert");
        newPassword.value = '';
    } catch (e) {
        alert("Fehler");
    }
}

async function logout() {
     await axios.post('/api/auth.php', { action: 'logout' });
     router.push('/login');
}

onMounted(() => {
    loadFolder(null);
    loadPushStats();
});
</script>
