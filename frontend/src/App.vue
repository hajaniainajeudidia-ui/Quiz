<template>
  <div>
    <!-- Pages publiques -->
    <router-view v-if="['Login', 'Register'].includes($route.name)" />

    <!-- Pages protégées -->
    <AppLayout v-else>
      <router-view />
    </AppLayout>
  </div>
</template>

<script setup>
import AppLayout from './components/Layout/AppLayout.vue'
import { useRoute } from 'vue-router'

const route = useRoute()
</script>