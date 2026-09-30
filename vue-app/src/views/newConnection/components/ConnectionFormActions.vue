<template>
  <div class="mgs-form-actions">

    <button
        class="mgs-btn mgs-btn-save"
        :disabled="disabled"
        @click="$emit('save', $event)"
    >
      {{ __('Save Connection') }}
    </button>

    <button
        class="mgs-btn mgs-btn-cancel"
        :disabled="disabled"
        @click="$emit('cancel')"
    >
      {{ __('Cancel') }}
    </button>

  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  disabled: { type: Boolean, default: false }
})

defineEmits(['save', 'cancel'])
</script>