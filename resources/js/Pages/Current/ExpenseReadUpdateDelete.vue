<template>
  <form @change="update">
    <input v-model="form.description" class="w-[28vw]" />
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  expense: Object
})

const form = useForm({
  description: props.expense.description,
})

const update = () => {
  form.put(`/expense/${props.expense.id}`)
}
</script>