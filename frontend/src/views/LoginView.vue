<template>
  <div class="max-w-md mx-auto mt-10 bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 transition-colors">
    <h1 class="text-2xl font-bold mb-6 text-center text-elektron-blue">Admin Login</h1>
    <form @submit.prevent="login">
      <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Passwort</label>
        <input v-model="password" type="password" class="w-full border dark:border-gray-600 rounded px-3 py-2 dark:bg-gray-700 dark:text-white focus:outline-none focus:border-elektron-blue" required>
      </div>
      <p v-if="error" class="text-red-500 mb-4">{{ error }}</p>
      <button type="submit" class="w-full bg-elektron-blue text-white font-bold py-2 px-4 rounded hover:bg-opacity-90">Login</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const password = ref('');
const error = ref('');
const router = useRouter();

async function login() {
  try {
    const res = await axios.post('/api/auth.php', {
        action: 'login',
        password: password.value
    });

    if (res.data.success) {
        router.push('/admin');
    } else {
        error.value = res.data.error || 'Login fehlgeschlagen';
    }
  } catch (e) {
    error.value = 'Netzwerkfehler';
  }
}
</script>
