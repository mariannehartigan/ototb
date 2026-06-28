<template>
  <div class="notloggedin-text">
    <div class="notloggedin-signin">
      <Link href="/login">
        Sign In
      </Link>
    </div>
    <div class="notloggedin-createaccount">
      <Link href="/createaccount">
        Create Account
      </Link>
    </div>
  </div>
  <div class="notloggedin-image">
    <img src="../../../public/homescreen-logo.jpg" />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>