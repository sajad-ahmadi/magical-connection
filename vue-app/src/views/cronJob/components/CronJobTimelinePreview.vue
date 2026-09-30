<template>
  <div class="mgs-card mgs-preview-card">

    <div v-show="enabled && hasPendingTransfer">

      <h3 class="mgs-card-title">{{ __('Schedule Preview') }}</h3>

      <div class="mgs-alert-success">
        <span>{{ __('Cron job is active and properly scheduled.') }}</span>
      </div>

      <div class="mgs-timeline">

        <div class="mgs-timeline-item">
          <span class="mgs-timeline-label">{{ __('Next Run') }}</span>
          <span class="mgs-timeline-value">
            {{ nextRunDayLabel }}
            <small>{{ nextRun }}</small>
          </span>
          <i class="mg-clock-icon mgs-nr-ic"></i>
        </div>

        <div class="mgs-timeline-item">
          <span class="mgs-timeline-label">{{ __('Run Interval') }}</span>
          <span class="mgs-timeline-value">
            {{ __('Every') }} {{ interval }} {{ __('minutes') }}
          </span>
          <i class="mgs-refresh-icon mgs-er-ic"></i>
        </div>

        <div class="mgs-timeline-item mgs-timeline-current">
          <span class="mgs-timeline-label">{{ __('Last Run') }}</span>
          <span class="mgs-timeline-value">{{ lastRun }}</span>
          <span class="mgs-current-badge failed">
            {{ __('Successful') }}
          </span>
          <i class="mg-tick-icon mgs-lsc-ic"></i>
        </div>

        <div class="mgs-timeline-item mgs-timeline-list">
          <span class="mgs-timeline-label">{{ __('Next 5 Runs') }}</span>
          <div class="mgs-timeline-times">
            <span
                v-for="(time, index) in next5Runs"
                :key="index"
            >
              {{ time.runIn }}
            </span>
          </div>
        </div>

      </div>
    </div>

    <img
        v-show="!enabled || !hasPendingTransfer"
        :src="waitingImage"
        alt="cron waiting"
    />

    <div v-if="!enabled || !hasPendingTransfer" class="mgs-cron-waiting">
      <p>{{ __('Cron Job is not set') }}</p>
      <span class="mgs-cron-waiting-line"></span>
    </div>

  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  enabled:            { type: Boolean, default: false },
  hasPendingTransfer: { type: Boolean, default: false },
  interval:           { type: [String, Number], default: 0 },
  nextRun:            { type: String, default: '' },
  nextRunDayLabel:    { type: String, default: '' },
  lastRun:            { type: String, default: '' },
  next5Runs:          { type: Array, default: () => [] },
  waitingImage:       { type: String, default: '' }
})
</script>

<style scoped>
.mgs-cron-waiting {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  direction: rtl;
}

.mgs-cron-waiting p {
  font-size: 19px;
  font-weight: 600;
  color: #4c1d95;
  letter-spacing: 0.3px;
  margin: 0 0 22px 0;
  animation: pulse 3.2s ease-in-out infinite;
}

.mgs-cron-waiting-line {
  display: block;
  width: 140px;
  height: 1.5px;
  background: #c4b5fd;
  border-radius: 2px;
  opacity: 0.6;
  animation: pulse 3.2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%      { opacity: 0.55; }
}
</style>