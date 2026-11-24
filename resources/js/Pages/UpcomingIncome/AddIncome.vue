<template>

    <div class="upcomingIncomeRow">

        <!-- Description -->
        <div class="upcomingIncomeDescriptionCol">
            <input
                v-if="type === 'addNewIncome'"
                type="text"
                v-model="plannedIncomeForm.description"
                @blur="descriptionBlurred"
                placeholder="Description">
            <input v-else disabled placeholder="add future change">
        </div>

        <!-- Frequency -->
        <div class="upcomingIncomeFrequencyCol">
            <FrequencyDropdown v-model="plannedIncomeForm.frequency" />
        </div>

        <!-- Amount -->
        <div class="upcomingIncomeAmountCol">

            <!-- Monthly -->
            <div v-if="plannedIncomeForm.frequency === 'monthly'">
                <div
                    v-for="(pay, index) in plannedIncomeForm.monthlyPays"
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
                    v-model="plannedIncomeForm.amount"
                    placeholder="amount">
            </div>

        </div>

        <!-- Deposited -->
        <div class="upcomingIncomeDepositedCol">

            <!-- Weekly -->
            <div v-if="plannedIncomeForm.frequency === 'weekly'">

                <DayOfWeekDropdown
                    v-model="plannedIncomeForm.dayOfWeekForWeeklyBiweekly"
                />

            </div>


            <!-- Biweekly -->
            <div v-if="plannedIncomeForm.frequency === 'biweekly'">

                <span v-if="type === 'addNewIncome'">
                  sample deposit date:
                  <VueDatePicker
                      v-model="plannedIncomeForm.depositDateForBiweeklyQuarterlyYearlyOnetime"
                      model-type="yyyy-MM-dd"
                      :time-config="{ enableTimePicker: false }"
                      :formats="{ input: 'MMMM d, yyyy' }"
                      class="vueDatePicker"
                  />
                </span>                

                <span v-if="plannedIncomeForm.depositDateForBiweeklyQuarterlyYearlyOnetime">
                    every other
                    {{ getDayOfWeek(plannedIncomeForm.depositDateForBiweeklyQuarterlyYearlyOnetime) }}
                </span>

            </div>


            <!-- Monthly -->
            <div v-if="plannedIncomeForm.frequency === 'monthly'">

                <div
                    v-for="(pay, index) in plannedIncomeForm.monthlyPays"
                    :key="index"
                >
                    <DayOfMonthDropdown
                        v-model="pay.dayDepositedForMonthly"
                    />

                </div>

            </div>


            <!-- Quarterly -->
            <div v-if="plannedIncomeForm.frequency === 'quarterly'">

                sample deposit date:

                <VueDatePicker
                    v-model="plannedIncomeForm.depositDateForBiweeklyQuarterlyYearlyOnetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    class="vueDatePicker"
                />

            </div>


            <!-- Yearly -->
            <div v-if="plannedIncomeForm.frequency === 'yearly'">

                deposit date:

                <VueDatePicker
                    v-model="plannedIncomeForm.depositDateForBiweeklyQuarterlyYearlyOnetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    class="vueDatePicker"
                />

            </div>


            <!-- One-time -->
            <div v-if="plannedIncomeForm.frequency === 'onetime'">

                deposit date:

                <VueDatePicker
                    v-model="plannedIncomeForm.depositDateForBiweeklyQuarterlyYearlyOnetime"
                    model-type="yyyy-MM-dd"
                    :time-config="{ enableTimePicker: false }"
                    :formats="{ input: 'MMMM d, yyyy' }"
                    class="vueDatePicker"                    
                />

            </div>


        </div>


        <!-- Start date -->
        <div  class="upcomingIncomeEffectiveDateCol">
            <span v-if="type === 'addNewIncome'">
            first pay:
            </span>
            <VueDatePicker
                v-if="type === 'addNewIncome'"
                v-model="plannedIncomeForm.startDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }"/>

            <span v-if="type === 'addFutureChangeToCurrentIncome'">
                takes effect:
                  <VueDatePicker
                      v-model="plannedIncomeForm.effectiveDate"
                      model-type="yyyy-MM-dd"
                      :time-config="{ enableTimePicker: false }"
                      :formats="{ input: 'MMMM d, yyyy' }"
                      class="vueDatePicker"
                  />
            </span>
        </div>

        <!-- End date -->
        <div class="upcomingIncomeEndDateCol">
            <VueDatePicker
                v-if="type === 'addNewIncome'" 
                v-model="plannedIncomeForm.endDate"
                model-type="yyyy-MM-dd"
                :time-config="{ enableTimePicker: false }"
                :formats="{ input: 'MMMM d, yyyy' }" />
        </div>

        <!-- Tithe -->
        <div class="upcomingIncomeTitheCol">
            <input
                v-if="type === 'addNewIncome'"
                v-model="plannedIncomeForm.tithe"
                type="checkbox"
            >
        </div>

        <!-- Notes -->
        <div class="upcomingIncomeNotesCol">

            <input
                v-if="type === 'addNewIncome'" 
                type="text"
                v-model="plannedIncomeForm.notes"
                placeholder="notes"
            >

        </div>

        <div class="upcomingIncomeAddCol">
            <button type="button" @click="submitIncome">
              +
            </button>
        </div>

    </div>


    <!-- Monthly options row -->
    <div class="upcomingIncomeRow monthlyOptionsRow">

        <div class="upcomingIncomeDescriptionCol"></div>

        <div class="upcomingIncomeFrequencyCol">

            <div v-if="plannedIncomeForm.frequency === 'monthly'">

                <select
                    v-model="plannedIncomeForm.numberPaysPerMonthForMonthly"
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
                        Number(plannedIncomeForm.numberPaysPerMonthForMonthly) > 1
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

        <div class="upcomingIncomeAmountCol"></div>
        <div class="upcomingIncomeDepositedCol"></div>
        <div class="upcomingIncomeEffectiveDateCol"></div>
        <div class="upcomingIncomeEndDateCol"></div>
        <div class="upcomingIncomeTitheCol"></div>
        <div class="upcomingIncomeNotesCol"></div>


    </div><!--upcomingIncomeRow monthlyOptionsRow-->



