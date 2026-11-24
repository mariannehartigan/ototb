<template>

    <UpcomingBudgetHeader />

    <div v-for="group in budgetGroups" :key="group.label">

        <div>{{ group.label }}</div>

        <UpcomingBudgetReadUpdate
            type="readUpdateCurrentBudget"
            v-for="plannedBudget in group.budgets"
            :key="plannedBudget.id"
            :plannedBudget="plannedBudget"
            :details="plannedBudget.current_details"
        />

        <br />

        <div>add new weekly budget item:</div>

        <AddBudget type="addNewBudget" />

        <br />

    </div>

</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AddBudget from './AddBudget.vue'
import UpcomingBudgetHeader from './UpcomingBudgetHeader.vue'
import UpcomingBudgetReadUpdate from './UpcomingBudgetReadUpdate.vue'

const page = usePage()

const budgetGroups = computed(() => [
    {
        label: 'Weekly',
        budgets: page.props.plannedBudgetsWeekly ?? []
    },
    {
        label: 'Monthly',
        budgets: page.props.plannedBudgetsMonthly ?? []
    },
    {
        label: 'Quarterly',
        budgets: page.props.plannedBudgetsQuarterly ?? []
    },
    {
        label: 'Yearly',
        budgets: page.props.plannedBudgetsYearly ?? []
    },
    {
        label: 'One Time',
        budgets: page.props.plannedBudgetsOneTime ?? []
    }
])

</script>