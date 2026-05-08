<template>
  <div class="auth-container text-sm">
    <div class="pb-2">If an account exists for {{ props.email }}, a reset link has been sent.</div>
    <div>Please check your email and follow the instructions to reset your password. This link will expire in 10 minutes.</div>
  </div>
</template>

<script setup>
const props = defineProps({
  email: String,
})
</script>