</template>


<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DayOfWeekDropdown from '../Components/DayOfWeekDropdown.vue'
import DayOfMonthDropdown from '../Components/DayOfMonthDropdown.vue'
import FrequencyDropdown from '../Components/FrequencyDropdown.vue'

const props = defineProps({
  type: String,
  plannedIncome: Object,
  details: Array
})

const descriptionFilled = ref('')

function descriptionBlurred() {
  if (plannedIncomeForm.description) {
    descriptionFilled.value = true
  }
}

const sameEachPayForMonthly = ref(false)

const plannedIncomeForm = useForm({
  type: props.type,
  plannedIncomeId: props.type === 'addFutureChangeToCurrentIncome' ? props.plannedIncome.id : null,
  description: '',
  amount: '',
  startDate: '',
  // startDate: props.type === 'addNewIncome' ? new Date().toISOString().split('T')[0] : '',
  endDate: '',
  frequency: props.type === 'addFutureChangeToCurrentIncome' ? props.details[0]?.frequency : '',
  depositDateForBiweeklyQuarterlyYearlyOnetime: '',
  effectiveDate: '',
  // effectiveDate: new Date().toISOString().split('T')[0],
  dayOfWeekForWeeklyBiweekly: '',
  numberPaysPerMonthForMonthly: 1,
  tithe: true,
  notes: '',
  monthlyPays: [
    {
      amount: '',
      dayDepositedForMonthly: '',
      notes: '',
    }
  ],
})


// make extra lines show up for monthly if number of pays > 1
watch(
  () => plannedIncomeForm.numberPaysPerMonthForMonthly,
  (numberOfPays) => {

    const number = Number(numberOfPays)

    while (plannedIncomeForm.monthlyPays.length < number) {
      plannedIncomeForm.monthlyPays.push({
        amount: '',
        dayDepositedForMonthly: '',
        notes: '',
      })
    }

    while (plannedIncomeForm.monthlyPays.length > number) {
      plannedIncomeForm.monthlyPays.pop()
    }

    // If "same amount each pay" is checked,
    // give any new pays the same amount.
    if (sameEachPayForMonthly.value) {

      const amount = plannedIncomeForm.monthlyPays.find(
        pay => pay.amount !== ''
      )?.amount

      if (amount !== undefined) {
        plannedIncomeForm.monthlyPays.forEach(pay => {
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

  plannedIncomeForm.monthlyPays.forEach(pay => {
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

    const amount = plannedIncomeForm.monthlyPays.find(
      pay => pay.amount !== ''
    )?.amount

    if (amount === undefined) {
      return
    }

    plannedIncomeForm.monthlyPays.forEach(pay => {
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


function submitIncome() {
  plannedIncomeForm.post('/plannedincome', {
    onSuccess: () => {
      plannedIncomeForm.reset()
      sameEachPayForMonthly.value = false
      descriptionFilled.value = false
    }
  })
}
</script>
