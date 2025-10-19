import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    port: 5173,
    watch: {
      usePolling: true,  // ← CRITICAL pour Docker
      interval: 100       // vérifie toutes les 100ms
    },
    hmr: {
      overlay: true
    }
  },
  resolve: {
    alias: {
      'gsap/ScrollTrigger': '/node_modules/gsap/ScrollTrigger.js'
    }
  }
})