<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow w-[600px] max-h-[80vh] flex flex-col">
          <h3 class="font-bold mb-4">Ordner auswählen</h3>
          <div class="flex-1 overflow-y-auto border p-2 mb-4">
              <ul class="space-y-1">
                  <folder-item
                    v-for="folder in tree"
                    :key="folder.id"
                    :folder="folder"
                    :selected-id="selectedId"
                    @select="$emit('select', $event)"
                  />
              </ul>
          </div>
           <div class="flex justify-end space-x-2">
              <button @click="$emit('close')" class="text-gray-500">Abbrechen</button>
          </div>
      </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import FolderItem from './FolderItem.vue';

const props = defineProps({
    tree: Array,
    selectedId: Number
});

defineEmits(['close', 'select']);
</script>
