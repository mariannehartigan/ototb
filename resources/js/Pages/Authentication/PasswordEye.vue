<template>
  <button type="button" class="toggle-password" @click="$emit('toggle')">
    <!-- Eye Open -->
    <svg
      v-if="show"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      class="icon"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
      />
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
      />
    </svg>

    <!-- Eye Closed -->
    <svg
      v-else
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      class="icon"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.374-4.568m3.181-2.475A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.98 9.98 0 01-4.043 5.166M9.878 9.878A3 3 0 0114.12 14.12M3 3l18 18"
      />
    </svg>
  </button>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
})
</script>