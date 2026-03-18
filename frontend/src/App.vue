<template>
  <div id="app" :key="0">
    <router-view :key="$route.path" />
  </div>
</template>

<script setup>
import { useRoute } from "vue-router"
const $route = useRoute()
</script>
