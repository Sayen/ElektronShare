<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Markdown } from 'tiptap-markdown'
import { watch, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
  extensions: [
    StarterKit,
    Markdown,
  ],
  content: props.modelValue,
  onUpdate: () => {
    // We want to emit markdown
    const markdown = editor.value.storage.markdown.getMarkdown();
    emit('update:modelValue', markdown)
  },
})

watch(() => props.modelValue, (newValue) => {
  // Only update if content is different to avoid cursor jumps
  // This is tricky with markdown conversion, simplified for now
  if (editor.value && newValue !== editor.value.storage.markdown.getMarkdown()) {
     editor.value.commands.setContent(newValue)
  }
})

onBeforeUnmount(() => {
  editor.value.destroy()
})
</script>

<template>
  <div v-if="editor" class="border border-gray-300 rounded-md overflow-hidden">
    <div class="bg-gray-50 border-b border-gray-300 p-2 flex gap-2">
       <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-gray-200': editor.isActive('bold') }" class="p-1 rounded hover:bg-gray-200">B</button>
       <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-gray-200': editor.isActive('italic') }" class="p-1 rounded hover:bg-gray-200">I</button>
       <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'bg-gray-200': editor.isActive('heading', { level: 1 }) }" class="p-1 rounded hover:bg-gray-200">H1</button>
       <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'bg-gray-200': editor.isActive('heading', { level: 2 }) }" class="p-1 rounded hover:bg-gray-200">H2</button>
       <button @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-gray-200': editor.isActive('bulletList') }" class="p-1 rounded hover:bg-gray-200">List</button>
    </div>
    <editor-content :editor="editor" class="prose max-w-none p-4 min-h-[150px] focus:outline-none" />
  </div>
</template>

<style>
.ProseMirror:focus {
    outline: none;
}
</style>
