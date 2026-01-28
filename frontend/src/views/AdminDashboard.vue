<template>
  <div>
    <h1 class="text-3xl font-bold mb-6 text-elektron-blue dark:text-white">Admin Dashboard</h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden transition-colors">
      <!-- Tabs -->
      <div class="flex border-b bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
        <button @click="currentTab = 'files'" :class="{'border-b-2 border-elektron-blue text-elektron-blue bg-white dark:bg-gray-800': currentTab === 'files', 'text-gray-600 dark:text-gray-400': currentTab !== 'files'}" class="flex-1 py-4 px-6 text-center font-medium hover:bg-white dark:hover:bg-gray-800 transition">Dateimanager</button>
        <button @click="currentTab = 'push'" :class="{'border-b-2 border-elektron-blue text-elektron-blue bg-white dark:bg-gray-800': currentTab === 'push', 'text-gray-600 dark:text-gray-400': currentTab !== 'push'}" class="flex-1 py-4 px-6 text-center font-medium hover:bg-white dark:hover:bg-gray-800 transition">Push Nachrichten</button>
        <button @click="currentTab = 'settings'" :class="{'border-b-2 border-elektron-blue text-elektron-blue bg-white dark:bg-gray-800': currentTab === 'settings', 'text-gray-600 dark:text-gray-400': currentTab !== 'settings'}" class="flex-1 py-4 px-6 text-center font-medium hover:bg-white dark:hover:bg-gray-800 transition">Einstellungen</button>
      </div>

      <div class="p-4 md:p-6">
        <!-- Files Tab -->
        <div v-if="currentTab === 'files'">
            <div class="mb-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-bold dark:text-white">Inhalt verwalten</h2>
                <div class="flex space-x-2 w-full md:w-auto">
                    <input v-model="searchQuery" @keyup.enter="performSearch" placeholder="Suchen..." class="border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:border-elektron-blue flex-grow">
                    <button @click="performSearch" class="bg-gray-200 dark:bg-gray-600 px-3 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                     <select v-model="sortOption" class="border border-gray-300 dark:border-gray-600 rounded px-2 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:border-elektron-blue">
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                        <option value="date_desc">Neueste</option>
                        <option value="date_asc">Älteste</option>
                    </select>
                    <button @click="showCreateFolder = true" class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 px-4 py-2 rounded text-gray-800 dark:text-white whitespace-nowrap">Neuer Ordner</button>
                </div>
            </div>

             <div class="mb-4 text-sm text-gray-500 dark:text-gray-400 flex flex-wrap items-center">
                <template v-if="!isSearching">
                    <span v-for="(crumb, idx) in breadcrumbs" :key="idx" class="flex items-center">
                        <span class="mx-1" v-if="idx > 0">/</span>
                        <button v-if="idx < breadcrumbs.length - 1" @click="loadFolder(crumb.id)" class="hover:text-elektron-blue font-medium transition-colors">{{ crumb.name }}</button>
                        <strong v-else class="text-gray-800 dark:text-gray-200 cursor-default">{{ crumb.name }}</strong>
                    </span>
                </template>
                 <span v-else class="flex items-center text-gray-800 dark:text-gray-200">
                     Suche: "{{ searchQuery }}"
                </span>
             </div>

            <div v-if="!isSearching" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded p-6 text-center mb-6 transition-colors" @dragover.prevent @drop.prevent="handleDrop">
                <p class="text-gray-500 dark:text-gray-400 mb-2">Dateien hierher ziehen oder auswählen</p>
                <input type="file" ref="fileInput" @change="handleFileSelect" class="hidden" multiple>
                <button @click="$refs.fileInput.click()" class="text-elektron-blue underline">Durchsuchen</button>
            </div>

            <!-- Upload Progress -->
            <div v-if="uploads.length > 0" class="mb-6 space-y-2">
                <div v-for="(up, idx) in uploads" :key="idx" class="bg-gray-100 dark:bg-gray-700 rounded p-2 text-sm flex items-center justify-between">
                    <span class="truncate max-w-xs dark:text-gray-200">{{ up.file.name }}</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-24 bg-gray-300 dark:bg-gray-600 rounded-full h-2">
                            <div class="bg-elektron-blue h-2 rounded-full" :style="{width: up.progress + '%'}"></div>
                        </div>
                        <span class="text-xs w-8 text-right dark:text-gray-400">{{ up.progress }}%</span>
                    </div>
                </div>
            </div>

            <ul class="divide-y divide-gray-200 dark:divide-gray-700 mb-6">
                <li v-for="folder in sortedFolders" :key="'f'+folder.id" class="py-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 px-2 rounded transition-colors">
                    <div @click="loadFolder(folder.id)" class="cursor-pointer flex items-center min-w-0">
                         <svg class="w-6 h-6 text-yellow-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        <span class="truncate dark:text-gray-200">{{ folder.name }}</span>
                        <span v-if="isSearching" class="ml-2 text-xs text-gray-400">in {{ folder.parent_name || 'Home' }}</span>
                    </div>
                    <div class="flex space-x-2 shrink-0">
                        <button @click="startRename('folder', folder)" class="text-gray-500 hover:text-elektron-blue dark:text-gray-400" title="Umbenennen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button @click="startMove('folder', folder)" class="text-gray-500 hover:text-elektron-blue dark:text-gray-400" title="Verschieben">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </button>
                        <button @click="deleteItem('folder', folder.id)" class="text-red-500 hover:text-red-700" title="Löschen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </li>
                 <li v-for="file in sortedFiles" :key="'fil'+file.id" class="py-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 px-2 rounded transition-colors">
                    <div class="flex items-center min-w-0">
                         <svg class="w-6 h-6 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <div class="truncate">
                             <div class="dark:text-gray-200 truncate">{{ file.filename }}</div>
                             <div class="text-xs text-gray-500 dark:text-gray-400" v-if="isSearching">in {{ file.folder_name || 'Home' }}</div>
                        </div>
                    </div>
                    <div class="flex space-x-2 shrink-0">
                         <button @click="startRename('file', file)" class="text-gray-500 hover:text-elektron-blue dark:text-gray-400" title="Umbenennen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                         </button>
                         <button @click="startMove('file', file)" class="text-gray-500 hover:text-elektron-blue dark:text-gray-400" title="Verschieben">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                         </button>
                        <button @click="deleteItem('file', file.id)" class="text-red-500 hover:text-red-700" title="Löschen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </li>
            </ul>

            <div class="mb-6" v-if="currentFolder && !isSearching">
                <label class="block font-bold mb-2 dark:text-gray-200">Ordner Beschreibung</label>
                <div ref="editorRef" class="mb-2 bg-white dark:bg-white rounded"></div> <!-- Editor needs white bg usually or complex adaptation -->
                <button @click="updateFolder" class="mt-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Speichern</button>
             </div>

            <!-- Modals -->
            <div v-if="showCreateFolder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow w-96">
                    <h3 class="font-bold mb-4 dark:text-white">Neuer Ordner</h3>
                    <input v-model="newFolderName" class="w-full border p-2 mb-4 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded" placeholder="Name">
                    <div class="flex justify-end space-x-2">
                        <button @click="showCreateFolder = false" class="text-gray-500 dark:text-gray-400">Abbrechen</button>
                        <button @click="createFolder" class="bg-elektron-blue text-white px-4 py-2 rounded">Erstellen</button>
                    </div>
                </div>
            </div>

             <div v-if="showRenameModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded shadow w-96">
                    <h3 class="font-bold mb-4 dark:text-white">Umbenennen</h3>
                    <input v-model="renameValue" class="w-full border p-2 mb-4 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded" placeholder="Neuer Name">
                    <div class="flex justify-end space-x-2">
                        <button @click="showRenameModal = false" class="text-gray-500 dark:text-gray-400">Abbrechen</button>
                        <button @click="performRename" class="bg-elektron-blue text-white px-4 py-2 rounded">Speichern</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Push Tab -->
        <div v-if="currentTab === 'push'">
            <h2 class="text-xl font-bold mb-4 dark:text-white">Push Nachricht senden</h2>

             <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded mb-6 text-sm text-blue-800 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
                <span class="font-bold">{{ pushStats.length }}</span> Abonnenten registriert.
            </div>

             <div class="mb-8">
                <h3 class="font-bold mb-2 dark:text-white">Abonnenten Liste (Technisch)</h3>
                <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase">
                            <tr>
                                <th class="p-3">Datum</th>
                                <th class="p-3">IP Adresse</th>
                                <th class="p-3">User Agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="sub in pushStats" :key="sub.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300">
                                <td class="p-3 whitespace-nowrap">{{ new Date(sub.created_at).toLocaleString('de-CH') }}</td>
                                <td class="p-3 font-mono">{{ sub.ip_address || '-' }}</td>
                                <td class="p-3 text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs" :title="sub.user_agent">{{ sub.user_agent || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form @submit.prevent="sendPush" class="max-w-lg">
                <div class="mb-4">
                    <label class="block font-bold mb-2 dark:text-white">Titel</label>
                    <input v-model="pushForm.title" class="w-full border dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white" required>
                </div>
                 <div class="mb-4">
                    <label class="block font-bold mb-2 dark:text-white">Nachricht</label>
                    <textarea v-model="pushForm.body" class="w-full border dark:border-gray-600 p-2 rounded h-24 dark:bg-gray-700 dark:text-white" required></textarea>
                </div>
                 <div class="mb-4">
                    <label class="block font-bold mb-2 dark:text-white">Link</label>
                    <div class="flex space-x-2">
                        <input v-model="pushForm.url" class="flex-1 border dark:border-gray-600 p-2 rounded dark:bg-gray-700 dark:text-white" placeholder="/">
                        <button type="button" @click="openPushFolderSelector" class="bg-gray-200 dark:bg-gray-600 px-3 py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-500 dark:text-white">Ordner wählen</button>
                    </div>
                </div>
                <button type="submit" class="bg-elektron-blue text-white px-6 py-2 rounded hover:bg-opacity-90">Senden</button>
            </form>
        </div>

        <!-- Settings Tab -->
        <div v-if="currentTab === 'settings'">
             <h2 class="text-xl font-bold mb-4 dark:text-white">Einstellungen</h2>

             <div class="mb-8 flex items-center justify-between max-w-sm bg-gray-50 dark:bg-gray-700 p-4 rounded">
                 <span class="font-medium dark:text-white">Dark Mode</span>
                 <button @click="globalState.toggleDarkMode()" class="bg-gray-200 dark:bg-gray-600 p-2 rounded-full relative w-12 h-6 flex items-center transition-colors">
                     <div class="w-4 h-4 rounded-full bg-white shadow transform transition-transform duration-200 ease-in-out" :class="{'translate-x-6 bg-elektron-blue': globalState.darkMode, 'translate-x-0': !globalState.darkMode}"></div>
                 </button>
             </div>

             <div class="max-w-sm mb-10">
                <h3 class="font-bold mb-2 dark:text-white">Admin Passwort ändern</h3>
                <input v-model="newPassword" type="password" class="w-full border dark:border-gray-600 p-2 mb-2 dark:bg-gray-700 dark:text-white rounded" placeholder="Neues Passwort">
                <button @click="changePassword" class="bg-gray-800 dark:bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Ändern</button>
             </div>

             <button @click="logout" class="text-red-500 underline hover:text-red-700">Abmelden</button>
        </div>
      </div>

       <div class="bg-gray-50 dark:bg-gray-900 text-center text-xs text-gray-400 py-2 border-t dark:border-gray-700">
          Elektron File Browser v0.3
      </div>
    </div>

    <FolderTreeModal v-if="showFolderSelector" :tree="folderTree" :selected-id="null" @close="showFolderSelector = false" @select="handleFolderSelect" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';
import FolderTreeModal from '../components/FolderTreeModal.vue';
import { globalState } from '../state';

const router = useRouter();
const currentTab = ref('files');

// Files State
const currentFolder = ref(null);
const folders = ref([]);
const files = ref([]);
const breadcrumbs = ref([]);
const searchQuery = ref('');
const isSearching = ref(false);
const sortOption = ref('name_asc');

// Actions State
const showCreateFolder = ref(false);
const newFolderName = ref('');

const showRenameModal = ref(false);
const renameItemType = ref('');
const renameItemId = ref(null);
const renameValue = ref('');

const uploads = ref([]); // { file, progress, status }

// Push/Move Logic
const showFolderSelector = ref(false);
const folderTree = ref([]);
const folderSelectorMode = ref('push'); // 'push' or 'move'
const moveItemType = ref('');
const moveItemId = ref(null);

const pushStats = ref([]);
const pushForm = ref({ title: '', body: '', url: '/' });

const newPassword = ref('');

// Editor
const editorRef = ref(null);
let editorInstance = null;

// --- Load Logic ---

async function loadFolder(id) {
    if (isSearching.value && !id) {
        searchQuery.value = '';
        isSearching.value = false;
    }

    try {
        const url = id ? `/api/files.php?folder_id=${id}` : '/api/files.php';
        const res = await axios.get(url);
        currentFolder.value = res.data.current_folder;
        folders.value = res.data.folders;
        files.value = res.data.files;
        breadcrumbs.value = res.data.breadcrumbs || [];

        // Reset search if we navigated manually
        if (!isSearching.value) {
            searchQuery.value = '';
        }

        const desc = currentFolder.value?.description || '';
        if (editorInstance) {
            editorInstance.setMarkdown(desc);
        } else {
             nextTick(() => {
                 if(editorInstance) editorInstance.setMarkdown(desc);
                 else initEditor(desc);
             });
        }
    } catch (e) {
        console.error(e);
    }
}

async function performSearch() {
    if (!searchQuery.value.trim()) {
        isSearching.value = false;
        loadFolder(currentFolder.value?.id);
        return;
    }

    isSearching.value = true;
    try {
        const res = await axios.get(`/api/files.php?action=search&query=${encodeURIComponent(searchQuery.value)}`);
        folders.value = res.data.folders;
        files.value = res.data.files;
        currentFolder.value = null;
        breadcrumbs.value = [];
    } catch (e) {
        console.error(e);
    }
}

const sortedFolders = computed(() => sortList([...folders.value]));
const sortedFiles = computed(() => sortList([...files.value], true));

function sortList(list) {
    return list.sort((a, b) => {
        if (sortOption.value === 'name_asc') {
            return (a.name || a.filename).localeCompare(b.name || b.filename);
        } else if (sortOption.value === 'name_desc') {
            return (b.name || b.filename).localeCompare(a.name || a.filename);
        } else if (sortOption.value === 'date_desc') {
            return new Date(b.created_at || 0) - new Date(a.created_at || 0);
        } else if (sortOption.value === 'date_asc') {
            return new Date(a.created_at || 0) - new Date(b.created_at || 0);
        }
        return 0;
    });
}

function initEditor(initialValue) {
    if(!editorRef.value) return;
    editorInstance = new Editor({
        el: editorRef.value,
        height: '300px',
        initialEditType: 'wysiwyg',
        previewStyle: 'vertical',
        initialValue: initialValue
    });
}

// --- Folder Actions ---

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
        alert("Fehler beim Erstellen");
    }
}

