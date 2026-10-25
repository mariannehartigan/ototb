<template>
  <div class="current-container">

    <div class="currentIncomeHeading currentIncomeRow">
      <div class="currentIncomeReceivedCol">
        Rec'd/Paid
      </div>
      <div class="currentIncomeDateCol">
        Date
      </div>
      <div class="currentIncomeDescriptionCol">
        Description
      </div>
      <div class="currentIncomeAmountCol">
        Amount
      </div>
      <div class="currentIncomeRemainingCol">
        Remaining
      </div>
    </div>

    <div class="currentExpenseHeading currentExpenseRow">
      <div class="currentExpensePaidCol">
        
      </div>
      <div class="currentExpenseDueCol">
        
      </div>
      <div class="currentExpenseDescriptionCol">
        
      </div>
      <div class="currentExpenseAmountCol">
        
      </div>
      <div class="currentExpenseAccountTakenFromCol">
        Account Taken From
      </div>
      <div class="currentExpenseAutomaticOrManualCol">
        Preauthorized or Manual
      </div>
      <div class="currentExpenseNotesCol">
        Notes
      </div>
    </div>

    <div v-for="income in incomesWithExpenses">
      <CurrentIncomeReadUpdateDelete :income="income" class="currentIncomeRow" />
      <Draggable 
        v-model="income.expenses" 
        item-key="id"
        :group="{ name: 'expenses' }"
        animation = "200"
        class="currentExpenseRow"
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
import CurrentIncomeReadUpdateDelete from './CurrentIncomeReadUpdateDelete.vue'
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