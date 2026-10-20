<template>

    <div class="upcomingFixedExpenseRow">

        <!-- Description -->
        <div class="upcomingFixedExpenseDescriptionCol">
            <input
                v-if="type === 'addNewFixedExpense'"
                type="text"
                v-model="plannedFixedExpenseForm.description"
                @blur="descriptionBlurred"
                placeholder="Description">
            <input v-else disabled placeholder="add future change">
        </div>

        <!-- Frequency -->
        <div class="upcomingFixedExpenseFrequencyCol">
            <FrequencyDropdown v-model="plannedFixedExpenseForm.frequency" />
        </div>

        <!-- Amount -->
        <div class="upcomingFixedExpenseAmountCol">

            <!-- Monthly -->
            <div v-if="plannedFixedExpenseForm.frequency === 'monthly'">
                <div
                    v-for="(pay, index) in plannedFixedExpenseForm.monthlyPays"
                    :key="index">
                    <input
                        type="text"
                        v-model="pay.amount"
                        @input="sameAmountForAllPays(pay.amount)"
                        placeholder="amount">
                </div>
            </div>

            <!-- Everything but monthly -->
            <div v-else>
                <input
                    type="text"
                    v-model="plannedFixedExpenseForm.amount"
                    placeholder="amount">
            </div>

        </div>

        <!-- Due -->
        <div class="upcomingFixedExpenseDueCol">

            <!-- Weekly -->
            <div v-if="plannedFixedExpenseForm.frequency === 'weekly'">

                <DayOfWeekDropdown
                    v-model="plannedFixedExpenseForm.dayOfWeekForWeeklyBiweekly"
                />

            </div>


            <!-- Biweekly -->
            <div v-if="plannedFixedExpenseForm.frequency === 'biweekly'">

                <span v-if="type === 'addNewFixedExpense'">
                  sample due date:
                  <VueDatePicker
                      v-model="plannedFixedExpenseForm.dueDateForBiweeklyQuarterlyYearlyOnetime"
                      model-type="yyyy-MM-dd"
                      :time-config="{ enableTimePicker: false }"
                      :formats="{ input: 'MMMM d, yyyy' }"
                      class="vueDatePicker"
                  />
                </span>                

                <span v-if="plannedFixedExpenseForm.dueDateForBiweeklyQuarterlyYearlyOnetime">
                    every other
                    {{ getDayOfWeek(plannedFixedExpenseForm.dueDateForBiweeklyQuarterlyYearlyOnetime) }}
                </span>

            </div>


            <!-- Monthly -->
            <div v-if="plannedFixedExpenseForm.frequency === 'monthly'">

                <div
                    v-for="(pay, index) in plannedFixedExpenseForm.monthlyPays"
                    :key="index"
                >
                    <DayOfMonthDropdown
                        v-model="pay.dayDueForMonthly"
                    />

                </div>

            </div>


            <!-- Quarterly -->
            <div v-if="plannedFixedExpenseForm.frequency === 'quarterly'">

                sample due date:

                <VueDatePicker
                    v-model="plannedFixedExpenseForm.dueDateForBiweeklyQuarterlyYearlyOnetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    class="vueDatePicker"
                />

            </div>


            <!-- Yearly -->
            <div v-if="plannedFixedExpenseForm.frequency === 'yearly'">

                due date:

                <VueDatePicker
                    v-model="plannedFixedExpenseForm.dueDateForBiweeklyQuarterlyYearlyOnetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    class="vueDatePicker"
                />

            </div>


            <!-- One-time -->
            <div v-if="plannedFixedExpenseForm.frequency === 'onetime'">

                due date:

                <VueDatePicker
                    v-model="plannedFixedExpenseForm.dueDateForBiweeklyQuarterlyYearlyOnetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    class="vueDatePicker"                    
                />

            </div>

        </div>

        <!--From account-->
        <div class="upcomingFixedExpenseFromAccountCol">
            <input
                type="text"
                v-model="plannedFixedExpenseForm.accountTakenFrom" >
        </div>

        <!--Assigned to income-->
        <div class="upcomingFixedExpenseAssignedToIncomeCol">
            <select v-model="plannedFixedExpenseForm.plannedIncomeId">
                <option :value="null">-- Select --</option>

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
        <div class="upcomingFixedExpenseEffectiveDateCol">
            <span v-if="type === 'addNewFixedExpense'">
            first pay:
            </span>
            <VueDatePicker
                v-if="type === 'addNewFixedExpense'"
                v-model="plannedFixedExpenseForm.startDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }"/>

            <span v-if="type === 'addFutureChangeToCurrentFixedExpense'">
                takes effect:
                  <VueDatePicker
                      v-model="plannedFixedExpenseForm.effectiveDate"
                      model-type="yyyy-MM-dd"
                      :time-config="{ enableTimePicker: false }"
                      :formats="{ input: 'MMMM d, yyyy' }"
                      class="vueDatePicker"
                  />
            </span>
        </div>

        <!-- End date -->
        <div class="upcomingFixedExpenseEndDateCol">
            <VueDatePicker
                v-if="type === 'addNewFixedExpense'" 
                v-model="plannedFixedExpenseForm.endDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }" />
        </div>

        <!-- Tithe -->
        <div class="upcomingFixedExpenseTitheCol">
            <input
                v-if="type === 'addNewFixedExpense'"
                v-model="plannedFixedExpenseForm.tithe"
                type="checkbox"
            >
        </div>

        <!-- Notes -->
        <div class="upcomingFixedExpenseNotesCol">

            <input
                v-if="type === 'addNewFixedExpense'" 
                type="text"
                v-model="plannedFixedExpenseForm.notes"
                placeholder="notes"
            >

        </div>

        <div class="upcomingFixedExpenseAddCol">
            <button type="button" @click="submitFixedExpense">
              +
            </button>
        </div>

    </div>


    <!-- Monthly options row -->
    <div class="upcomingFixedExpenseRow monthlyOptionsRow">

        <div class="upcomingFixedExpenseDescriptionCol"></div>

        <div class="upcomingFixedExpenseFrequencyCol">

            <div v-if="plannedFixedExpenseForm.frequency === 'monthly'">

                <select
                    v-model="plannedFixedExpenseForm.numberPaysPerMonthForMonthly"
                >
                    <option value="1">1 pay per month</option>
                    <option value="2">2 pays per month</option>
                    <option value="3">3 pays per month</option>
                    <option value="4">4 pays per month</option>
                    <option value="5">5 pays per month</option>
                    <option value="6">6 pays per month</option>
                </select>

                <br>

                <span
                    v-if="
                        Number(plannedFixedExpenseForm.numberPaysPerMonthForMonthly) > 1
                    "
                >

                    <input
                        type="checkbox"
                        v-model="sameEachPayForMonthly"
                    >

                    same amount each pay

                </span>

            </div>

        </div>

        <div class="upcomingFixedExpenseAmountCol"></div>
        <div class="upcomingFixedExpenseDueCol"></div>
        <div class="upcomingFixedExpenseEffectiveDateCol"></div>
        <div class="upcomingFixedExpenseEndDateCol"></div>
        <div class="upcomingFixedExpenseTitheCol"></div>
        <div class="upcomingFixedExpenseNotesCol"></div>

    </div><!--upcomingFixedExpenseRow monthlyOptionsRow-->

