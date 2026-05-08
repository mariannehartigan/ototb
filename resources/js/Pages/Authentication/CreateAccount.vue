<template>
  <Head title="Create Account" />
  <div class="auth-container">
    <form @submit.prevent="createAccount">
      <div class="pb-3">
        <label class="auth-label">Name</label>
        <input 
          v-model="form.name" 
          class="auth-input" 
          type="text"
          @focus="clearError('name')" 
          @blur="validateField('name', form)"          
        />
        <div class="error">{{ errors.name }}{{ form.errors.name }}</div>
      </div>

      <div class="pb-3">
        <label class="auth-label">Email</label>
        <input 
          v-model="form.email" 
          class="auth-input" 
          type="text" 
          @focus="clearError('email')" 
          @blur="validateField('email', form)"
        />
        <div class="error">{{ errors.email }}{{ form.errors.email }}</div>        
      </div>

      <div class="pb-8">
        <label class="auth-label">Password</label>
        <input 
          v-model="form.password" 
          class="auth-input" 
          :type="showPassword ? 'text' : 'password'" 
          autocomplete="new-password" 
          @focus="clearError('password')" 
          @blur="validateField('password', form)"
        />
        <PasswordEye :show="showPassword" @toggle="showPassword = !showPassword" />
        <div class="error">{{ errors.password }}{{ form.errors.password }}</div>        
      </div>

      <button type="submit" class="button">Create Account</button>

      <div class="pt-9">
        Already have an account? <Link href="/login" class="link">Sign in</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PasswordEye from './PasswordEye.vue'
import { useFormValidation } from '../../composables/validation';

const { errors, validateField, validateForm, clearError } = useFormValidation()

const showPassword = ref(false)

const form = useForm({
  name: '',
  email: '',
  password: '',
})

function createAccount() {
  if (!validateForm(form, ['name', 'email', 'password'])) return
  form.post('/createaccount')
}

</script>