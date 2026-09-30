<template>
  <div id="mg-main-dialog-wrapper">
    <div class="mgs-light-overlay" v-if="show_dialog_transfer">

      <div class="mgs-light-modal">

        <div class="mgs-light-header">
          <div class="mgs-light-title">
            <div v-if="!restore_file">
              <h2>
                <i class="mg-upload-icon mgs-lt-ic"></i>
                {{ __('Transfer File to Server') }}
              </h2>
              <p>{{ __('Transfer your files from media to remote server') }}</p>
            </div>
            <div v-else>
              <h2>
                <i class="mg-download-icon mgs-lt-ic"></i>
                {{ __('Transfer to WordPress Media') }}
              </h2>
              <p>{{ __('Review the files , then start the transfer.') }}</p>
            </div>
          </div>
          <button
              class="mgs-light-close"
              :title="__('close')"
              @click="closeDialog"
          >
            <i class="mg-close-icon mgs-cd-ic"></i>
          </button>
        </div>

        <div class="mgs-light-body">

          <MediaDialogFileInfo
              :attachment-info="attachment_info"
              :loading-media="loadingMedia()"
              :loading-hosts="loadingHosts()"
              :restore-file="restore_file"
          />

          <MediaDialogHostList
              :hosts="list_hosts"
              :selected-server="dialogSelectedServer"
              :loading-hosts="loadingHosts()"
              :restore-file="restore_file"
              :attachment-id="attachment_info.id"
              :reference-review="referenceReview"
              :reference-files="referenceFiles"
              :file-transport-details="fileTransportDetails"
              :ring-circ="RING_CIRC"
              @select="selectTransferServer"
          />

        </div>

        <div class="mgs-light-footer">
          <button
              v-if="!restore_file"
              class="mgs-light-btn mgs-light-confirm"
              @click="startTransfer(attachment_info.id)"
          >
            <i class="mg-upload-icon mgs-st-ic"></i>
            {{ __('Start Transfer') }}
          </button>
          <button
              v-else
              class="mgs-light-btn mgs-light-confirm"
              @click="startTransfer(attachment_info.id)"
          >
            <i class="mg-download-icon mgs-st-ic"></i>
            {{ __('Start Restore') }}
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { __ } from '@/config/index.js'
import { useMediaDialog } from '@/composables/useMediaDialog.js'
import MediaDialogFileInfo from './MediaDialogFileInfo.vue'
import MediaDialogHostList from './MediaDialogHostList.vue'

const {
  show_dialog_transfer,
  restore_file,
  attachment_info,
  list_hosts,
  dialogSelectedServer,
  referenceReview,
  referenceFiles,
  fileTransportDetails,
  RING_CIRC,
  loadingMedia,
  loadingHosts,
  selectTransferServer,
  startTransfer,
  closeDialog
} = useMediaDialog()
</script>