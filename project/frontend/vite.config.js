import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'assets/AppIcon.png', 'assets/logo.png'],
      manifest: {
        name: 'Elektron Download Center',
        short_name: 'Elektron',
        description: 'Download Center & File Manager',
        theme_color: '#5681a5',
        background_color: '#ffffff',
        display: 'standalone',
        icons: [
          {
            src: 'assets/AppIcon.png',
            sizes: '192x192', // Assuming the provided icon is at least this size, ideally we resize but for now use it
            type: 'image/png'
          },
          {
            src: 'assets/AppIcon.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  build: {
    outDir: '../public',
    emptyOutDir: true,
  }
})
