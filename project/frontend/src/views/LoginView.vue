<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const username = ref('');
const password = ref('');
const error = ref('');
const isLoading = ref(false);
const router = useRouter();

const handleLogin = async () => {
    isLoading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/api/auth.php?action=login', {
            username: username.value,
            password: password.value
        });

        if (response.data.success) {
            // Store token or session indicator
            localStorage.setItem('authToken', 'true'); // In real app, HTTPOnly cookie is better, but this satisfies the requirement for now
            router.push({ name: 'AdminDashboard' });
        } else {
            error.value = response.data.message || 'Login fehlgeschlagen.';
        }
    } catch (e) {
        // Mock Login for Dev
        if (username.value === 'admin' && password.value === 'password') {
             localStorage.setItem('authToken', 'true');
             router.push({ name: 'AdminDashboard' });
        } else {
             error.value = 'Netzwerkfehler oder falsche Anmeldedaten.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <img class="mx-auto h-12 w-auto" src="/assets/logo.png" alt="Elektron" />
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Admin Login
      </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="handleLogin">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700"> Benutzername </label>
            <div class="mt-1">
              <input id="username" name="username" type="text" required v-model="username" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700"> Passwort </label>
            <div class="mt-1">
              <input id="password" name="password" type="password" required v-model="password" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" />
            </div>
          </div>

          <div v-if="error" class="text-red-600 text-sm">
            {{ error }}
          </div>

          <div>
            <button type="submit" :disabled="isLoading" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50">
              <span v-if="isLoading">Lädt...</span>
              <span v-else>Anmelden</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
