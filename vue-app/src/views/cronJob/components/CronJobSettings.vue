<template>
  <div class="mgs-card mgs-settings-card">

    <div class="mgs-settings-header">
      <h3 class="mgs-card-title">{{ __('Cron Job Settings') }}</h3>

      <div v-if="showCountdown" class="mgs-countdown-bar">
        <span>{{ __('Next Run In') }}</span>
        <div class="mgs-countdown-track">
          <div
              class="mgs-countdown-fill"
              :style="{ width: countdownPercent + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <div class="mgs-setting-row">
      <div class="mgs-setting-info">
        <label>{{ __('Cron Job') }}</label>
        <p>
          {{ __('When enabled, scanned files will be automatically transferred based on the settings below.') }}
        </p>
      </div>

      <label class="mgs-switch">
        <input
            type="checkbox"
            :checked="enabled"
            @change="$emit('update:enabled', $event.target.checked)"
        />
        <span class="mgs-slider"></span>
      </label>
    </div>

    <div class="mgs-setting-row">
      <div class="mgs-setting-info">
        <label>{{ __('Cron Job Run Interval') }}</label>
        <p>{{ __('How often the transfer process should run.') }}</p>
      </div>

      <div class="mgs-input-group">
        <select
            class="mgs-select"
            :value="interval"
            @change="$emit('update:interval', $event.target.value)"
        >
          <option
              v-for="(time, index) in timingList"
              :key="time.id"
              :value="index"
          >
            {{ toMinutes(time.interval) }}
          </option>
        </select>
        <span class="mgs-input-suffix">{{ __('Minute') }}</span>
      </div>
    </div>

    <div class="mgs-setting-row">
      <div class="mgs-setting-info">
        <label>{{ __('Files Per Run') }}</label>
        <p>{{ __('Number of files to transfer per run.') }}</p>
      </div>

      <div class="mgs-input-group">
        <input
            class="mgs-input"
            type="number"
            :value="filesPerRun"
            @input="onNumberInput('update:filesPerRun', $event)"
            @keydown="preventNonNumeric"
        />
        <span class="mgs-input-suffix">{{ __('File') }}</span>
      </div>
    </div>

    <div class="mgs-setting-row">
      <div class="mgs-setting-info">
        <label>{{ __('Retry for Failed Files') }}</label>
        <p>{{ __('Maximum allowed time per run.') }}</p>
      </div>

      <div class="mgs-input-group">
        <input
            class="mgs-input"
            type="number"
            min="1"
            max="10"
            :value="maxRetries"
            @input="onNumberInput('update:maxRetries', $event)"
            @keydown="preventNonNumeric"
        />
        <span class="mgs-input-suffix">{{ __('Repeat') }}</span>
      </div>
    </div>

    <div class="mgs-settings-actions">
      <button
          class="mgs-btn mgs-btn-primary mgs-add-new-cronjob-btn"
          @click="$emit('save', $event)"
      >
        {{ __('Save Settings') }}
      </button>
    </div>

  </div>
</template>

<script setup>
import { __ } from '../../../config'
import { useTools } from '@/composables/useTools.js'

const { preventNonNumeric, toMinutes } = useTools()

const props = defineProps({
  enabled:       { type: Boolean, default: false },
  interval:      { type: [String, Number], default: null },
  filesPerRun:   { type: [String, Number], default: null },
  maxRetries:    { type: [String, Number], default: null },
  timingList:    { type: Object, default: () => ({}) },
  showCountdown: { type: Boolean, default: false },
  countdownPercent: { type: Number, default: 0 }
})

const emit = defineEmits([
  'update:enabled',
  'update:interval',
  'update:filesPerRun',
  'update:maxRetries',
  'save'
])

function onNumberInput(eventName, event) {
  const value = event.target.value.replace(/[^0-9]/g, '')
  event.target.value = value
  emit(eventName, value)
}
</script>