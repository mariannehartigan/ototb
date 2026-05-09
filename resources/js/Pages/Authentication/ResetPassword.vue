<template>
  <Head title="Reset Password" />
  <div class="auth-container">
    <h3 class="pb-5">Reset password for {{ props.email }}:</h3>

    <form @submit.prevent="submit">
      
      <div>
        <label class="auth-label">New password</label>
        <input 
          v-model="form.password" 
          class="auth-input" 
          :type="showPassword ? 'text' : 'password'" 
          autocomplete="new-password"
          @focus="clearError('password')"
        />
        <PasswordEye :show="showPassword" @toggle="showPassword = !showPassword" />
      </div>

      <div class="error pb-8">{{ errors.password }}{{ form.errors.password }}</div>

      <button type="submit" class="button">Reset Password</button>

    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import PasswordEye from './PasswordEye.vue'
import { useFormValidation } from '../../composables/validation'

const props = defineProps({
  token: String,
  email: String,
})

const showPassword = ref(false)

const { errors, validateField, validateForm, clearError } = useFormValidation()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
})

function submit() {
  if (!validateForm(form, ['password'])) return
  form.post('/resetpassword')
}
</script>

<style scoped>
.auth-label {
  width: 7vw;
}
</style>