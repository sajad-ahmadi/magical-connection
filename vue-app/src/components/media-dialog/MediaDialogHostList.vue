<template>
  <div class="mgs-light-column mgs-light-hosts">

    <div class="mgs-light-section" v-if="!restoreFile">
      <div v-if="hosts.length > 0">
        <div class="mgs-light-section-header">
          <i class="mg-server-icon mg-lsh-ic"></i>
          <h4>{{ __('Select Server') }}</h4>
        </div>
        <p class="mgs-light-section-desc">
          {{ __('Select one of your servers to transfer the file.') }}
        </p>

        <div class="mgs-light-host-list">
          <div v-if="loadingHosts">{{ __('loading...') }}</div>
          <div
              v-for="item in hosts"
              :key="item.id"
              :class="['mgs-light-host', { 'mgs-light-host-selected': selectedServer === item.id }]"
              @click="$emit('select', item.id)"
          >
            <div class="mgs-light-host-info">
              <div class="mgs-light-host-name">
                <span class="mgs-light-badge-live">{{ __('Active') }}</span>
                <strong>{{ item.name }}</strong>
              </div>
              <div class="mgs-light-host-url">{{ item.domain }}</div>
            </div>
            <div class="mgs-light-radio">
              <span class="mgs-light-radio-dot"></span>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        <MediaDialogNoServer />
      </div>
    </div>

    <div class="mgs-light-section mgs-light-settings">

      <div v-if="!restoreFile" class="modal-shell">

        <div class="stepper">
          <div
              class="step-chip"
              :class="{'is-active': referenceReview.reviewStarted && !referenceReview.reviewed,'is-done': referenceReview.reviewed}">
            <span class="num">1</span>
            <span>{{ __('Check References') }}</span>
          </div>
          <div class="step-line"></div>
          <div
              class="step-chip"
              :class="{'is-active': fileTransportDetails.transferStarted && !fileTransportDetails.transferFinished,'is-done': fileTransportDetails.transferFinished}">
            <span class="num">2</span>
            <span>{{ __('Transfer') }}</span>
          </div>
        </div>

        <div class="scan-card">
          <h3 class="section-title">
            {{ __('Check References') }}
            <span class="code-tag">syncAttachmentReferences</span>
          </h3>
          <p class="section-desc">
            {{ __('This step takes the attachment ID and searches the entire database...') }}
          </p>

          <div
              v-show="referenceReview.reviewStarted"
              class="scan-status"
              :class="{ 'is-done': referenceReview.reviewed }"
          >
            <span v-show="!referenceReview.reviewed" class="pulse-dot"></span>
            <span v-if="!referenceReview.reviewed" class="scanStatusText">
              {{ __('Checking References...') }}
            </span>
            <span v-else class="scanStatusText">
              {{ referenceReview.referenceFound }} {{ __('Reference Found') }}
            </span>
          </div>
        </div>

        <hr class="divider" />

        <div
            v-show="referenceReview.reviewed && referenceFiles.length > 0"
            class="transfer-section"
            :class="{ 'is-active': fileTransportDetails.transferStarted }"
        >
          <div class="transfer-head">
            <div>
              <h3 class="section-title">{{ __('Transferring Files...') }}</h3>
              <p class="transfer-sub">
                <span class="pulse-dot"></span>
                <span>
                  {{ __('Uploading —') }}
                  {{ toFa(fileTransportDetails.doneCount) }}
                  {{ __('of') }}
                  {{ toFa(referenceFiles.length) }}
                  {{ __('completed') }}
                </span>
              </p>
            </div>
            <div class="ring">
              <svg viewBox="0 0 40 40">
                <defs>
                  <linearGradient id="accentGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#007bff" />
                    <stop offset="100%" stop-color="#00b4d8" />
                  </linearGradient>
                </defs>
                <circle class="track" cx="20" cy="20" r="17" />
                <circle
                    class="fill"
                    cx="20" cy="20" r="17"
                    :stroke-dasharray="ringCirc"
                    :stroke-dashoffset="ringCirc * (1 - progressRatio)"
                />
              </svg>
              <div class="ring-label">
                <span>{{ toFa(referenceFiles.length) }}</span>/<span>{{ toFa(fileTransportDetails.doneCount) }}</span>
              </div>
            </div>
          </div>

          <ul class="transfer-list">
            <li class="row" v-for="file in referenceFiles" :key="file.key">
              <div class="row-top">
                <div class="mgs-file-type">
                  <img :src="file.url" />
                </div>
                <div class="row-body">
                  <div class="row-name">{{ file.name }}</div>
                  <div class="row-meta">
                    <span>{{ formatBytes(file.size) }}</span>
                    <span class="dot"></span>
                    <span>{{ file.width }} × {{ file.height }}</span>
                  </div>
                </div>
                <div class="row-status">
                  <span v-if="file.status === 'queue'" class="status-label">
                    {{ __('in queue') }}
                  </span>
                  <span v-else-if="file.status === 'uploading'" class="status-label uploading">
                    {{ __('uploading') }}
                    <i class="mgs-refresh-icon"></i>
                  </span>
                  <span v-else-if="file.status === 'done'" class="status-label done">
                    {{ __('done') }}
                  </span>
                </div>
              </div>
              <div
                  class="row-progress"
                  :class="{ 'is-visible': file.status === 'uploading' }"
              >
                <div class="row-progress-fill"></div>
              </div>
            </li>
          </ul>
        </div>

      </div>

      <div class="mgs-light-note">
        <div class="mgs-light-note-header">
          <i class="mg-information-icon mgs-lnh-ic"></i>
          <strong>{{ __('Note') }}</strong>
        </div>
        <ul v-if="!restoreFile">
          <li>{{ __('All image sizes (original and thumbnails) will be transferred.') }}</li>
          <li>{{ __('After a successful transfer, the file link in WordPress will be changed to the server') }}</li>
        </ul>
        <ul v-else>
          <li>{{ __('All image sizes (original version and thumbnails) will be restored to WordPress.') }}</li>
          <li>{{ __('After a successful restored, the file link in WordPress will be changed.') }}</li>
        </ul>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { __ } from '../../config'
import { useTools } from '@/composables/useTools.js'
import MediaDialogNoServer from './MediaDialogNoServer.vue'

const { formatBytes, toFa } = useTools()

const props = defineProps({
  hosts:                { type: Array, default: () => [] },
  selectedServer:       { type: [String, Number], default: null },
  loadingHosts:         { type: Boolean, default: false },
  restoreFile:          { type: Boolean, default: false },
  attachmentId:         { type: [String, Number], default: null },
  referenceReview:      { type: Object, default: () => ({}) },
  referenceFiles:       { type: Array, default: () => [] },
  fileTransportDetails: { type: Object, default: () => ({}) },
  ringCirc:             { type: Number, default: 106.8 }
})

defineEmits(['select'])

const progressRatio = computed(() => {
  if (!props.referenceFiles.length) return 0
  return props.fileTransportDetails.doneCount / props.referenceFiles.length
})
</script>