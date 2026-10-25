<template>
  <form @change="update">
      <div class="currentReceivedCol">
        <input v-model="form.received" type="checkbox" />
      </div>
      <div class="currentDateCol">
        <input v-model="formattedDate" />
      </div>
      <div class="currentDescriptionCol">
        <input v-model="form.description" />
      </div>
      <div class="currentAmountCol">
        <input v-model="form.amount" />
      </div>
      <div class="remainingCol">
        {{ income.remaining }}
      </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const { income } = defineProps({
  income: Object
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

<style scoped>
.remainingCol {
  font-weight: bold;
}
</style>