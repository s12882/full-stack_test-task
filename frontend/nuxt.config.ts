import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['~/assets/css/main.css'],
  vite: {
    plugins: [tailwindcss()],
  },
  runtimeConfig: {
    // Overridden by NUXT_API_BASE_INTERNAL from Docker
    apiBaseInternal: 'http://localhost:8000/api',
    public: {
      apiBase: 'http://localhost:8000/api',
    },
  },
})
