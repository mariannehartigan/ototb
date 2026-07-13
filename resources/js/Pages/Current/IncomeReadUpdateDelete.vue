<template>
  <form @change="update">
    <input v-model="form.date" />
    <input v-model="form.description" />
    <input v-model="form.amount" />
    <input v-model="form.remaining" />
    <input v-model="form.received" />
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const { income } = defineProps({
  income: Object
})

const form = useForm({
  date: income.date.split('T')[0],
  description: income.description,
  amount: income.amount,
  remaining: income.remaining,
  received: income.received,
})

const update = () => {
  form.put(`/income/${income.id}`)
}
</script>
