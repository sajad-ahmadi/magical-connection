<template>
  <div class="mgs-file-scanner">

    <header class="mgs-dash-header">
      <div class="mgs-header-right">
        <i class="mg-scanning-icon mgs-header-ic"></i>
        <div class="mgs-title-group">
          <h1 class="mgs-title">{{ __('Scan Files') }}</h1>
          <p class="mgs-subtitle">
            {{ __('Scan and check files in selected paths.') }}
          </p>
        </div>
      </div>

      <div class="mgs-header-left">
        <button
            class="mgs-btn mgs-btn-primary mgs-start-new-scan-btn"
            @click="handleStartNewScan"
        >
          <i class="mg-play-icon mgs-sns-ic"></i>
          {{ __('Start New Scan') }}
        </button>
      </div>
    </header>

    <ScanProgressCard
        v-show="fileScannerObject.showScanData"
        :scanning="fileScannerObject.scanning"
        :scanning-data="fileScannerObject.scanningData"
        @stop="stopScanBtn"
    />

    <div class="mgs-card mgs-summary-card">
      <h3 class="mgs-card-title">{{ __('Scan Results Summary') }}</h3>
      <div class="mgs-summary-grid">
        <ScanSummary :summary="scanSummery" />
      </div>
    </div>

    <div class="mgs-card mgs-file-details-card">

      <div class="mgs-card-header">
        <div class="mgs-header-right">
          <h3 class="mgs-card-title">{{ __('File Details by Type') }}</h3>
        </div>
        <div class="mgs-header-subtitle">
          ( {{ __('Total Files Found') }}
          <span class="mgs-total-count">{{ extension_total.total_files }}</span> )
        </div>
      </div>

      <div class="mgs-table-wrapper">
        <ScanFileDetails
            :details="scannedFilesDetailsByType"
            :totals="extension_total"
        />
      </div>

    </div>

  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { __ } from '../../config'
import { useFileScanner } from '@/composables/useFileScanner.js'
import { useLoading } from '@/composables/useLoading'

import ScanProgressCard from './components/ScanProgressCard.vue'
import ScanSummary from './components/ScanSummary.vue'
import ScanFileDetails from './components/ScanFileDetails.vue'

const {
  fileScannerObject,
  scanSummery,
  scannedFilesDetailsByType,
  extension_total,
  getScanSummeryData,
  startNewScanBtn,
  stopScanBtn
} = useFileScanner()

const { start: startLoading, stop: stopLoading } = useLoading()

function handleStartNewScan(event) {
  startNewScanBtn(event)
}

onMounted(async () => {
  startLoading()

  await getScanSummeryData()

  stopLoading()
})
</script>