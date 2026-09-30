<template>
  <div class="mg-faq">

    <header class="mgs-help-header mgs-animate-in">
      <div class="mgs-help-icon">
        <i class="mg-sliders-icon mgs-hi-ic"></i>
        <span class="mgs-help-dot"></span>
      </div>
      <h1 class="mgs-help-title">
        {{ __('Frequently Questions') }}
      </h1>
    </header>

    <div class="mgs-accordion-list">
      <FaqItem
          v-for="item in faqItems"
          :key="item.id"
          :item="item"
          :is-open="openItemId === item.id"
          @toggle="handleToggle"
      />
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { __ } from '../../config'
import { faqItems } from './data'
import FaqItem from './components/FaqItem.vue'
import { useLoading } from '@/composables/useLoading'

const { start: startLoading, stop: stopLoading } = useLoading()

const openItemId = ref(null)

const route = useRoute()

function handleToggle(itemId) {
  openItemId.value = openItemId.value === itemId ? null : itemId
}

onMounted(() => {
  startLoading()
  stopLoading()

  const hash = (route.hash || window.location.hash || '').replace(/^#/, '')
  if (hash) {
    const found = faqItems.find((item) => item.id === hash)
    if (found) {
      openItemId.value = found.id
    }
  }

  if (route.query.open) {
    const found = faqItems.find((item) => item.id === route.query.open)
    if (found) openItemId.value = found.id
  }
})
</script>