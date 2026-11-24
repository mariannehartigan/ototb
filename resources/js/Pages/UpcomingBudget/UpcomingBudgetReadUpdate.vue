<template>
    <div
        class="upcomingBudgetRow"
        v-for="detail in details"
        :key="detail.id"
    >
        <!-- Description -->
        <div class="upcomingBudgetDescriptionCol">
            <input
                v-if="type === 'readUpdateCurrentBudget'"
                v-model="plannedBudgetForm.description"
                @change="updateBudget"
            />

            <input
                v-if="type === 'readUpdateFutureBudget'"
                disabled
                placeholder="Future change"
            />
        </div>

        <!-- Amount -->
        <div class="upcomingBudgetAmountCol">
            <input
                v-model="detail.amount"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Due -->
        <div class="upcomingBudgetDueCol">

            <!-- weekly -->
            <input
                v-if="detail.frequency === 'weekly'"
                v-model="detail.day_of_week_for_weekly"
                @change="updateDetail(detail)"
            />

            <!-- monthly -->
            <span v-if="detail.frequency === 'monthly'">
                <label>deposit day </label>

                <input
                    v-model="detail.day_due_for_monthly"
                    :style="{
                        width: `${String(detail.day_for_monthly).length}ch`
                    }"
                    @change="updateDetail(detail)"
                />

                <LettersAfterDateLikeTH
                    :number="Number(detail.day_for_monthly)"
                />
            </span>

            <!-- biweekly, quarterly, yearly, onetime -->
            <span
                v-if="
                    detail.frequency === 'biweekly' ||
                    detail.frequency === 'quarterly' ||
                    detail.frequency === 'yearly' ||
                    detail.frequency === 'onetime'
                "
            >
                <label
                    v-if="
                        detail.frequency === 'biweekly' ||
                        detail.frequency === 'quarterly' ||
                        detail.frequency === 'yearly'
                    ">
                    sample date:
                </label>

                <input
                    v-model="detail.deposit_date_for_biweekly_quarterly_yearly_onetime"
                    @change="updateDetail(detail)"
                    class="depositDateInput"
                />
                <VueDatePicker
                    v-model="detail.deposit_date_for_biweekly_quarterly_yearly_onetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    @update:model-value="updateDetail(detail)"
                />

                <span v-if="detail.frequency === 'biweekly'">
                    every other
                    {{ getDayOfWeek(detail.deposit_date_for_biweekly_quarterly_yearly_onetime) }}
                </span>

                <span v-else-if="detail.frequency === 'quarterly'">
                    every 3 months:
                    {{ getQuarterlyMonths(detail.deposit_date_for_biweekly_quarterly_yearly_onetime) }}
                </span>
            </span>
        </div>

        <!-- From account -->
        <div class="upcomingBudgetFromAccountCol">
            <input
                v-model="detail.account_taken_from"
                @change="updateDetail(detail)"
            />
        </div>
        
        <!-- Assigned to income -->
        <div class="upcomingBudgetAssignedToIncomeCol">
            <select
                v-model="detail.planned_income_id"
                @change="updateDetail(detail)"
            >
                <option :value="null">Not assigned</option>

                <option
                    v-for="income in plannedIncomes"
                    :key="income.id"
                    :value="income.id"
                >
                    {{ income.description }}
                </option>
            </select>
        </div>

        <!-- Start date -->
        <div class="upcomingBudgetEffectiveDateCol">
            <div
                v-if="type === 'readUpdateFutureBudget'"
                class="upcomingBudgetNotesCol">
                takes effect
                <input
                    v-model="detail.effective_date"
                    @change="updateDetail(detail)"
                />
            </div>
        </div>

        

        <!-- End date -->
        <div class="upcomingBudgetEndDateCol">
            <VueDatePicker
                v-if="type === 'readUpdateCurrentBudget'"
                v-model="plannedBudgetForm.endDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }"
                style="display: inline-block"
                @update:model-value="updateBudget"
            />
        </div>

        <!-- Tithe -->
        <div class="upcomingBudgetTitheCol">
            <input
                v-if="type === 'readUpdateCurrentBudget'"
                v-model="plannedBudgetForm.tithe"
                type="checkbox"
                @change="updateBudget"
            />
        </div>

        <!-- Notes -->
        <div
            v-if="type === 'readUpdateCurrentBudget'"
            class="upcomingBudgetNotesCol"
        >
            <input
                v-model="plannedBudgetForm.notes"
                @change="updateBudget"
            />
        </div>

    </div>


    <!-- Add future change -->
    <AddBudget
        v-if="type === 'readUpdateFutureBudget'"
        type="addFutureChangeToCurrentBudget"
        :planned-Budget="plannedBudget"
        :details="details"
    />


    <!-- Render future changes -->
    <UpcomingBudgetReadUpdate
        v-if="type === 'readUpdateCurrentBudget'"
        type="readUpdateFutureBudget"
        :plannedBudget="plannedBudget"
        :details="plannedBudget.future_details"
    />


</template>

<script setup>
import { computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AddBudget from './AddBudget.vue';
import LettersAfterDateLikeTH from '../Components/LettersAfterDateLikeTH.vue'
const props = defineProps({
  type: String,
  plannedBudget: Object,
  details: Array
})
const plannedIncomes = computed(() => usePage().props.plannedIncomes)



// effective date
function displayEffectiveDate(date) {
    const today = new Date().toISOString().split('T')[0]

    if (date <= today) {
        return 'now'
    }

    const [year, month, day] = date.split('-')

    return `${month}-${day}-${year}`
}
function startEditingDate(event, detail) {
    event.target.value = detail.effective_date
    event.target.select()
}
function changeEffectiveDate(event, detail) {
    const value = event.target.value

    const [month, day, year] = value.split('-')

    if (month && day && year) {
        detail.effective_date = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`

        updateDetail(detail)
    }
}

function getDayOfWeek(date) {
        return new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long'
        })
    }

    function getQuarterlyMonths(date) {
        const startDate = new Date(date + 'T00:00:00')
        const startMonth = startDate.getMonth()
        const months = []

        for (let i = 0; i < 4; i++) {
            const month = new Date(2026, startMonth + (i * 3), 1)

            months.push(
                month.toLocaleDateString('en-US', {
                    month: 'short'
                })
            )
        }

        return months.join(', ')
    }

const plannedBudgetForm = useForm({
  description: props.plannedBudget.description,
  startDate: props.plannedBudget.start_date,
  endDate: props.plannedBudget.end_date,
  tithe: props.plannedBudget.tithe,
  notes: props.plannedBudget.notes,
  details: props.details,
})

const updateBudget = () => {
  plannedBudgetForm.put(`/plannedBudget/${props.plannedBudget.id}`)
}

function updateDetail(detail) {
    router.put(`/plannedBudgetdetail/${detail.id}`, {
        amount: detail.amount,
        frequency: detail.frequency,
        day_of_week_for_weekly_biweekly: detail.day_of_week_for_weekly_biweekly,
        day_due_for_monthly: detail.day_due_for_monthly,
        account_taken_from: detail.account_taken_from,
        planned_income_id: detail.planned_income_id,
        deposit_date_for_biweekly_quarterly_yearly_onetime: detail.deposit_date_for_biweekly_quarterly_yearly_onetime,
        effective_date: detail.effective_date,
        tithe: detail.tithe,
        notes: detail.notes,
    })
}

</script>