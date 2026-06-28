<template>
  <form @change="updateIncome">
    <input v-model="incomeForm.description" class="w-[10vw]" />
    <input v-model="incomeForm.amount" class="w-[10vw]" />
    <input v-model="incomeForm.day_deposited" class="w-[10vw]" />
    <input v-model="incomeForm.frequency" class="w-[10vw]" />
    <input v-model="incomeForm.notes" class="w-[10vw]" />
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  income: Object
})

const incomeForm = useForm({
  description: props.income.description,
  amount: props.income.amount,
  day_deposited: props.income.day_deposited,
  frequency: props.income.frequency,
  notes: props.income.notes
})

const updateIncome = () => {
  incomeForm.put(`/income/${props.income.id}`)
}
</script>