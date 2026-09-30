<template>
  <div class="mgs-new-connection">

    <header class="mgs-form-header mgs-animate-in">
      <div class="mgs-header-icon">
        <i class="mg-sliders-icon mgs-ench-ic"></i>
        <span class="mgs-header-dot"></span>
      </div>
      <h1 class="mgs-form-title">{{ __('Add / Edit Connection') }}</h1>
    </header>

    <div class="mgs-form-body-grid">

      <div class="mgs-card mgs-form-card mgs-animate-in" style="animation-delay: 0.1s">
        <h2 class="mgs-card-title">{{ __('Connection Info') }}</h2>

        <ConnectionBaseFields
            v-model:protocol="connectionData.protocol"
            v-model:name="connectionData.name"
            :disabled="inputsDisabled"
        />

        <ConnectionFtpFields
            v-if="connectionData.protocol === 'FTP'"
            v-model:host="connectionData.host"
            v-model:port="connectionData.port"
            v-model:username="connectionData.username"
            v-model:password="connectionData.password"
            v-model:basePath="connectionData.base_path"
            v-model:domain="connectionData.domain"
            v-model:passiveMode="connectionData.passive_mod"
            :disabled="inputsDisabled"
        />

        <ConnectionHttpFields
            v-else-if="connectionData.protocol === 'HTTP'"
            v-model:token="connectionData.token"
            v-model:domain="connectionData.domain"
            :download-url="httpApiDownloadUrl"
            :disabled="inputsDisabled"
            @open-tutorial="handleOpenTutorial"
        />

        <ConnectionFormActions
            :disabled="inputsDisabled"
            @save="handleSave"
            @cancel="handleCancel"
        />

      </div>
    </div>

  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { __ , MagicalConnection } from '../../config'
import { useEditAddConnection } from '@/composables/useEditAddConnection.js'
import { useLoading } from '@/composables/useLoading'

import ConnectionBaseFields from './components/ConnectionBaseFields.vue'
import ConnectionFtpFields from './components/ConnectionFtpFields.vue'
import ConnectionHttpFields from './components/ConnectionHttpFields.vue'
import ConnectionFormActions from './components/ConnectionFormActions.vue'

const route = useRoute()
const router = useRouter()

const {
  inputsDisabled,
  connectionData,
  isEditMode,
  resetFormData,
  resetConnectionTestStatus,
  getConnectionInfo,
  saveConnectionBtn,
  mgsCancelEditConnectionInfo
} = useEditAddConnection()

const { start: startLoading, stop: stopLoading } = useLoading()

const httpApiDownloadUrl =
    (MagicalConnection?.pluginUrl ?? '') + 'resources/http-api.zip'

function handleSave(event) {
  saveConnectionBtn(event)
}

function handleCancel() {
  mgsCancelEditConnectionInfo()
}

function handleOpenTutorial(event) {
  event.preventDefault()
  router.push({ name: 'faq', query: { open: 'mgs-API-setup' } })
}

onMounted(async () => {
  resetConnectionTestStatus()

  if (!isEditMode.value) {
    resetFormData()
    return
  }

  startLoading()

  await getConnectionInfo(route.params.id)

  stopLoading()
})

watch(
    () => route.params.id,
    async (newId) => {
      resetConnectionTestStatus()
      if (!newId) {
        resetFormData()
        return
      }
      startLoading()
      await getConnectionInfo(newId)
      stopLoading()
    }
)
</script>