<template>
  <Head title="Sign In" />
  
  <div class="auth-container">
    <form @submit.prevent="login">
      <div class="pb-3">
        <label class="auth-label">Email</label>
        <input 
          v-model="form.email" 
          type="text" 
          class="auth-input"
          @focus="clearError('email')"
          @blur="validateField('email', form)"
        />
        <div class="error">{{ errors.email }}{{ form.errors.email }}</div>
      </div>

      <div class="pb-3">
        <label class="auth-label">Password</label>
        <input 
          v-model="form.password" 
          :type="showPassword ? 'text' : 'password'" autocomplete="new-password" 
          class="auth-input" 
          @focus="clearError('password')"
          @blur="validateField('password', form)"
        />
        <PasswordEye :show="showPassword" @toggle="showPassword = !showPassword" />
        <div class="error">{{ errors.password }}{{ form.errors.password }}</div>
      </div>

      <div class="pt-3 pb-3 text-sm link">Forgot Password?</div>

      <button type="submit" class="button">Sign in</button>

      <div class="pt-5 text-sm">
        Haven't got an account? <span class="link">Create Account</span>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import PasswordEye from './PasswordEye.vue';
import { useFormValidation } from '../../composables/validation';

const { errors, validateField, validateForm, clearError } = useFormValidation()

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
})

function login() {
  if (!validateForm(form, ['email', 'password'])) return
  form.post('/login')
}
</script>