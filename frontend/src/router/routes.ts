import { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue')
  },
  {
    path: '/guest',
    component: () => import('layouts/GuestLayout.vue'),
    children: [
      { 
        path: 'register', 
        component: () => import('pages/RegisterPage.vue') 
      },
      { 
        path: 'login', 
        component: () => import('pages/LoginPage.vue') },
  ],
},

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
];

export default routes;