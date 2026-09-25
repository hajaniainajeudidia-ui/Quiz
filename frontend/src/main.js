import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const pinia = createPinia();

const app = createApp(App)
app.use(pinia)
app.use(router)
app.use(Toast, {
  position: 'top-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  transition: 'Vue-Toastification__bounce',
  maxToasts: 5,
  showProgress: false,
})
app.mount('#app')
