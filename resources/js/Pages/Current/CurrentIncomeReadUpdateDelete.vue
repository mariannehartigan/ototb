<template>
  <form @change="update">
      <div class="currentReceivedCol">
        <input v-model="form.received" type="checkbox" />
      </div>
      <div class="currentDateCol">
        <input v-model="form.date" />
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

<style scoped>
.remainingCol {
  font-weight: bold;
}
</style>