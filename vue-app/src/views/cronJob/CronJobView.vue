<template>
  <div class="mgs-cron-job">

    <header class="mgs-page-header">

      <div class="mgs-header-right">
        <i class="mg-cronjob-icon mgs-header-ic"></i>
        <div class="mgs-title-group">
          <h1 class="mgs-title">{{ __('cron job') }}</h1>
          <p class="mgs-subtitle">
            {{ __('Automated File Transfer Management') }}
          </p>
        </div>
      </div>

      <div class="mgs-header-left">

        <button
            class="mgs-btn mgs-btn-primary mgs-run-cronjob-now"
            @click="handleRunManually"
        >
          <i class="mg-play-icon mgs-rcn-ic"></i>
          {{ __('Run Manually Now') }}
        </button>

        <div class="mgs-status-badge">
          <span class="mgs-status-label">{{ __('Cron Job') }}</span>
          <span v-if="cronJobData.enabled" class="mgs-status-active">
            {{ __('Active') }}
          </span>
          <span v-else class="mgs-status-de-active">
            {{ __('Inactive') }}
          </span>
        </div>

      </div>
    </header>

    <div v-if="cronJobData.enabled && cronJobData.hasPendingTransfer"
        class="mgs-stats-grid">
      <CronJobStats
          :files-per-run="cronJobData.files_per_run"
          :interval="cronStates.interval"
          :next-run="cronStates.nextRun"
          :next-run-day-label="cronStates.nextRunDayLabel"
      />
    </div>

    <div class="mgs-two-columns">

      <CronJobSettings
          v-model:enabled="cronFormData.enabled"
          v-model:interval="cronFormData.interval"
          v-model:files-per-run="cronFormData.files_per_run"
          v-model:max-retries="cronFormData.max_retries"
          :timing-list="timingList"
          :show-countdown="cronJobData.enabled && cronJobData.hasPendingTransfer"
          :countdown-percent="cronStates.percent"
          @save="handleSaveSettings"
      />

      <CronJobTimelinePreview
          :enabled="cronJobData.enabled"
          :has-pending-transfer="cronJobData.hasPendingTransfer"
          :interval="cronStates.interval"
          :next-run="cronStates.nextRun"
          :next-run-day-label="cronStates.nextRunDayLabel"
          :last-run="cronStates.lastRun"
          :next-5-runs="next5Runs"
          :waiting-image="waitingImage"
      />

    </div>

    <div class="mgs-card mgs-how-it-works">
      <h3 class="mgs-card-title">{{ __('How It Works') }}</h3>
      <CronJobHowItWorks />
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { __ , MagicalConnection } from '../../config'
import { useCronJob } from '@/composables/useCronJob.js'
import { useLoading } from '@/composables/useLoading'

import CronJobSettings from './components/CronJobSettings.vue'
import CronJobStats from './components/CronJobStats.vue'
import CronJobTimelinePreview from './components/CronJobTimelinePreview.vue'
import CronJobHowItWorks from './components/CronJobHowItWorks.vue'


const {
  cronJobData,
  cronStates,
  cronFormData,
  timingList,
  next5Runs,
  getCronJobData,
  saveSettingsBtn,
  runCronManuallyBtn
} = useCronJob()

const { start: startLoading, stop: stopLoading } = useLoading()

const waitingImage =
    (MagicalConnection?.url ?? '') + 'assets/icons/cron-waiting.svg'

function handleSaveSettings(event) {
  saveSettingsBtn(event)
}

function handleRunManually(event) {
  runCronManuallyBtn(event)
}

onMounted(async () => {
  startLoading()

  await getCronJobData()

  stopLoading()
})
</script>