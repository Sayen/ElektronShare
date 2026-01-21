<template>
  <div>
    <nav class="flex mb-6 text-gray-500 text-sm overflow-x-auto whitespace-nowrap">
      <button @click="loadFolder(null)" class="hover:text-elektron-blue font-medium" :class="{'font-bold text-gray-800 cursor-default': !currentFolder?.parent_id, 'hover:text-elektron-blue': currentFolder?.parent_id}">Home</button>
      <span v-for="(crumb, idx) in breadcrumbs" :key="idx" class="flex items-center">
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <button @click="loadFolder(crumb.id)" class="hover:text-elektron-blue font-medium">{{ crumb.name }}</button>
      </span>
      <span v-if="currentFolder && currentFolder.parent_id" class="flex items-center text-gray-800">
         <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
         {{ currentFolder.name }}
      </span>
    </nav>

    <div v-if="loading" class="text-center py-10 text-gray-500">Lade...</div>

    <div v-else>
      <div v-if="currentFolder && currentFolder.description" class="bg-white p-6 rounded-lg shadow-sm mb-6">
           <div ref="viewerRef"></div>
      </div>

      <div v-if="folders.length > 0" class="mb-8">
        <h3 class="text-sm font-bold text-gray-400 uppercase mb-3 tracking-wider">Ordner</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div v-for="folder in folders" :key="folder.id"
                 @click="loadFolder(folder.id)"
                 class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 cursor-pointer hover:border-elektron-blue hover:shadow-md transition flex items-center">
                 <svg class="w-8 h-8 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                 <span class="font-medium truncate">{{ folder.name }}</span>
            </div>
        </div>
      </div>

      <div>
        <h3 class="text-sm font-bold text-gray-400 uppercase mb-3 tracking-wider">Dateien</h3>
        <div v-if="files.length === 0" class="text-gray-400 italic">Keine Dateien vorhanden.</div>
        <div class="grid grid-cols-1 gap-3">
           <div v-for="file in files" :key="file.id" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex items-center justify-between">
              <div class="flex items-center overflow-hidden">
                <svg class="w-8 h-8 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                <div class="truncate">
                    <div class="font-medium text-gray-800 truncate">{{ file.filename }}</div>
                    <div class="text-xs text-gray-500">{{ formatDate(file.created_at) }}</div>
                </div>
              </div>
              <a :href="'/uploads/' + file.path" download target="_blank" class="ml-4 p-2 text-elektron-blue hover:bg-blue-50 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
              </a>
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
// import { marked } from 'marked';
import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

const route = useRoute();
const router = useRouter();

const currentFolder = ref(null);
const folders = ref([]);
const files = ref([]);
const loading = ref(false);
const breadcrumbs = ref([]);

// const parsedDescription = computed(() => {
//     return currentFolder.value?.description ? marked(currentFolder.value.description) : '';
// });

const viewerRef = ref(null);
let viewerInstance = null;

function updateViewer() {
    nextTick(() => {
        if (!currentFolder.value?.description || !viewerRef.value) return;

        // Destroy old instance if exists to avoid duplication or weird state
        if(viewerInstance) {
            viewerInstance.destroy(); // destroy method might not exist in viewer? Viewer just replaces innerHTML.
            // Actually Viewer doesn't have destroy in same way, but creating new one on same EL works or we can setMarkdown
        }

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
    loading.value = true;
    try {
        const url = id ? `/api/files.php?folder_id=${id}` : '/api/files.php';
        const res = await axios.get(url);
        currentFolder.value = res.data.current_folder;
        folders.value = res.data.folders;
        files.value = res.data.files;
        updateViewer();
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

watch(() => route.query.folder, (newId) => {
    loadFolder(newId || null);
}, { immediate: true });

</script>
