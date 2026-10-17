<template>
    <div
        class="upcomingFixedExpenseRow"
        v-for="detail in details"
        :key="detail.id"
    >
        <!-- Description -->
        <div class="upcomingFixedExpenseDescriptionCol">
            <input
                v-if="type === 'readUpdateCurrentFixedExpense'"
                v-model="plannedFixedExpenseForm.description"
                @change="updateFixedExpense"
            />

            <input
                v-if="type === 'readUpdateFutureFixedExpense'"
                disabled
                placeholder="Future change"
            />
        </div>

        <!-- Frequency -->
        <div class="upcomingFixedExpenseFrequencyCol">
            <input
                v-model="detail.frequency"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Amount -->
        <div class="upcomingFixedExpenseAmountCol">
            <input
                v-model="detail.amount"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Due -->
        <div class="upcomingFixedExpenseDueCol">

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
                        width: `${String(detail.day_due_for_monthly).length}ch`
                    }"
                    @change="updateDetail(detail)"
                />

                <LettersAfterDateLikeTH
                    :number="Number(detail.day_due_for_monthly)"
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
        <div class="upcomingFixedExpenseFromAccountCol">
            <input
                v-model="detail.account_taken_from"
                @change="updateDetail(detail)"
            />
        </div>
        
        <!-- Assigned to income -->
        <div class="upcomingFixedExpenseAssignedToIncomeCol">
            <input
                v-model="detail.planned_income_id"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Start date -->
        <div class="upcomingFixedExpenseEffectiveDateCol">
            <div
                v-if="type === 'readUpdateFutureFixedExpense'"
                class="upcomingFixedExpenseNotesCol">
                takes effect
                <input
                    v-model="detail.effective_date"
                    @change="updateDetail(detail)"
                />
            </div>
        </div>

        

        <!-- End date -->
        <div class="upcomingFixedExpenseEndDateCol">
            <VueDatePicker
                v-if="type === 'readUpdateCurrentFixedExpense'"
                v-model="plannedFixedExpenseForm.endDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }"
                style="display: inline-block"
                @update:model-value="updateFixedExpense"
            />
        </div>

        <!-- Tithe -->
        <div class="upcomingFixedExpenseTitheCol">
            <input
                v-if="type === 'readUpdateCurrentFixedExpense'"
                v-model="plannedFixedExpenseForm.tithe"
                type="checkbox"
                @change="updateFixedExpense"
            />
        </div>

        <!-- Notes -->
        <div
            v-if="type === 'readUpdateCurrentFixedExpense'"
            class="upcomingFixedExpenseNotesCol"
        >
            <input
                v-model="plannedFixedExpenseForm.notes"
                @change="updateFixedExpense"
            />
        </div>

    </div>


    <!-- Add future change -->
    <AddFixedExpense
        v-if="type === 'readUpdateFutureFixedExpense'"
        type="addFutureChangeToCurrentFixedExpense"
        :planned-FixedExpense="plannedFixedExpense"
        :details="details"
    />


    <!-- Render future changes -->
    <UpcomingFixedExpenseReadUpdate
        v-if="type === 'readUpdateCurrentFixedExpense'"
        type="readUpdateFutureFixedExpense"
        :plannedFixedExpense="plannedFixedExpense"
        :details="plannedFixedExpense.future_details"
    />


</template>

<script setup>
import { computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AddFixedExpense from './AddFixedExpense.vue';
import LettersAfterDateLikeTH from '../Components/LettersAfterDateLikeTH.vue'
const props = defineProps({
  type: String,
  plannedFixedExpense: Object,
  details: Array
})

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

const plannedFixedExpenseForm = useForm({
  description: props.plannedFixedExpense.description,
  startDate: props.plannedFixedExpense.start_date,
  endDate: props.plannedFixedExpense.end_date,
  tithe: props.plannedFixedExpense.tithe,
  notes: props.plannedFixedExpense.notes,
  details: props.details,
})

const updateFixedExpense = () => {
  plannedFixedExpenseForm.put(`/plannedFixedExpense/${props.plannedFixedExpense.id}`)
}

function updateDetail(detail) {
    router.put(`/plannedFixedExpensedetail/${detail.id}`, {
        amount: detail.amount,
        frequency: detail.frequency,
        day_of_week_for_weekly: detail.day_of_week_for_weekly,
        day_due_for_monthly: detail.day_due_for_monthly,
        account_taken_from: detail.account_taken_from,
        planned_income_id: detail.planned_income_id,
        deposit_date_for_biweekly_quarterly_yearly_onetime: detail.deposit_date_for_biweekly_quarterly_yearly_onetime,
        effective_date: detail.effective_date,
        notes: detail.notes,
    })
}

</script>