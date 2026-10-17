<script setup>
import { ref, nextTick, onMounted } from 'vue'

const today = new Date()

const selectedDate = ref(null)

const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const months = []

for (let i = -3; i <= 11; i++) {

    const date = new Date(
        today.getFullYear(),
        today.getMonth() + i,
        1
    )

    const year = date.getFullYear()
    const month = date.getMonth()

    const monthName = date.toLocaleString('default', {
        month: 'long'
    })

    const numberOfDays = new Date(
        year,
        month + 1,
        0
    ).getDate()

    const firstDay = new Date(
        year,
        month,
        1
    ).getDay()

    const days = Array.from(
        { length: numberOfDays },
        (_, i) => i + 1
    )

    months.push({
        year,
        month,
        monthName,
        firstDay,
        days
    })
}


function selectDate(month, day) {

    selectedDate.value = new Date(
        month.year,
        month.month,
        day
    )

    console.log(selectedDate.value)
}


function isToday(month, day) {

    return (
        month.year === today.getFullYear() &&
        month.month === today.getMonth() &&
        day === today.getDate()
    )
}


function isSelected(month, day) {

    if (!selectedDate.value) {
        return false
    }

    return (
        month.year === selectedDate.value.getFullYear() &&
        month.month === selectedDate.value.getMonth() &&
        day === selectedDate.value.getDate()
    )
}


const calendarContainer = ref(null)

onMounted(async () => {

    await nextTick()

    const container = calendarContainer.value
    const currentMonth = container.children[3]

    const containerTop =
        container.getBoundingClientRect().top

    const monthTop =
        currentMonth.getBoundingClientRect().top

    container.scrollTop += monthTop - containerTop
})

</script>


<template>

    <div
        ref="calendarContainer"
        class="calendar-container"
    >

        <div
            v-for="month in months"
            :key="`${month.year}-${month.month}`"
            class="month-calendar"
        >

            <!-- Month -->
            <div class="month">
                {{ month.monthName }} {{ month.year }}
            </div>


            <!-- Day abbreviations -->
            <div
                v-for="day in daysOfWeek"
                :key="day"
                class="weekday"
            >
                {{ day }}
            </div>


            <!-- Empty spaces before the 1st -->
            <div
                v-for="n in month.firstDay"
                :key="'empty-' + n"
            ></div>


            <!-- Dates -->
            <div
                v-for="day in month.days"
                :key="day"
                class="day"
                :class="{
                    today: isToday(month, day),
                    selected: isSelected(month, day)
                }"
                @click="selectDate(month, day)"
            >
                {{ day }}
            </div>

        </div>

    </div>

</template>


<style scoped>

.calendar-container { 
    width: 330px; 
    height: 310px; 
    overflow-y: auto; 
    overflow-x: hidden; 
    padding: 10px; 
    box-sizing: border-box; 
    border: 1px solid #ccc; 
    border-radius: 6px; 
    background: white; 
} 

/* Each month */ 
.month-calendar { 
    display: grid; 
    grid-template-columns: repeat(7, 40px); 
    gap: 5px; 
    margin-bottom: 30px; 
    width: 310px; 
}


/* Month name */

.month {
    grid-column: 1 / -1;

    text-align: center;

    font-size: 18px;
    font-weight: bold;

    margin-bottom: 10px;
}


/* Weekdays */

.weekday {
    width: 40px;

    text-align: center;

    font-size: 12px;
    font-weight: bold;
}


/* Dates */

.day {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;

    border-radius: 50%;

    box-sizing: border-box;
}


.day:hover {
    background: #eee;
}


/* Today */

.today {
    border: 2px solid #999;
}


/* Selected date */

.selected {
    background: #ccc;
}

</style>