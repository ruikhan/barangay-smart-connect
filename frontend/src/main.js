import axios from 'axios'; // ← ADD
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import App from './App.vue'
import router from './router'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// ── Axios global config ──────────────────────────
axios.defaults.baseURL = import.meta.env.PROD
  ? 'https://api.my-yca.com'   // live tunnel
  : ''                          // dev: use Vite proxy
axios.defaults.headers.common['Accept'] = 'application/json'

const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary:   '#1565C0',
          secondary: '#0288D1',
          accent:    '#00897B',
          success:   '#2E7D32',
          warning:   '#F57F17',
          error:     '#C62828',
        },
      },
    },
  },
})

createApp(App)
  .use(createPinia())
  .use(router)
  .use(vuetify)
  .mount('#app')