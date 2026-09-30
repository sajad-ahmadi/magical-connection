<template>
  <div class="mgs-save-bar">
    <button
        class="mgs-btn mgs-btn-save"
        @click="$emit('save', $event)"
    >
      <i class="mg-save-icon mgs-sst-ic"></i>
      {{ __('Save Settings') }}
    </button>
  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineEmits(['save'])
</script>