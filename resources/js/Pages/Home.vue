<template>
  <Head title="Home" />

  <div v-if="!user">
    <NotLoggedIn />
  </div>

  <div v-if="user">
    <div class="header">
      <Header />
    </div>
    
    <div class="column1 tabs">
      <button v-if="user"
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab"
        :class="{ active: activeTab.id === tab.id }"
      >{{ tab.label }}
      </button>
    </div>

    <div class="column2">
      <keep-alive>
        <component v-if="user" :is="activeTab.component" />
      </keep-alive>
    </div>

    <div class="column3"></div>
    
    <div class="footer"></div>
  </div>
</template>

<script setup>
import { computed, markRaw, ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import Header from '../Pages/Header.vue'
import NotLoggedIn from '../Pages/NotLoggedIn.vue'
import Upcoming from './Upcoming/Upcoming.vue'
import Current from './Current.vue'
import History from './History.vue'
import Assets from './Assets.vue'
import Debts from './Debts.vue'

const user = computed(() => usePage().props.user)

const tabs = [
  {
    id: 'upcoming',
    label: 'Upcoming',
    component: markRaw(Upcoming)
  },
  {
    id: 'current',
    label: 'Current',
    component: markRaw(Current)
  },
  {
    id: 'history',
    label: 'History',
    component: markRaw(History)
  },
  {
    id: 'assets',
    label: 'Assets',
    component: markRaw(Assets)
  },
  {
    id: 'debts',
    label: 'Debts',
    component: markRaw(Debts)
  },
]

const activeTab = ref(tabs[0])
</script>