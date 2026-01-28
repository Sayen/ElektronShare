<template>
  <div>
    <!-- Top Bar: Search & Sort -->
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
       <!-- Breadcrumbs / Home Link -->
        <nav class="flex text-gray-500 dark:text-gray-400 text-sm overflow-x-auto whitespace-nowrap items-center">
            <button @click="loadFolder(null)" class="hover:text-elektron-blue font-medium transition-colors"
                    :class="{'font-bold text-gray-800 dark:text-gray-200 cursor-default': !currentFolder?.parent_id && !isSearching, 'hover:text-elektron-blue': currentFolder?.parent_id || isSearching}">
                Home
            </button>

            <template v-if="!isSearching">
                <span v-for="(crumb, idx) in breadcrumbs" :key="idx" class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <button @click="loadFolder(crumb.id)" class="hover:text-elektron-blue font-medium transition-colors">{{ crumb.name }}</button>
                </span>
                <span v-if="currentFolder && currentFolder.parent_id" class="flex items-center text-gray-800 dark:text-gray-200">
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    {{ currentFolder.name }}
                </span>
            </template>
            <span v-else class="flex items-center text-gray-800 dark:text-gray-200">
                 <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                 Suche: "{{ searchQuery }}"
            </span>
        </nav>

        <div class="flex flex-wrap gap-2">
            <input v-model="searchQuery" @keyup.enter="performSearch" placeholder="Suchen..." class="border border-gray-300 dark:border-gray-600 rounded px-3 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:border-elektron-blue flex-grow min-w-[120px]">
            <button @click="performSearch" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 px-3 py-1 rounded text-sm transition-colors dark:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>

            <select v-model="sortOption" class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:border-elektron-blue flex-shrink-0">
                <option value="name_asc">Name (A-Z)</option>
                <option value="name_desc">Name (Z-A)</option>
                <option value="date_desc">Neueste zuerst</option>
                <option value="date_asc">Älteste zuerst</option>
            </select>
        </div>
    </div>

    <div v-if="loading" class="text-center py-10 text-gray-500 dark:text-gray-400">Lade...</div>

    <div v-else>
      <div v-if="currentFolder && currentFolder.description && !isSearching" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6 border border-gray-200 dark:border-gray-700 transition-colors">
           <div ref="viewerRef" class="toastui-editor-contents dark:text-gray-200"></div>
      </div>

      <div v-if="sortedFolders.length > 0" class="mb-8">
        <h3 class="text-sm font-bold text-gray-400 uppercase mb-3 tracking-wider">Ordner</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div v-for="folder in sortedFolders" :key="folder.id"
                 @click="loadFolder(folder.id)"
                 class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-elektron-blue dark:hover:border-elektron-blue hover:shadow-md transition flex items-center">
                 <svg class="w-8 h-8 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                 <div class="overflow-hidden">
                     <span class="font-medium truncate block dark:text-gray-200">{{ folder.name }}</span>
                     <span v-if="isSearching" class="text-xs text-gray-400">in {{ folder.parent_name || 'Home' }}</span>
                 </div>
            </div>
        </div>
      </div>

      <div>
        <h3 class="text-sm font-bold text-gray-400 uppercase mb-3 tracking-wider">Dateien</h3>
        <div v-if="sortedFiles.length === 0" class="text-gray-400 italic">Keine Dateien vorhanden.</div>
        <div class="grid grid-cols-1 gap-3">
           <div v-for="file in sortedFiles" :key="file.id" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between transition-colors">
              <div class="flex items-center overflow-hidden flex-1 cursor-pointer" @click="previewFile(file)">
                <svg v-if="file.mime_type === 'application/pdf'" class="w-8 h-8 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                <svg v-else class="w-8 h-8 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>

                <div class="truncate">
                    <div class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ file.filename }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatDate(file.created_at) }}
                        <span v-if="isSearching" class="ml-2 bg-gray-100 dark:bg-gray-700 px-1 rounded">in {{ file.folder_name || 'Home' }}</span>
                    </div>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                  <button v-if="file.mime_type === 'application/pdf' || file.filename.endsWith('.pdf')" @click="previewFile(file)" class="p-2 text-gray-500 hover:text-elektron-blue dark:text-gray-400 dark:hover:text-elektron-blue" title="Vorschau">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  </button>
                  <a :href="'/uploads/' + file.path" download target="_blank" class="p-2 text-elektron-blue hover:bg-blue-50 dark:hover:bg-gray-700 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                  </a>
              </div>
           </div>
        </div>
      </div>
    </div>

    <!-- File Preview Modal -->
    <div v-if="previewingFile" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4" @click.self="previewingFile = null">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="font-bold text-lg dark:text-white truncate">{{ previewingFile.filename }}</h3>
                <button @click="previewingFile = null" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-900 flex justify-center items-center p-4">
                <iframe v-if="previewingFile.filename.endsWith('.pdf')" :src="'/uploads/' + previewingFile.path" class="w-full h-full min-h-[500px]" frameborder="0"></iframe>
                <img v-else-if="['jpg','jpeg','png','gif','webp'].includes(previewingFile.filename.split('.').pop().toLowerCase())" :src="'/uploads/' + previewingFile.path" class="max-w-full max-h-full object-contain">
                <div v-else class="text-center p-10">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Vorschau nicht verfügbar</p>
                    <a :href="'/uploads/' + previewingFile.path" download class="bg-elektron-blue text-white px-4 py-2 rounded">Herunterladen</a>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

