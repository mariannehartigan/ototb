import { boot } from 'quasar/wrappers';
import axios, { AxiosInstance } from 'axios';
/*import {echo} from 'src/boot/echo';*/
declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
  }
}

const api = axios.create({ baseURL: process.env.VUE_APP_API_URL });

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  /*config.headers['X-Socket-Id'] = echo.socketId()*/
  return config;
})

export default boot(({ app }) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$api = api;
});

export { api };