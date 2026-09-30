<template>
  <div class="mgs-tabs-list">

    <button
        v-for="tab in tabs"
        :key="tab.key"
        class="mgs-tab-btn"
        :class="{ 'mgs-tab-active': activeTab === tab.key }"
        @click="$emit('change', tab.key)"
    >
      <i :class="tab.icon + ' mgs-tb-ic'"></i>
      <span>{{ tab.title }}</span>
    </button>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { __ } from '../../../config'

defineProps({
  activeTab: {
    type: String,
    required: true
  }
})

defineEmits(['change'])

const tabs = computed(() => [
  {
    key: 'media',
    icon: 'mg-image-icon',
    title: __('WP Media Config')
  }
])
</script>