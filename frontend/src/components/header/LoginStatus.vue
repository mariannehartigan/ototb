<template>
    <div class="loginStatus">
        <p>
            Welcome, {{userName}} &nbsp;
        
      <button @click="handleAction">Logout</button>
      </p>
    </div>
  </template>
  
  <script setup lang="ts">  
  import { storeToRefs } from 'pinia';
  import { useUserStore } from 'src/stores/user-store';
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';

  const router = useRouter();
  const { user } = storeToRefs(useUserStore());
  const userName = computed(() => user.value?.name);
  
  const handleAction = async () => {
    if (userName.value !== undefined) {
      await useUserStore().logout();
      await router.push('/guest/login');
    } else {
      await router.push('/guest/login');
    }
  };

  </script>
  
  <style>
  
  </style>
  