<template>
  <Head title="Home" />
  <div v-if="!user" class="text-center pt-[7vh]">
    Sign in to organize your life!
  </div>
  <Todos v-if="user" />
</template>

<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Todos from '../Pages/Todos/Todos.vue'

const user = computed(() => usePage().props.user)
</script>