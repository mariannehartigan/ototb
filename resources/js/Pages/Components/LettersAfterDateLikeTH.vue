<template>
  {{ suffix }}
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  number: Number
})

const suffix = computed(() => {

  if (props.number >= 11 && props.number <= 13) {
    return 'th'
  }

  switch (props.number % 10) {
    case 1:
      return 'st'
    case 2:
      return 'nd'
    case 3:
      return 'rd'
    default:
      return 'th'
  }

})
</script>