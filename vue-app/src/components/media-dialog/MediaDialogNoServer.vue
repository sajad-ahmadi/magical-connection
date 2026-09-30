<template>
  <div class="empty-state">
    <svg width="220" height="220" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="serverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#e8f0fe" />
          <stop offset="100%" stop-color="#b8d4f0" />
        </linearGradient>
        <linearGradient id="warnGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#ff9800" />
          <stop offset="100%" stop-color="#f44336" />
        </linearGradient>
      </defs>

      <ellipse cx="100" cy="178" rx="50" ry="5" fill="#000" opacity="0.1" />

      <g class="server-body">
        <rect x="55" y="80" width="90" height="85" rx="8" fill="url(#serverGrad)" stroke="#7ba7d9" stroke-width="2" />
        <rect x="55" y="80" width="90" height="14" rx="8" fill="#7ba7d9" opacity="0.3" />
        <line x1="60" y1="105" x2="140" y2="105" stroke="#a8c5e8" stroke-width="1.5" />
        <line x1="60" y1="125" x2="140" y2="125" stroke="#a8c5e8" stroke-width="1.5" />
        <line x1="60" y1="145" x2="140" y2="145" stroke="#a8c5e8" stroke-width="1.5" />
        <circle class="light light-1" cx="72" cy="93" r="3.5" fill="#4caf50" />
        <circle class="light light-2" cx="72" cy="115" r="3.5" fill="#4caf50" />
        <circle class="light light-3" cx="72" cy="135" r="3.5" fill="#4caf50" />
        <rect x="120" y="90" width="18" height="7" rx="2" fill="#7ba7d9" opacity="0.6" />
        <rect x="120" y="112" width="18" height="7" rx="2" fill="#7ba7d9" opacity="0.6" />
        <rect x="120" y="132" width="18" height="7" rx="2" fill="#7ba7d9" opacity="0.6" />
      </g>

      <circle class="dashed-ring" cx="100" cy="50" r="32" fill="none" stroke="#ff9800" stroke-width="1.5" stroke-dasharray="6 6" opacity="0.4" />
      <circle class="warning-pulse" cx="100" cy="50" r="25" fill="url(#warnGrad)" />
      <circle cx="100" cy="50" r="22" fill="#fff5e6" stroke="#ff9800" stroke-width="2.5" />

      <g class="warning-icon">
        <line class="x-line" x1="91" y1="41" x2="109" y2="59" stroke="#ff5722" stroke-width="3.5" stroke-linecap="round" />
        <line class="x-line x-line-2" x1="109" y1="41" x2="91" y2="59" stroke="#ff5722" stroke-width="3.5" stroke-linecap="round" />
      </g>
    </svg>

    <h3 class="empty-title">{{ __("You haven't added any servers yet.") }}</h3>
    <p class="empty-text">{{ __('Your server list is empty. To get started, add your first server.') }}</p>
    <a @click="goToPlugin" class="empty-btn">{{ __('Add New +') }}</a>
  </div>
</template>

<script setup>
import { __ , MagicalConnection } from '../../config'

const goToPlugin = () => {
  console.log()
  window.location.href = MagicalConnection.pluginPage
}
</script>