async function updateFolder() {
    if(!editorInstance || !currentFolder.value) return;
    const markdown = editorInstance.getMarkdown();

    const params = new URLSearchParams();
    params.append('action', 'update_folder');
    params.append('id', currentFolder.value.id);
    params.append('description', markdown);

    try {
        await axios.post('/api/files.php', params);
        alert("Gespeichert");
    } catch (e) {
        alert("Fehler beim Speichern");
    }
}

// --- File Upload ---

async function handleFileSelect(e) {
    if(e.target.files.length > 0) {
        for(let i=0; i<e.target.files.length; i++) {
            await uploadFile(e.target.files[i]);
        }
        loadFolder(currentFolder.value.id);
        setTimeout(() => uploads.value = [], 5000); // Clear progress after delay
    }
}

async function handleDrop(e) {
    if(e.dataTransfer.files.length > 0) {
         for(let i=0; i<e.dataTransfer.files.length; i++) {
            await uploadFile(e.dataTransfer.files[i]);
        }
        loadFolder(currentFolder.value.id);
        setTimeout(() => uploads.value = [], 5000);
    }
}

async function uploadFile(file) {
    const uploadItem = reactive({ file, progress: 0 });
    uploads.value.push(uploadItem);

    const formData = new FormData();
    formData.append('action', 'upload_file');
    formData.append('file', file);
    formData.append('folder_id', currentFolder.value?.id || ''); // Handle root uploads? Current logic needs folder_id? Root not supported for files? API assumes folder_id.
    // Wait, API files.php: 'INSERT INTO files ...'
    // If folder_id is missing, DB error or 0?
    // In my logic `folder_id` is mandatory in insert usually?
    // Actually root folder has an ID (we fetch it). `currentFolder` should be set.

    try {
        await axios.post('/api/files.php', formData, {
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadItem.progress = percentCompleted;
            }
        });
    } catch (e) {
        console.error("Upload failed", e);
        uploadItem.progress = -1; // Error state
    }
}

