import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig(({ mode }) => ({
  plugins: [
    vue(),
    vueDevTools(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: [
        'favicon.ico',
        'pwa-192.png',
        'pwa-512.png',
        'pwa-512-maskable.png',
        'screenshots/desktop-wide.png',
        'screenshots/mobile.png',
      ],
      manifest: {
        name: 'Відстеження витрат',
        short_name: 'SpendTrack',
        description: 'Відстежуйте особисті та спільні витрати з будь-якого пристрою.',
        theme_color: '#2563eb',
        background_color: '#ffffff',
        display: 'standalone',
        scope: '/',
        start_url: '/',
        icons: [
          {
            src: '/pwa-192.png',
            sizes: '192x192',
            type: 'image/png',
            purpose: 'any',
          },
          {
            src: '/pwa-512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any',
          },
          {
            src: '/pwa-512-maskable.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'maskable',
          },
        ],
        screenshots: [
          {
            src: '/screenshots/desktop-wide.png',
            sizes: '1920x820',
            type: 'image/png',
            form_factor: 'wide',
            label: 'Spend Track dashboard on desktop',
          },
          {
            src: '/screenshots/mobile.png',
            sizes: '1000x1970',
            type: 'image/png',
            label: 'Spend Track dashboard on mobile',
          },
        ],
      },
      workbox: {
        navigateFallbackDenylist: [/^\/api\//],
        globPatterns: mode === 'production' ? ['**/*.{js,css,html,ico,png,svg}'] : [],
      },
      devOptions: {
        enabled: true,
        type: 'module',
      },
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
}))
