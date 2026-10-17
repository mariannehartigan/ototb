<template>
  <!--Heading-->
  <div class="upcomingFixedExpenseRow upcomingFixedExpenseHeading">

    <div class="upcomingFixedExpenseDescriptionCol">
        Description
    </div>

    <div class="upcomingFixedExpenseFrequencyCol">
        Frequency
    </div>

    <div class="upcomingFixedExpenseAmountCol">
        Amount
    </div>

    <div class="upcomingFixedExpenseDueCol">
        Due
    </div>
    
    <div class="upcomingFixedExpenseFromAccountCol">
        From account
    </div>

    <div class="upcomingFixedExpenseAssignedToIncomeCol">
        Assigned to Income
    </div>

    <div class="upcomingFixedExpenseEffectiveDateCol">
        
    </div>

    <div class="upcomingFixedExpenseEndDateCol">
        End Date
    </div>

    <div class="upcomingFixedExpenseTitheCol">
        Tithe
    </div>

    <div class="upcomingFixedExpenseNotesCol">
        Notes
    </div>

    <div class="upcomingFixedExpenseAddCol">
    </div>

  </div>

<!--     <UpcomingFixedExpenseReadUpdate type="readUpdateCurrentFixedExpense"
      v-for="plannedFixedExpense in plannedFixedExpenses" 
      :key="plannedFixedExpense.id"
      :plannedFixedExpense="plannedFixedExpense"
      :details="plannedFixedExpense.planned_expense_details"
    /> -->

    <UpcomingFixedExpenseReadUpdate
        type="readUpdateCurrentFixedExpense"
        v-for="plannedFixedExpense in plannedFixedExpenses"
        :key="plannedFixedExpense.id"
        :plannedFixedExpense="plannedFixedExpense"
        :details="plannedFixedExpense.current_details"
    />
    <br />

    <div>add new fixed expense:</div>

    <AddFixedExpense type="addNewFixedExpense" />


</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AddFixedExpense from './AddFixedExpense.vue'
import UpcomingFixedExpenseReadUpdate from './UpcomingFixedExpenseReadUpdate.vue'

const plannedFixedExpenses = computed(() => usePage().props.plannedFixedExpenses)
</script>