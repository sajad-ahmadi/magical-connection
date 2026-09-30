<template>
  <MediaDialog />
  <NotifyContainer />
</template>

<script setup>
import { onMounted } from 'vue'
import MediaDialog from './components/media-dialog/MediaDialog.vue'
import { useMediaDialog } from './composables/useMediaDialog'
import NotifyContainer from "./components/NotifyContainer.vue"

const {
  show_dialog_transfer,
  list_hosts,
  attachment_info,
  restore_file,
  openDialog
} = useMediaDialog()

onMounted(() => {
  window.MagicalConnectionAppDialog = {
    openDialog(attachmentId) {
      openDialog(attachmentId)
    },

    get list_hosts() {
      return list_hosts.value
    },
    set list_hosts(val) {
      list_hosts.value = val
    },

    get attachment_info() {
      return attachment_info.value
    },
    set attachment_info(val) {
      attachment_info.value = val
    },

    get restore_file() {
      return restore_file.value
    },
    set restore_file(val) {
      restore_file.value = val
    },

    get show_dialog_transfer() {
      return show_dialog_transfer.value
    }
  }
})
</script>