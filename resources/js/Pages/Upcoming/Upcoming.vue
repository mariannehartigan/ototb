<template>
  <div v-for="income in incomes" :key="income.id">
    <UpcomingReadUpdateDelete :income="income" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import UpcomingReadUpdateDelete from './UpcomingReadUpdateDelete.vue';

const incomes = computed(() => usePage().props.incomes)
</script>