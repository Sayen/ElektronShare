<template>
  <li>
      <div
        @click="$emit('select', folder.id)"
        class="cursor-pointer hover:bg-blue-50 p-1 rounded flex items-center"
        :class="{'bg-blue-100 font-bold text-elektron-blue': selectedId === folder.id}"
      >
           <svg class="w-4 h-4 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
          {{ folder.name }}
      </div>
      <ul v-if="folder.children && folder.children.length > 0" class="pl-4 border-l ml-2 mt-1">
          <folder-item
            v-for="child in folder.children"
            :key="child.id"
            :folder="child"
            :selected-id="selectedId"
             @select="$emit('select', $event)"
          />
      </ul>
  </li>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    folder: Object,
    selectedId: Number
});

defineEmits(['select']);
</script>
