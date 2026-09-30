<template>
  <div class="mgs-setting-box">
    <div class="mgs-setting-row">
      <div class="mgs-setting-info">
        <h3>{{ __('Direct Upload To Remote Server') }}</h3>
        <p>
          {{ __('By enabling this option, files will be uploaded from the media library to the remote server.') }}
        </p>
      </div>

      <label class="mgs-switch">
        <input
            type="checkbox"
            :checked="autoTransferEnabled"
            @change="$emit('update:autoTransferEnabled', $event.target.checked)"
        />
        <span class="mgs-slider"></span>
      </label>
    </div>
  </div>

  <div class="mgs-setting-box">
    <h3 class="mgs-box-title">{{ __('Default Storage Server') }}</h3>

    <div class="mgs-setting-row">
      <div class="mgs-form-group">
        <label>{{ __('Select your Default Server') }}</label>

        <div class="mgs-select-wrapper">
          <select
              class="mgs-select"
              :value="connectionId"
              @change="$emit('update:connectionId', $event.target.value)"
          >
            <option
                v-for="server in connections"
                :key="server.id"
                :value="server.id"
            >
              {{ server.name }}
            </option>
          </select>
          <i class="mg-arrow-icon"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="mgs-setting-box">
    <h3 class="mgs-box-title">{{ __('Access Statuses') }}</h3>

    <div
        v-for="(role, index) in roles"
        :key="index"
        class="mgs-setting-row"
        :data-key="index"
    >
      <div class="mgs-setting-info">
        <span class="mgs-label-text">{{ role.name }}</span>
      </div>

      <label class="mgs-switch">
        <input
            type="checkbox"
            :checked="roleExistsCheck(index)"
            @change="$emit('roleChange', index, $event)"
        />
        <span class="mgs-slider"></span>
      </label>
    </div>

    <div class="mgs-setting-note">
      <p>
        {{ __('By enabling this option, only the site administrator will have permission to transfer files from the media library to the remote server.') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { __ } from '../../../config'

const props = defineProps({
  autoTransferEnabled: { type: Boolean, default: false },
  connectionId:        { type: [String, Number], default: null },
  connections:         { type: Array, default: () => [] },
  roles:               { type: Object, default: () => ({}) },

  roleExistsCheck: {
    type: Function,
    default: () => false
  }
})

defineEmits([
  'update:autoTransferEnabled',
  'update:connectionId',
  'roleChange'
])
</script>