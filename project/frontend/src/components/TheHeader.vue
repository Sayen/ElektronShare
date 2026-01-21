<script setup>
</script>

<template>
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex-shrink-0 flex items-center">
          <router-link to="/">
            <img class="h-8 w-auto" src="/assets/logo.png" alt="Logo" />
          </router-link>
        </div>
        <div class="flex items-center">
             <slot name="actions"></slot>
        </div>
      </div>
    </div>
  </header>
</template>
