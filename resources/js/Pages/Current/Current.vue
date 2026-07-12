<template>
  <div class="current-container">
    <div v-for="income in incomesWithExpenses">
      <IncomeReadUpdateDelete :income="income" />
      <Draggable 
        v-model="income.expenses" 
        item-key="id"
        :group="{ name: 'expenses' }"
        animation = "200"
        class="pl-[1.5vw]"
        @end = "onDragEnd"
      >
        <template #item="{ element }">
          <ExpenseReadUpdateDelete :expense="element" />
        </template>
      </Draggable>
      <br />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Draggable from 'vuedraggable'
import IncomeReadUpdateDelete from './IncomeReadUpdateDelete.vue'
import ExpenseReadUpdateDelete from './ExpenseReadUpdateDelete.vue'

const incomesWithExpenses = ref(usePage().props.incomesWithExpenses.map(income => ({ ...income, expenses: [...income.expenses] })) )

watch(
  () => usePage().props.incomesWithExpenses,
  (val) => {
    incomesWithExpenses.value = val.map(c => ({ ...c }))
  }
)

function onDragEnd() {
  const newExpenseData = []
  incomesWithExpenses.value.forEach(income => {
    income.expenses.forEach((expense, index) => {
      newExpenseData.push({
        id: expense.id,
        position: index,
        income_id: income.id
      })
    })
  })
  router.put('/reorderexpenses', { expenses: newExpenseData })
}
</script>