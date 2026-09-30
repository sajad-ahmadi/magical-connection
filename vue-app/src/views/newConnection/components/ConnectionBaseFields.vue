<template>
  <div class="mgs-form-group">
    <label>{{ __('Connection Protocol') }}</label>

    <div class="mgs-select-wrapper">
      <select
          id="mgs-protocol-select"
          class="mgs-select"
          :disabled="disabled"
          :value="protocol"
          @change="$emit('update:protocol', $event.target.value)"
      >
        <option value="FTP">FTP</option>
        <option value="HTTP">HTTP</option>
      </select>
      <i class="mg-arrow-icon"></i>
    </div>
  </div>

  <div class="mgs-form-group">
    <label>{{ __('Connection Name') }}</label>

    <div class="mgs-input-group">
      <input class="mgs-input"
          type="text"
          placeholder="my connection"
          :disabled="disabled"
          :value="name"
          @input="$emit('update:name', $event.target.value)"
      />
    </div>
  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  protocol: { type: String, required: true },
  name:     { type: String, default: '' },
  disabled: { type: Boolean, default: false }
})

defineEmits(['update:protocol', 'update:name'])
</script>