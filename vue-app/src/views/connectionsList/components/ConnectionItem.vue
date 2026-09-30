<template>
  <li class="mg-connection-item">

    <div class="mg-ci-right">

      <div class="mg-cir-head">
        <span class="mg-cir-internet-icon-parent">
          <i class="mg-internet-icon mg-cir-hosts"></i>
        </span>
      </div>

      <div class="mg-connection-body">

        <div class="mg-cb-row">
          <span class="mg-cb-row-title mgs-cur-d">{{ connection.name }}</span>
          <span class="mg-cb-row-text mgs-cur-d">{{ connection.domain }}</span>
        </div>

        <div class="mg-cb-row">
          <span class="mg-cb-row-title mgs-cur-d">
            {{ __('transferred files') }}
          </span>
          <span class="mg-cb-row-text mgs-cur-d">
            {{ connection.transferred_files }}
          </span>
        </div>

        <div class="mg-cb-row">
          <span class="mg-cb-row-title mgs-cur-d">
            {{ __('protocol') }}
          </span>
          <div class="mg-cb-protocols mgs-cur-d"
              :class="{ ftp: connection.protocol === `FTP` , http: connection.protocol === `HTTP`}">
            <span class="mg-ftp-protocol mg-cb-protocol-item ftp">FTP</span>
            <span class="mg-http-protocol mg-cb-protocol-item http">HTTP</span>
          </div>
        </div>

        <div class="mg-cb-row">

          <div class="mg-connection-item-status-wrapper" :class="status">

            <div class="mg-connection-status success">
              <i class="mg-cs-icon mg-tick-icon"></i>
              <span class="mg-cs-text mgs-cur-d">{{ __('connected') }}</span>
            </div>

            <div class="mg-connection-status checking">
              <i class="mg-cs-icon mgs-refresh-icon"></i>
              <span class="mg-cs-text mgs-cur-d">{{ __('checking connection') }}</span>
            </div>

            <div class="mg-connection-status interrupted">
              <i class="mg-cs-icon mg-warning-icon"></i>
              <span class="mg-cs-text mgs-cur-d">{{ __('connection interrupted') }}</span>
            </div>

          </div>

          <span class="mg-cb-row-title mgs-cur-d">
            {{ __('last update') }}
          </span>
          <span class="mg-cb-row-text mgs-cur-d">
            {{ connection.updated_at }}
          </span>
        </div>

      </div>
    </div>

    <div class="mg-ci-left">

      <button
          class="mg-ci-action-btn"
          :title="__('check again')"
          @click="$emit('refresh')"
      >
        <i class="mg-ci-action-icon mgs-refresh-icon"></i>
      </button>

      <button
          class="mg-ci-action-btn"
          :title="__('edit')"
          :disabled="buttonDisabled"
          @click="$emit('edit')"
      >
        <i class="mg-ci-action-icon mg-edit-icon"></i>
      </button>


      <button
          class="mg-ci-action-btn"
          :title="__('delete')"
          :disabled="buttonDisabled"
          @click="$emit('delete')"
      >
        <i class="mg-ci-action-icon mg-delete-icon"></i>
      </button>

    </div>

  </li>
</template>

<script setup>
import { __ } from '../../../config'

defineProps({
  connection: {
    type: Object,
    required: true
  },

  status: {
    type: String,
    default: 'checking'
  },

  buttonDisabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['refresh', 'edit', 'delete'])
</script>