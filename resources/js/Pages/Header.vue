<template>
  <div class="logo-container">
    <Link href="/">
      <!-- <div class="logo">ON TOP OF THE BILLS</div> -->
      <img class="logo" src="../../../public/logo.png" />
    </Link>
  </div>
  <div class="links">
    <div v-if="user">
      Hello {{ user.name }}!
      &nbsp;&nbsp;
      <Link href="/logout" method="delete" as="button">Sign Out</Link>
    </div>
    <div v-else>
      <Link href="/login">Sign in</Link>
      &nbsp;&nbsp;
      <Link href="/createaccount">Create account</Link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const user = computed(() => usePage().props.user)
</script>