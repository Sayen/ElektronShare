import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['logo.png', 'AppIcon.png'],
      manifest: {
        name: 'Elektron Files App',
        short_name: 'Elektron',
        description: 'Elektron File Browser PWA',
        theme_color: '#517c9f',
        icons: [
          {
            src: 'AppIcon.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'AppIcon.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  server: {
    proxy: {
      '/api': 'http://localhost:8000',
      '/uploads': 'http://localhost:8000'
    }
  },
  build: {
    outDir: '../backend/public',
    emptyOutDir: true
  }
})
