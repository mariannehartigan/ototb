import './bootstrap';
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import InertiaPersistentLayout from './Pages/InertiaPersistentLayout.vue';
import '../css/app.css'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    const page = pages[`./Pages/${name}.vue`]
    page.default.layout = page.default.layout || InertiaPersistentLayout
    return page
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      .mount(el)
  },
  title: (title) => title ? `${import.meta.env.VITE_APP_NAME} - ${title}` : import.meta.env.VITE_APP_NAME,
})