</template>


<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import DayOfWeekDropdown from '../Components/DayOfWeekDropdown.vue'
import DayOfMonthDropdown from '../Components/DayOfMonthDropdown.vue'
import FrequencyDropdown from '../Components/FrequencyDropdown.vue'

const props = defineProps({
  type: String,
  plannedFixedExpense: Object,
  details: Array
})
const plannedIncomes = computed(() => usePage().props.plannedIncomes)

const descriptionFilled = ref('')

function descriptionBlurred() {
  if (plannedFixedExpenseForm.description) {
    descriptionFilled.value = true
  }
}

const sameEachPayForMonthly = ref(false)

const plannedFixedExpenseForm = useForm({
  type: props.type,
  plannedFixedExpenseId: props.type === 'addFutureChangeToCurrentFixedExpense' ? props.plannedFixedExpense.id : null,
  description: '',
  amount: '',
  accountTakenFrom: '',
  plannedIncomeId: null,
  startDate: '',
  endDate: '',
  frequency: props.type === 'addFutureChangeToCurrentFixedExpense' ? props.details[0]?.frequency : '',
  dueDateForBiweeklyQuarterlyYearlyOnetime: '',
  effectiveDate: '',
  dayOfWeekForWeeklyBiweekly: '',
  numberPaysPerMonthForMonthly: 1,
  tithe: true,
  notes: '',
  monthlyPays: [
    {
      amount: '',
      dayDueForMonthly: '',
    }
  ],
})


// make extra lines show up for monthly if number of pays > 1
watch(
  () => plannedFixedExpenseForm.numberPaysPerMonthForMonthly,
  (numberOfPays) => {

    const number = Number(numberOfPays)

    while (plannedFixedExpenseForm.monthlyPays.length < number) {
      plannedFixedExpenseForm.monthlyPays.push({
        amount: '',
        dayDueForMonthly: '',
        notes: '',
      })
    }

    while (plannedFixedExpenseForm.monthlyPays.length > number) {
      plannedFixedExpenseForm.monthlyPays.pop()
    }

    // If "same amount each pay" is checked,
    // give any new pays the same amount.
    if (sameEachPayForMonthly.value) {

      const amount = plannedFixedExpenseForm.monthlyPays.find(
        pay => pay.amount !== ''
      )?.amount

      if (amount !== undefined) {
        plannedFixedExpenseForm.monthlyPays.forEach(pay => {
          pay.amount = amount
        })
      }
    }
  }
)


// same amount for all pays
function sameAmountForAllPays(amount) {

  if (!sameEachPayForMonthly.value) {
    return
  }

  plannedFixedExpenseForm.monthlyPays.forEach(pay => {
    pay.amount = amount
  })
}


// if box is checked after entering an amount
watch(
  sameEachPayForMonthly,
  (checked) => {

    if (!checked) {
      return
    }

    const amount = plannedFixedExpenseForm.monthlyPays.find(
      pay => pay.amount !== ''
    )?.amount

    if (amount === undefined) {
      return
    }

    plannedFixedExpenseForm.monthlyPays.forEach(pay => {
      pay.amount = amount
    })
  }
)

function getDayOfWeek(date) {
  const dateObject = date instanceof Date
    ? date
    : new Date(date + 'T00:00:00')

  return dateObject.toLocaleDateString('en-US', {
    weekday: 'long'
  })
}


function submitFixedExpense() {
  plannedFixedExpenseForm.post('/plannedFixedExpense', {
    onSuccess: () => {
      plannedFixedExpenseForm.reset()
      sameEachPayForMonthly.value = false
      descriptionFilled.value = false
    }
  })
}
</script>
