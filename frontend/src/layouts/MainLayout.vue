<template>

  <div class="container">
    <PageHeader class="header"/>
    <div class="div1">
      <p>Welcome, {{userName}}</p>
    </div>
    <div class="div2">
      <button @click="handleAction">Logout</button>
    </div>
    <PageFooter />
  </div>
  
</template>

<script setup lang="ts">
  import { storeToRefs } from 'pinia';
  import { useUserStore } from 'src/stores/user-store';
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  import PageHeader from '../components/header/PageHeader.vue'
  import PageFooter from '../components/PageFooter.vue'

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

<style scoped>

</style>