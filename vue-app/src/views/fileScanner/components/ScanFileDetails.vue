<template>
  <table class="mgs-file-table">
    <thead>
    <tr>
      <th>{{ __('File Type') }}</th>
      <th>{{ __('Total Files') }}</th>
      <th>{{ __('In WordPress') }}</th>
      <th>{{ __('On Remote Server') }}</th>
      <th>{{ __('Total WordPress Size') }}</th>
      <th>{{ __('Total Remote Server Size') }}</th>
    </tr>
    </thead>

    <tbody>
    <tr v-for="item in details" :key="item.id">
      <td>
        <div class="mgs-file-type" :class="rowClass(item.file_type)">
            <span class="mgs-file-icon" :class="iconClass(item.file_type)">
              <i class="mgs-fr-ic" :class="iconType(item.file_type)"></i>
            </span>
          <span class="mgs-file-name">{{ item.extension }}</span>
        </div>
      </td>

      <td>
        <strong class="mgs-count">{{ item.total_files }}</strong>
      </td>

      <td>
        <strong class="mgs-count">{{ item.in_wordpress }}</strong>
        <span class="mgs-percent">({{ item.wordpress_percentage }}%)</span>
      </td>

      <td>
        <strong class="mgs-count">{{ item.in_remote }}</strong>
        <span class="mgs-percent">({{ item.remote_percentage }}%)</span>
      </td>

      <td>
        <strong class="mgs-size">{{ formatBytes(item.wordpress_size) }}</strong>
      </td>

      <td>
        <strong class="mgs-size">{{ formatBytes(item.remote_size) }}</strong>
      </td>

    </tr>

    <tr class="mgs-total-row">
      <td>
        <div class="mgs-file-type mgs-fr-total">
            <span class="mgs-file-icon mgs-icon-total">
              <i class="mgs-fr-ic mg-layers-icon"></i>
            </span>
          <span class="mgs-file-name">{{ __('Total') }}</span>
        </div>
      </td>
      <td>
        <strong class="mgs-count">{{ totals.total_files }}</strong>
      </td>
      <td>
        <strong class="mgs-count">{{ totals.in_wordpress }}</strong>
        <span class="mgs-percent">({{ totals.wordpress_percentage }}%)</span>
      </td>
      <td>
        <strong class="mgs-count">{{ totals.in_remote }}</strong>
        <span class="mgs-percent">({{ totals.remote_percentage }}%)</span>
      </td>
      <td>
        <strong class="mgs-size">{{ formatBytes(totals.wordpress_size) }}</strong>
      </td>
      <td>
        <strong class="mgs-size">{{ formatBytes(totals.remote_size) }}</strong>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script setup>
import { __ } from '../../../config'
import { useTools } from '@/composables/useTools.js'

const { formatBytes } = useTools()

defineProps({
  details: {
    type: Array,
    default: () => []
  },

  totals: {
    type: Object,
    default: () => ({})
  }
})

function rowClass(fileType) {
  return {
    'mgs-fr-archive': fileType === 'archive',
    'mgs-fr-images':  fileType === 'image',
    'mgs-fr-videos':  fileType === 'video',
    'mgs-fr-audio':   fileType === 'audio',
    'mgs-fr-others':  fileType === 'other'
  }
}

function iconClass(fileType) {
  return {
    'mgs-icon-ach':    fileType === 'archive',
    'mgs-icon-green':  fileType === 'image',
    'mgs-icon-purple': fileType === 'video',
    'mgs-icon-red':    fileType === 'audio',
    'mgs-icon-gray':   fileType === 'other'
  }
}

function iconType(fileType) {
  return {
    'mg-archive-icon': fileType === 'archive',
    'mg-image-icon':   fileType === 'image',
    'mg-clip-icon':    fileType === 'video',
    'mg-audio-icon':   fileType === 'audio',
    'mg-file-icon':    fileType === 'other'
  }
}
</script>