<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import MarkdownIt from 'markdown-it';
import TheHeader from '../components/TheHeader.vue';
import BurgerMenu from '../components/BurgerMenu.vue';
import FolderIcon from '../components/FolderIcon.vue';
import FileIcon from '../components/FileIcon.vue';
import pushManager from '../utils/pushManager.js';
import axios from 'axios';

const route = useRoute();
const currentFolder = ref(null);
const folders = ref([]);
const files = ref([]);
const breadcrumbs = ref([]);
const isLoading = ref(true);
const md = new MarkdownIt({ html: true });
const showPushPrompt = ref(false);

// Fetch data
const fetchData = async (folderId = null) => {
    isLoading.value = true;
    try {
        const params = folderId ? { folder_id: folderId } : {};
        const response = await axios.get('/api/files.php', { params });

        // Mock data fallback if API fails (for dev environment without PHP)
        if (response.headers['content-type'] && response.headers['content-type'].includes('text/html')) {
             throw new Error('API returned HTML, likely 404 or error');
        }

        const data = response.data;
        currentFolder.value = data.current_folder;
        folders.value = data.folders;
        files.value = data.files;
        breadcrumbs.value = data.breadcrumbs || [];

    } catch (e) {
        console.warn("Using Mock Data");
        // Mock Data Logic
        if (!folderId) {
            currentFolder.value = { description: "# Willkommen\nDies ist das **Elektron** Download Center." };
            folders.value = [{ id: 1, name: "Technische Daten" }, { id: 2, name: "Marketing Material" }];
            files.value = [];
            breadcrumbs.value = [];
        } else if (folderId == 1) {
            currentFolder.value = { id: 1, name: "Technische Daten", description: "Hier finden Sie alle technischen Datenblätter." };
            folders.value = [];
            files.value = [{ id: 101, name: "Spezifikationen_2024.pdf", url: "#" }];
            breadcrumbs.value = [{ id: null, name: 'Home' }, { id: 1, name: 'Technische Daten' }];
        } else {
             currentFolder.value = { id: 2, name: "Marketing", description: "" };
             folders.value = [];
             files.value = [{ id: 201, name: "Brochure.pdf", url: "#" }];
             breadcrumbs.value = [{ id: null, name: 'Home' }, { id: 2, name: 'Marketing Material' }];
        }
    } finally {
        isLoading.value = false;
    }
};

const handleFolderClick = (id) => {
    fetchData(id);
};

const handleRepairPush = async () => {
    const success = await pushManager.subscribeUser();
    if (success) {
        alert("Push-Benachrichtigungen wurden aktiviert!");
        showPushPrompt.value = false;
    } else {
        alert("Konnte Push-Benachrichtigungen nicht aktivieren. Bitte prüfen Sie Ihre Browser-Einstellungen.");
    }
};

onMounted(async () => {
    fetchData();
    // Check push status
    if ('Notification' in window && Notification.permission === 'default') {
        showPushPrompt.value = true;
    }
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 pb-10">
    <TheHeader>
        <template #actions>
            <BurgerMenu @repair-push="handleRepairPush" />
        </template>
    </TheHeader>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Push Prompt -->
        <div v-if="showPushPrompt" class="mb-6 bg-blue-50 border-l-4 border-primary p-4 rounded shadow-sm flex justify-between items-center">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Bleiben Sie informiert! Aktivieren Sie Push-Benachrichtigungen für Updates.
                    </p>
                </div>
            </div>
            <button @click="handleRepairPush" class="ml-4 px-3 py-1 bg-primary text-white text-sm rounded hover:bg-primary-dark transition">Aktivieren</button>
        </div>

        <!-- Breadcrumbs -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="#" @click.prevent="fetchData(null)" class="text-gray-400 hover:text-gray-500">
                        <svg class="flex-shrink-0 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                    </a>
                </li>
                <li v-for="crumb in breadcrumbs" :key="crumb.id">
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="#" @click.prevent="fetchData(crumb.id)" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">{{ crumb.name }}</a>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Description (Markdown) -->
        <div v-if="currentFolder && currentFolder.description" class="bg-white shadow rounded-lg p-6 mb-8 prose max-w-none text-gray-700">
            <div v-html="md.render(currentFolder.description)"></div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <!-- Folders -->
            <div v-for="folder in folders" :key="'folder-' + folder.id"
                 @click="handleFolderClick(folder.id)"
                 class="group flex flex-col items-center p-4 bg-white rounded-lg shadow hover:shadow-md cursor-pointer transition">
                <FolderIcon />
                <span class="mt-2 text-sm font-medium text-gray-900 text-center truncate w-full">{{ folder.name }}</span>
            </div>

            <!-- Files -->
            <div v-for="file in files" :key="'file-' + file.id"
                 class="group flex flex-col items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition">
                <a :href="file.url" target="_blank" class="flex flex-col items-center w-full">
                    <FileIcon />
                    <span class="mt-2 text-sm font-medium text-gray-900 text-center truncate w-full">{{ file.name }}</span>
                </a>
            </div>
        </div>

        <div v-if="!isLoading && folders.length === 0 && files.length === 0" class="text-center py-12 text-gray-500">
            Dieser Ordner ist leer.
        </div>
    </main>
  </div>
</template>
