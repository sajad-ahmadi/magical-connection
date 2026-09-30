<template>
  <div class="mgs-stat-card">
    <div class="mgs-stat-info">
      <span class="mgs-stat-label">{{ __('Files Per Run') }}</span>
      <strong class="mgs-stat-value">
        {{ filesPerRun }}
        <small>{{ __('File') }}</small>
      </strong>
      <span class="mgs-stat-desc">{{ __('Per Run') }}</span>
    </div>
    <div class="mgs-stat-icon mgs-icon-purple">
      <i class="mgs-sd-ic mg-file-icon"></i>
    </div>
  </div>

  <div class="mgs-stat-card">
    <div class="mgs-stat-info">
      <span class="mgs-stat-label">{{ __('Run Interval') }}</span>
      <strong class="mgs-stat-value">
        {{ interval }}
        <small>{{ __('Minute') }}</small>
      </strong>
      <span class="mgs-stat-desc">{{ __('Repeat Interval') }}</span>
    </div>
    <div class="mgs-stat-icon mgs-icon-blue">
      <i class="mgs-refresh-icon mgs-sd-ic"></i>
    </div>
  </div>

  <div class="mgs-stat-card">
    <div class="mgs-stat-info">
      <span class="mgs-stat-label">{{ __('Next Run') }}</span>
      <strong class="mgs-stat-value">{{ nextRun }}</strong>
      <span class="mgs-stat-desc">{{ nextRunDayLabel }}</span>
    </div>
    <div class="mgs-stat-icon mgs-icon-green">
      <i class="mgs-sd-ic mg-clock-icon"></i>
    </div>
  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  filesPerRun:     { type: [String, Number], default: 0 },
  interval:        { type: [String, Number], default: 0 },
  nextRun:         { type: String, default: '' },
  nextRunDayLabel: { type: String, default: '' }
})
</script>