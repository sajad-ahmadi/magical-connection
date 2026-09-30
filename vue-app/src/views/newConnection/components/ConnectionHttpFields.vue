<template>
  <div class="mgs-form-group">
    <label>{{ __('api key') }}</label>
    <div class="mgs-input-group disabled">
      <i class="fa-solid fa-link"></i>
      <input
          class="mgs-input en-only disabled"
          type="text"
          :value="token"
          disabled
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

  <div class="api-setup-card">

    <div class="api-card-header">
      <div class="api-header-text">
        <h3>{{ __('API Setup') }}</h3>
        <p>
          {{ __('To use the HTTP connection, first install the API on the server.') }}
        </p>
      </div>
      <button type="button" class="api-icon-btn" :title="__('Download')">
        <i class="mg-download-icon mg-aib-ic"></i>
      </button>
    </div>

    <div class="api-card-divider"></div>

    <div class="api-card-footer">
      <a
          class="api-tutorial-link"
          href="#"
          @click.prevent="$emit('openTutorial', $event)"
      >
        <i class="mg-arrow-icon mgs-atl-ic"></i>
        {{ __('View Magical Connection Tutorial') }}
      </a>

      <a
          class="api-download-btn"
          :href="downloadUrl"
          download
      >
        <i class="mg-download-icon mgs-adb-ic"></i>
        {{ __('Download API') }}
      </a>
    </div>
  </div>

  <div class="api-notice">
    <i class="mg-warning-icon mgs-an-ic"></i>
    <span>
      <strong>{{ __('Note:') }}</strong>
      {{ __('Please download the API file and install it on the destination server before saving the connection. Otherwise, the connection will not be established.') }}
    </span>
  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  token:       { type: String, default: '' },
  domain:      { type: String, default: '' },
  downloadUrl: { type: String, default: '' },
  disabled:    { type: Boolean, default: false }
})

defineEmits(['update:domain', 'openTutorial'])
</script>