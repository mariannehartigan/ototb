<template>
  <div class="logo-container">
    <Link href="/">
      <img class="logo" src="../../../public/logo.png" />
    </Link>
  </div>
  <div class="links">
    <div v-if="user">
      Hello {{ user.name }}!
      &nbsp;&nbsp;
      Sign Out
    </div>
    <div v-else>
      <Link href="/login">Sign in</Link>
      &nbsp;&nbsp;
      Create account
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const user = computed(() => usePage().props.user)
</script>