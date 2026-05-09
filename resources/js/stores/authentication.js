import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    email: '',
  }),

  actions: {
    setEmail(value) {
      this.email = value
    }
  }
})