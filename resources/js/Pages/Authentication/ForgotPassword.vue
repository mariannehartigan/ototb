<template>
  <Head title="Forgot Password" />
  <div class="auth-container">
    <form @submit.prevent="submit">
      <h3 class="pb-5 text-center">Forgot Password</h3>
      <div class="pb-1">
        <label class="auth-label">Email</label>
        <input 
          v-model="form.email" 
          class="auth-input" 
          type="text" 
          @focus="clearError('email')"
        />
      </div>
      <div class="error pb-8">{{ errors.email }}</div>

      <button class="button">Send Reset Link</button>

      <div class="pt-5 pb-3 text-sm link">
        <Link href="/login">Back to Sign In</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useFormValidation } from '../../composables/validation'
import { useAuthStore } from '../../stores/authentication'

const { errors, validateField, validateForm, clearError } = useFormValidation()

const form = useForm({
  email: useAuthStore().email ?? '',
})

function submit() {
  if (!validateForm(form, ['email'])) return
  form.post('/forgotpassword')
}
</script>