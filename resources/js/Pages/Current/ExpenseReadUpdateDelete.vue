<template>
  <form @change="update">
    <div class="currentExpensePaidCol">
      <input v-model="form.paid" type="checkbox" />
    </div>
      <input v-model="formattedDate" />
    <input v-model="form.description" />
    <input v-model="form.amount" />
    <input v-model="form.account_taken_from" />
    <input v-model="form.automatic_payment" />
    <input v-model="form.notes" />
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const { expense } = defineProps({
  expense: Object
})

const formattedDate = computed({
  get() {
    if (!form.date) return ''

    const date = new Date(form.date + 'T00:00:00')
    const currentYear = new Date().getFullYear()
    const month = date.toLocaleString('en-CA', { month: 'short' })
    const day = date.getDate()

    if (date.getFullYear() !== currentYear) {
      return `${month} ${day}, ${date.getFullYear()}`
    }
    return `${month} ${day}`
  },

  set(value) {
    const date = new Date(value)
    if (!isNaN(date.getTime())) {
      form.date = date.toISOString().split('T')[0]
    }
  }
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

