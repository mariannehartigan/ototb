<template>
  <form @change="update">
    <input v-model="form.date" />
    <input v-model="form.description" />
    <input v-model="form.amount" />
    <input v-model="form.account_taken_from" />
    <input v-model="form.automatic_payment" />
    <input v-model="form.notes" />
    <input v-model="form.paid" />
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const { expense } = defineProps({
  expense: Object
})

const form = useForm({
  date: expense.date,
  description: expense.description,
  amount: expense.amount,
  account_taken_from: expense.account_taken_from,
  automatic_payment: expense.automatic_payment,
  notes: expense.notes,
  paid: expense.paid,
})

const update = () => {
  form.put(`/expense/${expense.id}`)
}
</script>

<style scoped>
input {
  width: 8vw;
}
</style>
