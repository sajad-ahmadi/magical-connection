<template>
  <div class="mgs-form-group">
    <label>{{ __('Server Address (IP)') }}</label>
    <div class="mgs-input-group">
      <i class="fa-solid fa-server"></i>
      <input
          class="mgs-input en-only"
          type="text"
          placeholder="192.168.2.1"
          :disabled="disabled"
          :value="host"
          @input="$emit('update:host', $event.target.value)"
      />
    </div>
  </div>

  <div class="mgs-form-group">
    <label>{{ __('Port') }}</label>
    <div class="mgs-input-group">
      <i class="fa-solid fa-plug"></i>
      <input
          class="mgs-input en-only"
          type="number"
          placeholder="21"
          :disabled="disabled"
          :value="port"
          @input="onPortInput"
          @keydown="preventNonNumeric"
      />
    </div>
  </div>

  <div class="mgs-form-group">
    <label>{{ __('Username') }}</label>
    <div class="mgs-input-group">
      <i class="fa-solid fa-user"></i>
      <input
          class="mgs-input en-only"
          type="text"
          placeholder="my_username"
          :disabled="disabled"
          :value="username"
          @input="$emit('update:username', $event.target.value)"
      />
    </div>
  </div>

  <div class="mgs-form-group">
    <label>{{ __('Password') }}</label>
    <div class="mgs-input-group">
      <i class="fa-solid fa-lock"></i>
      <input
          class="mgs-input en-only"
          type="text"
          placeholder="my_password"
          :disabled="disabled"
          :value="password"
          @input="$emit('update:password', $event.target.value)"
      />
    </div>
  </div>

  <div class="mgs-form-group">
    <label>{{ __('PATH') }}</label>
    <div class="mgs-input-group">
      <i class="fa-solid fa-folder"></i>
      <input
          class="mgs-input en-only"
          type="text"
          placeholder="public_html/"
          :disabled="disabled"
          :value="basePath"
          @input="$emit('update:basePath', $event.target.value)"
      />
    </div>
  </div>

  <div class="mgs-form-group">
    <label>{{ __('Domain') }}</label>
    <div class="mgs-input-group">
      <i class="fa-solid fa-server"></i>
      <input
          class="mgs-input en-only"
          type="text"
          placeholder="https://example.com"
          :disabled="disabled"
          :value="domain"
          @input="$emit('update:domain', $event.target.value)"
      />
    </div>
  </div>

  <div class="mgs-switch-row">
    <span class="mgs-switch-label">{{ __('Passive Mode') }}</span>
    <label class="mgs-switch">
      <input
          type="checkbox"
          :disabled="disabled"
          :checked="passiveMode"
          @change="$emit('update:passiveMode', $event.target.checked ? 1 : 0)"
      />
      <span class="mgs-slider"></span>
    </label>
  </div>
</template>

<script setup>
import { __ } from '../../../config'
import { useTools } from '@/composables/useTools.js'

const { preventNonNumeric } = useTools()

defineProps({
  host:        { type: String, default: '' },
  port:        { type: [String, Number], default: 21 },
  username:    { type: String, default: '' },
  password:    { type: String, default: '' },
  basePath:    { type: String, default: '/' },
  domain:      { type: String, default: '' },
  passiveMode: { type: [Number, Boolean], default: 1 },
  disabled:    { type: Boolean, default: false }
})

const emit = defineEmits([
  'update:host',
  'update:port',
  'update:username',
  'update:password',
  'update:basePath',
  'update:domain',
  'update:passiveMode'
])

function onPortInput(event) {
  const clean = event.target.value.replace(/[^0-9]/g, '')
  event.target.value = clean
  emit('update:port', clean === '' ? '' : Number(clean))
}
</script>