<script setup>
import { useRouter } from 'vue-router';
import TheHeader from '../components/TheHeader.vue';

const router = useRouter();

const logout = () => {
    localStorage.removeItem('authToken');
    // Call backend logout
    fetch('/api/auth.php?action=logout');
    router.push({ name: 'Home' });
};
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <TheHeader>
        <template #actions>
            <button @click="logout" class="text-sm font-medium text-gray-500 hover:text-gray-900">Abmelden</button>
        </template>
    </TheHeader>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex space-x-4 mb-6">
             <router-link :to="{name: 'AdminFiles'}" class="px-4 py-2 rounded-md" :class="$route.name === 'AdminFiles' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'">Dateimanager</router-link>
             <router-link :to="{name: 'AdminPush'}" class="px-4 py-2 rounded-md" :class="$route.name === 'AdminPush' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'">Push Konsole</router-link>
        </div>

        <router-view></router-view>
    </div>
  </div>
</template>
