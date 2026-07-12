<template>
  <form @change="update">
    <div class="relative">
      <input
        v-model="form.month"
        @focus="showMonths = true"
        @blur="hideMonths"
        class="w-32"
      />

      <div
        v-if="showMonths"
        class="absolute bg-white border w-32 z-10"
      >
        <div
          v-for="month in months"
          :key="month"
          @mousedown="selectMonth(month)"
          class="px-2 py-1 hover:bg-gray-100 cursor-pointer"
        >
          {{ month }}
        </div>
      </div>
    </div>

    <input
      v-model="form.day"
      class="w-16"
    />

    <input v-model="form.description" />
    <input v-model="form.amount" />
    <input v-model="form.remaining" />
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const { income } = defineProps({
  income: Object
})

const originalDate = new Date(income.date)

const months = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
]

const showMonths = ref(false)

const form = useForm({
  month: originalDate.toLocaleString('en-US', { month: 'long' }),
  day: originalDate.getDate(),
  description: income.description,
  amount: income.amount,
  remaining: income.remaining,
})

const selectMonth = (month) => {
  form.month = month
  showMonths.value = false
}

const hideMonths = () => {
  setTimeout(() => {
    showMonths.value = false
  }, 100)
}

const update = () => {
  const monthIndex = months.indexOf(form.month)

  if (monthIndex === -1 || form.day < 1 || form.day > 31) {
    return
  }

  form.date = `${originalDate.getFullYear()}-${String(monthIndex + 1).padStart(2, '0')}-${String(form.day).padStart(2, '0')}`

  form.put(`/income/${income.id}`)
}
</script>

<!-- 

"date": "2026-06-30", 
"description": "Hack the Box", 
"amount": "2457.64", 
"remaining": "627.60", 
"received": 0,  

-->