// --- Rename / Move / Delete ---

function startRename(type, item) {
    renameItemType.value = type;
    renameItemId.value = item.id;
    renameValue.value = type === 'folder' ? item.name : item.filename;
    showRenameModal.value = true;
}

async function performRename() {
    if (!renameValue.value) return;
    try {
        const params = new URLSearchParams();
        params.append('action', 'rename_item');
        params.append('type', renameItemType.value);
        params.append('id', renameItemId.value);
        params.append('new_name', renameValue.value);

        await axios.post('/api/files.php', params);
        showRenameModal.value = false;

        // Refresh local list or reload
        if (isSearching.value) performSearch();
        else loadFolder(currentFolder.value?.id);
    } catch (e) {
        alert("Fehler beim Umbenennen");
    }
}

async function startMove(type, item) {
    moveItemType.value = type;
    moveItemId.value = item.id;
    folderSelectorMode.value = 'move';
    await loadFolderTree();
    showFolderSelector.value = true;
}

function openPushFolderSelector() {
    folderSelectorMode.value = 'push';
    loadFolderTree().then(() => showFolderSelector.value = true);
}

async function loadFolderTree() {
     try {
        const res = await axios.get('/api/files.php?action=get_all_folders');
        const list = res.data.folders;
        const map = {};
        const roots = [];
        list.forEach(f => {
            f.children = [];
            map[f.id] = f;
        });
        list.forEach(f => {
            if(f.parent_id && map[f.parent_id]) {
                map[f.parent_id].children.push(f);
            } else {
                roots.push(f);
            }
        });
        folderTree.value = roots;
    } catch(e) {
        alert("Fehler beim Laden der Ordner");
    }
}

async function handleFolderSelect(id) {
    if (folderSelectorMode.value === 'push') {
        pushForm.value.url = `/?folder=${id}`;
        showFolderSelector.value = false;
    } else {
        // Perform Move
        try {
            const params = new URLSearchParams();
            params.append('action', 'move_item');
            params.append('type', moveItemType.value);
            params.append('id', moveItemId.value);
            params.append('target_folder_id', id);

            const res = await axios.post('/api/files.php', params);
            if (res.data.error) {
                alert(res.data.error);
            } else {
                showFolderSelector.value = false;
                if(isSearching.value) performSearch();
                else loadFolder(currentFolder.value?.id);
            }
        } catch (e) {
             alert("Fehler beim Verschieben");
        }
    }
}

async function deleteItem(type, id) {
    if(!confirm("Wirklich löschen?")) return;
    try {
        await axios.delete('/api/files.php', { data: { type, id } });
        if(isSearching.value) performSearch();
        else loadFolder(currentFolder.value?.id);
    } catch (e) {
        alert("Fehler");
    }
}

// --- Push & Settings ---

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
        const errorMsg = e.response?.data?.error || e.message || "Fehler beim Senden";
        alert("Fehler beim Senden: " + errorMsg);
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
