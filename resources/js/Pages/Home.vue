<template>
  <Head title="Home" />
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const user = computed(() => usePage().props.user)
</script>