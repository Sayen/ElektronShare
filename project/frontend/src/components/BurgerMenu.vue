<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const isOpen = ref(false);
const router = useRouter();

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
};

const closeMenu = () => {
  isOpen.value = false;
};

// Emit event for push repair
const emit = defineEmits(['repair-push']);
</script>

<template>
  <div class="relative">
    <button @click="toggleMenu" class="p-2 rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary">
      <span class="sr-only">Open menu</span>
      <!-- Heroicon name: outline/menu -->
      <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-56 origin-top-right bg-white border border-gray-200 divide-y divide-gray-100 rounded-md shadow-lg outline-none z-50">
      <div class="px-4 py-3">
        <p class="text-sm font-medium text-gray-900">Menu</p>
      </div>
      <div class="py-1">
        <router-link to="/" @click="closeMenu" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Home</router-link>
        <router-link to="/admin" @click="closeMenu" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Login</router-link>
        <button @click="$emit('repair-push'); closeMenu()" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
           Push-Benachrichtigungen reparieren
        </button>
      </div>
    </div>
  </div>
</template>
