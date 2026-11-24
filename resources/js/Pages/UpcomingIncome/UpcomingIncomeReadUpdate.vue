<template>
    <div
        class="upcomingIncomeRow"
        v-for="detail in (type === 'readUpdateCurrentIncome' ? currentDetails : futureDetails)"
        :key="detail.id"
    >

        <!-- Description -->
        <div class="upcomingIncomeDescriptionCol">
            <input
                v-if="type === 'readUpdateCurrentIncome'"
                v-model="plannedIncomeForm.description"
                @change="updateIncome"
            />

            <input
                v-if="type === 'readUpdateFutureIncome'"
                disabled
                placeholder="Future change"
            />
        </div>

        <!-- Frequency -->
        <div class="upcomingIncomeFrequencyCol">
            <input
                v-model="detail.frequency"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Amount -->
        <div class="upcomingIncomeAmountCol">
            <input
                v-model="detail.amount"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Deposited -->
        <div class="upcomingIncomeDepositedCol">

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
                    v-model="detail.day_deposited_for_monthly"
                    :style="{
                        width: `${String(detail.day_deposited_for_monthly).length}ch`
                    }"
                    @change="updateDetail(detail)"
                />

                <LettersAfterDateLikeTH
                    :number="Number(detail.day_deposited_for_monthly)"
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

        <!-- Start date -->
        <div class="upcomingIncomeEffectiveDateCol">
            <div
                v-if="type === 'readUpdateFutureIncome'"
                class="upcomingIncomeNotesCol">
                takes effect
                <input
                    v-model="detail.effective_date"
                    @change="updateDetail(detail)"
                />
            </div>
        </div>

        

        <!-- End date -->
        <div class="upcomingIncomeEndDateCol">
            <VueDatePicker
                v-if="type === 'readUpdateCurrentIncome'"
                v-model="plannedIncomeForm.endDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }"
                style="display: inline-block"
                @update:model-value="updateIncome"
            />
        </div>

        <!-- Tithe -->
        <div class="upcomingIncomeTitheCol">
            <input
                v-if="type === 'readUpdateCurrentIncome'"
                v-model="detail.tithe"
                type="checkbox"
                @change="updateDetail(detail)"
            />
        </div>

        <!-- Notes -->
        <div
            v-if="type === 'readUpdateCurrentIncome'"
            class="upcomingIncomeNotesCol"
        >
            <input
                v-model="detail.notes"
                @change="updateDetail(detail)"
            />
        </div>

    </div>


    <!-- Add future change -->
    <AddIncome
        v-if="type === 'readUpdateFutureIncome'"
        type="addFutureChangeToCurrentIncome"
        :planned-income="plannedIncome"
        :details="details"
    />


    <!-- Render future changes -->
    <UpcomingIncomeReadUpdate
        v-if="type === 'readUpdateCurrentIncome'"
        type="readUpdateFutureIncome"
        :plannedIncome="plannedIncome"
        :details="details"
    />

</template>

<script setup>
import { computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AddIncome from './AddIncome.vue';
import LettersAfterDateLikeTH from '../Components/LettersAfterDateLikeTH.vue'
const props = defineProps({
  type: String,
  plannedIncome: Object,
  details: Array
})


const currentDetails = computed(() => {
    const today = new Date().toISOString().split('T')[0]

    const validDetails = props.details
        .filter(detail => detail.effective_date <= today)

    if (!validDetails.length) {
        return []
    }

    const mostRecent = validDetails.reduce((latest, detail) => {
        return detail.effective_date > latest.effective_date
            ? detail
            : latest
    })

    return [mostRecent]
})

const futureDetails = computed(() => {
    const today = new Date().toISOString().split('T')[0]

    return props.details
        .filter(detail => detail.effective_date > today)
        .sort((a, b) => a.effective_date.localeCompare(b.effective_date))
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

const plannedIncomeForm = useForm({
  description: props.plannedIncome.description,
  startDate: props.plannedIncome.start_date,
  endDate: props.plannedIncome.end_date,
  details: props.details,
})

const updateIncome = () => {
  plannedIncomeForm.put(`/plannedincome/${props.plannedIncome.id}`)
}

function updateDetail(detail) {
    router.put(`/plannedincomedetail/${detail.id}`, {
        amount: detail.amount,
        frequency: detail.frequency,
        day_of_week_for_weekly: detail.day_of_week_for_weekly,
        day_deposited_for_monthly: detail.day_deposited_for_monthly,
        tithe: detail.tithe,
        deposit_date_for_biweekly_quarterly_yearly_onetime: detail.deposit_date_for_biweekly_quarterly_yearly_onetime,
        effective_date: detail.effective_date,
        notes: detail.notes,
    })
}

</script>