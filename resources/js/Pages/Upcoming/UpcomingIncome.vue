<template>
  <!--Heading-->
  <div class="upcomingIncomeRow upcomingIncomeHeading">

    <div class="upcomingIncomeDescriptionCol">
        Description
    </div>

    <div class="upcomingIncomeFrequencyCol">
        Frequency
    </div>

    <div class="upcomingIncomeAmountCol">
        Amount
    </div>

    <div class="upcomingIncomeDepositedCol">
        Deposited
    </div>

    <div class="upcomingIncomeEffectiveDateCol">
        
    </div>

    <div class="upcomingIncomeEndDateCol">
        End Date
    </div>

    <div class="upcomingIncomeTitheCol">
        Tithe
    </div>

    <div class="upcomingIncomeNotesCol">
        Notes
    </div>

    <div class="upcomingIncomeAddCol">
    </div>

  </div>

    <UpcomingIncomeReadUpdate type="readUpdateCurrentIncome"
      v-for="plannedIncome in plannedIncomes" 
      :key="plannedIncome.id"
      :plannedIncome="plannedIncome"
      :details="plannedIncome.planned_income_details"
    />
    <br />


    <div>add new income:</div>

    <AddIncome type="addNewIncome" />


</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AddIncome from './AddIncome.vue'
import UpcomingIncomeReadUpdate from './UpcomingIncomeReadUpdate.vue'

const plannedIncomes = computed(() => usePage().props.plannedIncomes)
</script>