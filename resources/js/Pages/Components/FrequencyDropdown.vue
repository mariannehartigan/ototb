<template>
  <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)"
  >
    <option value="">--select frequency--</option>
    <option value="weekly">weekly</option>
    <option value="biweekly">biweekly</option>
    <option value="monthly">monthly</option>
    <option value="quarterly">quarterly</option>
    <option value="yearly">yearly</option>
    <option value="onetime">one-time</option>
  </select>
</template>

<script setup>
defineProps({
  modelValue: String
})

defineEmits(['update:modelValue'])
</script>