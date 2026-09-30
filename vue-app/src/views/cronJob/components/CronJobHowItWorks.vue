<template>
  <div class="mgs-steps">

    <div class="mgs-step">
      <div class="mgs-step-icon mgs-step-1">1</div>
      <div class="mgs-step-info">
        <strong>{{ __('Check Time') }}</strong>
        <p>{{ __('WordPress runs the cron job at the scheduled time.') }}</p>
      </div>
      <i class="fa-solid fa-arrow-left mgs-step-arrow"></i>
    </div>

    <div class="mgs-step">
      <div class="mgs-step-icon mgs-step-2">2</div>
      <div class="mgs-step-info">
        <strong>{{ __('Get Files') }}</strong>
        <p>{{ __('The system gets the files pending transfer from the scan results.') }}</p>
      </div>
      <i class="fa-solid fa-arrow-left mgs-step-arrow"></i>
    </div>

    <div class="mgs-step">
      <div class="mgs-step-icon mgs-step-3">3</div>
      <div class="mgs-step-info">
        <strong>{{ __('Transfer Files') }}</strong>
        <p>{{ __('Files are transferred to the destination server.') }}</p>
      </div>
      <i class="fa-solid fa-arrow-left mgs-step-arrow"></i>
    </div>

    <div class="mgs-step">
      <div class="mgs-step-icon mgs-step-4">4</div>
      <div class="mgs-step-info">
        <strong>{{ __('Update Status') }}</strong>
        <p>{{ __('File statuses are updated and the process repeats.') }}</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { __ } from '../../../config'
</script>