const route = useRoute();
const router = useRouter();

const currentFolder = ref(null);
const folders = ref([]);
const files = ref([]);
const loading = ref(false);
const breadcrumbs = ref([]);

const searchQuery = ref('');
const isSearching = ref(false);
const sortOption = ref('name_asc');

const previewingFile = ref(null);
const viewerRef = ref(null);
let viewerInstance = null;

function updateViewer() {
    nextTick(() => {
        if (!currentFolder.value?.description || !viewerRef.value) return;

        if(viewerInstance) {
           // Toast UI Viewer is tricky to destroy/reset cleanly without full reload sometimes,
           // but creating new instance usually overrides content.
        }

        viewerRef.value.innerHTML = ''; // Clear previous

        viewerInstance = new Viewer({
            el: viewerRef.value,
            initialValue: currentFolder.value.description
        });
    });
}

function formatDate(dateStr) {
    if(!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('de-CH');
}

async function loadFolder(id) {
    if (isSearching.value && !id) {
        // Clear search if Home clicked
        searchQuery.value = '';
        isSearching.value = false;
    }

    loading.value = true;
    try {
        const url = id ? `/api/files.php?folder_id=${id}` : '/api/files.php';
        const res = await axios.get(url);
        currentFolder.value = res.data.current_folder;
        folders.value = res.data.folders;
        files.value = res.data.files;
        breadcrumbs.value = res.data.breadcrumbs || [];

        // Reset search state if we navigated
        if (!isSearching.value) {
            searchQuery.value = '';
        }

        updateViewer();
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function performSearch() {
    if (!searchQuery.value.trim()) {
        isSearching.value = false;
        loadFolder(currentFolder.value?.id);
        return;
    }

    loading.value = true;
    isSearching.value = true;
    try {
        const res = await axios.get(`/api/files.php?action=search&query=${encodeURIComponent(searchQuery.value)}`);
        folders.value = res.data.folders;
        files.value = res.data.files;
        currentFolder.value = null; // No specific folder in search results
        breadcrumbs.value = [];
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

const sortedFolders = computed(() => {
    let list = [...folders.value];
    return sortList(list);
});

const sortedFiles = computed(() => {
    let list = [...files.value];
    return sortList(list, true);
});

function sortList(list, isFile=false) {
    return list.sort((a, b) => {
        if (sortOption.value === 'name_asc') {
            return (a.name || a.filename).localeCompare(b.name || b.filename);
        } else if (sortOption.value === 'name_desc') {
            return (b.name || b.filename).localeCompare(a.name || a.filename);
        } else if (sortOption.value === 'date_desc') {
            // Folders might not have created_at in default schema, check db
            // Assuming they do or we ignore date sort for folders if missing
            return new Date(b.created_at || 0) - new Date(a.created_at || 0);
        } else if (sortOption.value === 'date_asc') {
            return new Date(a.created_at || 0) - new Date(b.created_at || 0);
        }
        return 0;
    });
}

function previewFile(file) {
    previewingFile.value = file;
}

watch(() => route.query.folder, (newId) => {
    if(!isSearching.value) {
        loadFolder(newId || null);
    }
}, { immediate: true });

</script>
