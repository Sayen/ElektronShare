<template>
  <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
    <div class="container mx-auto px-4 h-16 flex items-center justify-between">
      <router-link to="/" class="flex items-center">
        <img src="/logo.png" alt="Elektron" class="h-8 mr-3">
      </router-link>

      <button @click="$emit('toggle-menu')" aria-label="Menu" class="p-2 text-gray-600 hover:text-elektron-blue focus:outline-none">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
  </header>
</template>

<script setup>
defineEmits(['toggle-menu']);
</script>
