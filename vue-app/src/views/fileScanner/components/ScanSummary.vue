<template>
  <div class="mgs-summary-item mgs-si-docs">
    <div class="mgs-icon-box mgs-icon-blue">
      <i class="mgs-dt-ic mg-docs-icon"></i>
    </div>
    <div class="mgs-summary-info">
      <span>{{ __('Documents') }}</span>
      <strong>{{ summary.document.count }}</strong>
      <small>({{ summary.document.percentage }})</small>
    </div>
  </div>

  <div class="mgs-summary-item mgs-si-images">
    <div class="mgs-icon-box mgs-icon-green">
      <i class="mgs-dt-ic mg-image-icon"></i>
    </div>
    <div class="mgs-summary-info">
      <span>{{ __('Images') }}</span>
      <strong>{{ summary.image.count }}</strong>
      <small>({{ summary.image.percentage }})</small>
    </div>
  </div>

  <div class="mgs-summary-item mgs-si-audio">
    <div class="mgs-icon-box mgs-icon-red">
      <i class="mgs-dt-ic mg-audio-icon"></i>
    </div>
    <div class="mgs-summary-info">
      <span>{{ __('Audio') }}</span>
      <strong>{{ summary.audio.count }}</strong>
      <small>({{ summary.audio.percentage }})</small>
    </div>
  </div>

  <div class="mgs-summary-item mgs-si-videos">
    <div class="mgs-icon-box mgs-icon-purple">
      <i class="mgs-dt-ic mg-clip-icon"></i>
    </div>
    <div class="mgs-summary-info">
      <span>{{ __('Videos') }}</span>
      <strong>{{ summary.video.count }}</strong>
      <small>({{ summary.video.percentage }})</small>
    </div>
  </div>

  <div class="mgs-summary-item mgs-si-others">
    <div class="mgs-icon-box mgs-icon-gray">
      <i class="mgs-dt-ic mg-file-icon"></i>
    </div>
    <div class="mgs-summary-info">
      <span>{{ __('Others') }}</span>
      <strong>{{ summary.other.count }}</strong>
      <small>({{ summary.other.percentage }})</small>
    </div>
  </div>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  summary: {
    type: Object,
    required: true
  }
})
</script>