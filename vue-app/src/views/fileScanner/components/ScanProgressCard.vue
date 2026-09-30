<template>
  <div class="mgs-card mgs-scan-progress-card">

    <div class="mgs-progress-header">
      <div class="mgs-progress-title">
        <h2 class="mgs-card-title">{{ __('Scan Default Media Path') }}</h2>
      </div>

      <div v-if="scanning" class="mgs-scanning-alert">
        <i class="mgs-refresh-icon mgs-sa-ic"></i>
        {{ __('Scanning...') }}
      </div>
    </div>

    <div class="mgs-progress-body">

      <div class="mgs-progress-stats">

        <div class="mgs-stat-item">
          <i class="mg-clock-icon mgs-si-ic"></i>
          {{ __('Elapsed Time:') }}
          <span>{{ scanningData.elapsedTime }}</span>
        </div>

        <div class="mgs-stat-item">
          <i class="mg-file-icon mgs-si-ic"></i>
          {{ __('Scanned Files:') }}
          <span>{{ scanningData.scannedFiles }}</span>
        </div>

        <div class="mgs-stat-item">
          <i class="mg-hard-disc-icon mgs-si-ic"></i>
          {{ __('Scanned Volume:') }}
          <span>{{ useTools().formatBytes(scanningData.scannedVolume) }}</span>
        </div>

        <div class="mgs-stat-item">
          <i class="mg-folder-icon mgs-si-ic"></i>
          {{ __('Files Found:') }}
          <span>{{ scanningData.filesFound }}</span>
        </div>

      </div>

      <div class="mgs-progress-main">

        <div class="mgs-percentage">{{ scanningData.scanPercent }}</div>

        <div class="mgs-progress-bar-container">
          <div
              class="mgs-progress-bar"
              :style="{ '--mgs-scan-progress-width': scanningData.scanPercent }"
          ></div>
        </div>

        <div class="mgs-progress-path">
          {{ scanningData.currentPath }}
        </div>

        <div class="mgs-progress-meta">
          <span>
            <i class="mg-speed-icon mgs-pm-ic"></i>
            {{ __('speed :') }}
            <span>{{ scanningData.filePerSec }}</span>
            {{ __('second/file') }}
          </span>
          <span>
            <i class="mg-hourglass-icon mgs-pm-ic"></i>
            {{ __('Estimated Time Remaining:') }}
            {{ scanningData.estimatedTimeRemaining }}
          </span>
        </div>

        <div class="mgs-fast-scan-wrapper">
          <div class="mgs-progress-actions">
            <button class="mgs-btn mgs-btn-danger" @click="$emit('stop')">
              <i class="mg-stop-icon mgs-ss-ic"></i>
              {{ __('Stop Scan') }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { __ } from '../../../config'
import { useTools } from '@/composables/useTools.js'

defineProps({
  scanning: {
    type: Boolean,
    default: false
  },

  scanningData: {
    type: Object,
    required: true
  }
})

defineEmits(['stop'])
</script>