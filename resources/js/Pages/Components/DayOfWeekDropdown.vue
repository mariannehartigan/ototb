<template>
  <select v-model="model">
    <option value="">--select day of week--</option>
    <option value="Monday">every Monday</option>
    <option value="Tuesday">every Tuesday</option>
    <option value="Wednesday">every Wednesday</option>
    <option value="Thursday">every Thursday</option>
    <option value="Friday">every Friday</option>
    <option value="Saturday">every Saturday</option>
    <option value="Sunday">every Sunday</option>
  </select>
</template>

<script setup>
const model = defineModel({
  default: ''
})
</script>

<style scoped>
option {
    text-align: center;
}
</style>