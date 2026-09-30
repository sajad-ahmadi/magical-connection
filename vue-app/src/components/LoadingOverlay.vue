<template>
  <transition name="fade">
    <div v-if="isLoading" class="mgs-loading-overlay">
      <div class="mgs-loading-spinner"></div>
    </div>
  </transition>
</template>

<script setup>
import { useLoading } from '@/composables/useLoading'
const { isLoading } = useLoading()
</script>

<style scoped>
.mgs-loading-overlay {
  position: absolute; inset: 0;
  background: rgb(255, 255, 255);
  display: flex; align-items: center; justify-content: center;
  z-index: 10;
}
.mgs-loading-spinner {
  width: 32px; height: 32px;
  border: 3px solid #ddd; border-top-color: #333;
  border-radius: 50%; animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>