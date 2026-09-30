<template>
  <div class="mgs-light-column mgs-light-file-info">

    <div class="mgs-light-preview">
      <div class="mgs-light-preview-shine"></div>
      <img
          v-if="!loadingMedia"
          :src="attachmentInfo.url"
          :alt="__('Preview')"
          class="mgs-light-image"
      />
      <div v-if="loadingMedia" class="skeleton-item">
        <div class="radial-skeleton"></div>
      </div>
    </div>

    <div class="mgs-light-file-name">
      <strong v-if="!loadingMedia">{{ attachmentInfo.name }}</strong>
      <div v-if="loadingMedia" class="file-info">
        <div class="skeleton-name"></div>
        <div class="skeleton-meta"></div>
      </div>
      <span class="mgs-light-type" v-if="!loadingMedia">{{ attachmentInfo.type }}</span>
    </div>

    <div class="mgs-light-details">
      <div v-if="loadingMedia" class="file-info">
        <div class="skeleton-name"></div>
        <div class="skeleton-meta"></div>
      </div>

      <div class="mgs-light-detail-row" v-if="!loadingMedia">
        <span class="mgs-light-detail-label">
          <i class="mg-database-icon mgs-ldr-ic"></i>
          {{ __('File Size:') }}
        </span>
        <span class="mgs-light-detail-value">{{ formatBytes(attachmentInfo.size) }}</span>
      </div>

      <div class="mgs-light-detail-row" v-if="!loadingMedia">
        <span class="mgs-light-detail-label">
          <i class="mg-img-size-icon mgs-ldr-ic"></i>
          {{ __('Resolution:') }}
        </span>
        <span class="mgs-light-detail-value">
          {{ attachmentInfo.width }} × {{ attachmentInfo.height }}
        </span>
      </div>

      <div class="mgs-light-detail-row" v-if="!loadingMedia">
        <span class="mgs-light-detail-label">
          <i class="mg-file-icon mgs-ldr-ic"></i>
          {{ __('File Type:') }}
        </span>
        <span class="mgs-light-detail-value">{{ attachmentInfo.mime }}</span>
      </div>

      <div class="mgs-light-detail-row" v-if="!loadingMedia">
        <span class="mgs-light-detail-label">
          <i class="mg-folder-icon mgs-ldr-ic"></i>
          {{ __('File Path:') }}
        </span>
        <span class="mgs-light-detail-value" v-if="restoreFile">{{ attachmentInfo.url }}</span>
        <span class="mgs-light-detail-value" v-if="!restoreFile">{{ attachmentInfo.dir_upload }}</span>
      </div>

      <div class="mgs-light-detail-row" v-if="!loadingMedia">
        <span class="mgs-light-detail-label">
          <i class="mg-calendar-icon mgs-ldr-ic"></i>
          {{ __('Upload Date:') }}
        </span>
        <span class="mgs-light-detail-value">{{ attachmentInfo.date }}</span>
      </div>

      <div class="mgs-light-detail-row" v-if="!loadingMedia">
        <span class="mgs-light-detail-label">
          <i class="mg-hashtag-icon mgs-ldr-ic"></i>
          {{ __('File ID:') }}
        </span>
        <span class="mgs-light-detail-value">{{ attachmentInfo.id }}</span>
      </div>
    </div>

    <div v-if="!loadingMedia" class="mgs-light-sizes">
      <h4>{{ __('Available Sizes for This Image') }}</h4>
      <div class="mgs-light-table-wrapper">
        <table class="mgs-light-table">
          <thead>
          <tr>
            <th>{{ __('Preview') }}</th>
            <th>{{ __('Resolution') }}</th>
            <th>{{ __('File Size') }}</th>
            <th>{{ __('Dimensions') }}</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(item, key) in attachmentInfo.sizes" :key="key">
            <td><img :src="item.url" class="mgs-light-thumb" /></td>
            <td>{{ item.width }} × {{ item.height }}</td>
            <td>{{ formatBytes(item.filesize) }}</td>
            <td><span class="mgs-light-badge">{{ key }}</span></td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { __ } from '../../config'
import { useTools } from '@/composables/useTools.js'

const { formatBytes } = useTools()

defineProps({
  attachmentInfo: { type: Object, default: () => ({}) },
  loadingMedia:   { type: Boolean, default: true },
  loadingHosts:   { type: Boolean, default: true },
  restoreFile:    { type: Boolean, default: false }
})
</script>