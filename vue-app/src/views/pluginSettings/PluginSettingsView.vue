<template>
  <div class="mgs-plugin-settings">

    <header class="mgs-settings-header">
      <div class="mgs-settings-icon">
        <i class="mg-sliders-icon mgs-sh-ic"></i>
        <span class="mgs-settings-dot"></span>
      </div>
      <h1 class="mgs-settings-title">{{ __('Plugin Settings') }}</h1>
    </header>

    <div class="mgs-tabs-container">

      <SettingsTabs
          :active-tab="settingActiveTab"
          @change="settingTabBtn"
      />

      <WpMediaSettings
          v-show="settingActiveTab === 'media'"
          :class="['mgs-tab-content', { 'mgs-tab-active': settingActiveTab === 'media' }]"
          v-model:auto-transfer-enabled="pluginSettings.auto_transfer.enabled"
          v-model:connection-id="pluginSettings.auto_transfer.connection_id"
          :connections="mgsConnectionList"
          :roles="wpRoles"
          @role-change="handleRoleChange"
          :role-exists-check="roleExists"
      />

      <SettingsSaveBar @save="handleSave" />

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { __ } from '../../config'
import { usePluginSettings } from '@/composables/usePluginSettings.js'
import { useConnectionList } from '@/composables/useConnectionList'
import { useLoading } from '@/composables/useLoading'

import SettingsTabs from './components/SettingsTabs.vue'
import WpMediaSettings from './components/WpMediaSettings.vue'
import SettingsSaveBar from './components/SettingsSaveBar.vue'

const {
  settingActiveTab,
  pluginSettings,
  wpRoles,
  getAllSettings,
  getWpRoles,
  settingTabBtn,
  roleChanges,
  roleExists,
  savePluginSettings
} = usePluginSettings()

const { mgsConnectionList, getConnectionList } = useConnectionList()

const { start: startLoading, stop: stopLoading } = useLoading()

function handleRoleChange(roleKey, event) {
  roleChanges(roleKey, event)
}

function handleSave(event) {
  savePluginSettings(event)
}

onMounted(async () => {
  startLoading()

  await Promise.all([
    getAllSettings(),
    getWpRoles(),
    getConnectionList()
  ])

  stopLoading()
})
</script>