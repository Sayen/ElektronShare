<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import FolderIcon from '../../components/FolderIcon.vue';
import FileIcon from '../../components/FileIcon.vue';
import WysiwygEditor from '../../components/WysiwygEditor.vue';

const currentFolder = ref(null);
const folders = ref([]);
const files = ref([]);
const breadcrumbs = ref([]);
const isLoading = ref(true);
const isUploading = ref(false);
const showCreateFolderModal = ref(false);
const newFolderName = ref('');
const showEditInfoModal = ref(false);
const folderDescription = ref('');

const fetchData = async (folderId = null) => {
    isLoading.value = true;
    try {
        const params = folderId ? { folder_id: folderId } : {};
        const response = await axios.get('/api/files.php', { params });
        const data = response.data;
        currentFolder.value = data.current_folder;
        folderDescription.value = data.current_folder?.description || '';
        folders.value = data.folders;
        files.value = data.files;
        breadcrumbs.value = data.breadcrumbs || [];
    } catch (e) {
        console.warn("Using Mock Data (Admin)");
        // Mock Data
         if (!folderId) {
            currentFolder.value = { id: null, description: "# Admin Root" };
            folders.value = [{ id: 1, name: "Technische Daten" }, { id: 2, name: "Marketing Material" }];
            files.value = [];
            breadcrumbs.value = [];
        } else {
            currentFolder.value = { id: folderId, name: "Subfolder", description: "" };
            folders.value = [];
            files.value = [];
            breadcrumbs.value = [{id: null, name: "Root"}, {id: folderId, name: "Subfolder"}];
        }
    } finally {
        isLoading.value = false;
    }
};

const handleFolderClick = (id) => {
    fetchData(id);
};

const createFolder = async () => {
    if(!newFolderName.value) return;
    try {
        await axios.post('/api/files.php?action=create_folder', {
            parent_id: currentFolder.value?.id,
            name: newFolderName.value
        });
        showCreateFolderModal.value = false;
        newFolderName.value = '';
        fetchData(currentFolder.value?.id);
    } catch (e) {
        alert("Fehler beim Erstellen des Ordners");
    }
};

const deleteFolder = async (id) => {
    if(!confirm("Ordner wirklich löschen?")) return;
    try {
        await axios.post('/api/files.php?action=delete_folder', { id });
        fetchData(currentFolder.value?.id);
    } catch (e) {
        alert("Fehler beim Löschen");
    }
};

const deleteFile = async (id) => {
    if(!confirm("Datei wirklich löschen?")) return;
    try {
        await axios.post('/api/files.php?action=delete_file', { id });
        fetchData(currentFolder.value?.id);
    } catch (e) {
        alert("Fehler beim Löschen");
    }
};

const updateDescription = async () => {
    try {
        await axios.post('/api/files.php?action=update_description', {
            folder_id: currentFolder.value?.id,
            description: folderDescription.value
        });
        showEditInfoModal.value = false;
        fetchData(currentFolder.value?.id); // Refresh
    } catch (e) {
        alert("Fehler beim Speichern");
    }
}

// Drag & Drop Upload
const dragActive = ref(false);
const handleDrop = async (e) => {
    e.preventDefault();
    dragActive.value = false;
    const droppedFiles = e.dataTransfer.files;
    if(droppedFiles.length) uploadFiles(droppedFiles);
};

const uploadFiles = async (fileList) => {
    isUploading.value = true;
    const formData = new FormData();
    for(let i=0; i < fileList.length; i++){
        formData.append('files[]', fileList[i]);
    }
    if(currentFolder.value?.id) formData.append('folder_id', currentFolder.value.id);

    try {
        await axios.post('/api/files.php?action=upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        fetchData(currentFolder.value?.id);
    } catch (e) {
        alert("Upload fehlgeschlagen");
    } finally {
        isUploading.value = false;
    }
};

onMounted(() => fetchData());
</script>

<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="#" @click.prevent="fetchData(null)" class="text-gray-400 hover:text-gray-500">Root</a></li>
                <li v-for="crumb in breadcrumbs" :key="crumb.id">
                    <span class="text-gray-300">/</span>
                    <a href="#" @click.prevent="fetchData(crumb.id)" class="ml-2 text-gray-500 hover:text-gray-700">{{ crumb.name }}</a>
                </li>
            </ol>
        </nav>
        <div class="space-x-2">
            <button @click="showEditInfoModal = true" class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Info bearbeiten</button>
            <button @click="showCreateFolderModal = true" class="px-3 py-1 bg-primary text-white rounded hover:bg-primary-dark">Ordner erstellen</button>
        </div>
    </div>

    <!-- Drag Drop Zone -->
    <div
        @dragenter.prevent="dragActive = true"
        @dragleave.prevent="dragActive = false"
        @dragover.prevent
        @drop="handleDrop"
        :class="{'bg-blue-50 border-primary': dragActive, 'border-gray-300': !dragActive}"
        class="border-2 border-dashed rounded-lg p-10 flex flex-col items-center justify-center text-gray-500 mb-6 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <span v-if="isUploading">Upload läuft...</span>
        <span v-else>Dateien hierher ziehen zum Upload</span>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <div v-for="folder in folders" :key="'folder-' + folder.id" class="group relative flex flex-col items-center p-4 bg-gray-50 rounded-lg border border-transparent hover:border-gray-200">
            <div @click="handleFolderClick(folder.id)" class="cursor-pointer flex flex-col items-center w-full">
                <FolderIcon />
                <span class="mt-2 text-sm font-medium text-gray-900 text-center truncate w-full">{{ folder.name }}</span>
            </div>
            <button @click="deleteFolder(folder.id)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div v-for="file in files" :key="'file-' + file.id" class="group relative flex flex-col items-center p-4 bg-gray-50 rounded-lg border border-transparent hover:border-gray-200">
             <a :href="file.url" target="_blank" class="flex flex-col items-center w-full">
                <FileIcon />
                <span class="mt-2 text-sm font-medium text-gray-900 text-center truncate w-full">{{ file.name }}</span>
            </a>
            <button @click="deleteFile(file.id)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div v-if="showCreateFolderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h3 class="text-lg font-medium mb-4">Neuen Ordner erstellen</h3>
            <input v-model="newFolderName" class="w-full border p-2 rounded mb-4" placeholder="Ordnername" />
            <div class="flex justify-end space-x-2">
                <button @click="showCreateFolderModal = false" class="px-4 py-2 text-gray-600">Abbrechen</button>
                <button @click="createFolder" class="px-4 py-2 bg-primary text-white rounded">Erstellen</button>
            </div>
        </div>
    </div>

    <!-- Edit Info Modal -->
    <div v-if="showEditInfoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow-lg w-3/4 max-w-4xl h-3/4 flex flex-col">
            <h3 class="text-lg font-medium mb-4">Ordner-Info bearbeiten</h3>
            <div class="flex-grow overflow-auto mb-4">
                 <WysiwygEditor v-model="folderDescription" />
            </div>
            <div class="flex justify-end space-x-2">
                <button @click="showEditInfoModal = false" class="px-4 py-2 text-gray-600">Abbrechen</button>
                <button @click="updateDescription" class="px-4 py-2 bg-primary text-white rounded">Speichern</button>
            </div>
        </div>
    </div>

  </div>
</template>
