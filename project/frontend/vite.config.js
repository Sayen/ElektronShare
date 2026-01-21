import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      strategies: 'injectManifest',
      srcDir: 'src',
      filename: 'sw.js',
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
            sizes: '192x192',
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
  base: './',
  build: {
    outDir: '../public',
    emptyOutDir: true,
  }
})
