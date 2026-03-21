import vue from "@vitejs/plugin-vue"
import { defineConfig } from "vite"
import vuetify from "vite-plugin-vuetify"

export default defineConfig({
  plugins: [
    vue(),
    vuetify({ autoImport: true }),
  ],
  resolve: {
    dedupe: ["vue", "vue-router", "pinia", "vuetify"],
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    allowedHosts: ["app.my-yca.com", "localhost"],
    proxy: {
      "/api": {
        target: "http://nginx:80",
        changeOrigin: true,
        secure: false,
      },
      "/broadcasting": {
        target: "http://nginx:80",
        changeOrigin: true,
        secure: false,
      },
    },
  },
})