<template>
  <div class="mgs-connection-list">

    <header class="mgs-dash-header">
      <div class="mgs-header-right">
        <h1 class="mgs-title mgs-cur-d">
          {{ __('your connection') }}
        </h1>
      </div>

      <div class="mgs-header-left">
        <button
            class="mgs-btn mgs-btn-primary mgs-add-new-connection-btn"
            :disabled="mgsCnButtonDisabled"
            @click="addNewConnection">

          <i class="mg-add-new-icon mgs-sns-ic"></i>
          {{ __('add new connection') }}
        </button>
      </div>
    </header>

    <div class="mg-setting-body-wrapper">

      <ul v-if="mgsConnectionList.length > 0" class="mg-connection-list">
        <ConnectionItem
            v-for="connection in mgsConnectionList"
            :key="connection.id"
            :connection="connection"
            :status="test(connection.id)"
            :button-disabled="mgsCnButtonDisabled"
            @refresh="mgsRefreshConnection(connection.id)"
            @edit="editConnectionInfo(connection.id)"
            @delete="deleteConnection(connection.id)"
        />
      </ul>

      <div v-else>
        <img
            class="mgs-empty-state"
            :src="emptyStateImage"
            style="margin: auto; display: block;padding: 0"
            alt="no connections"
        />

        <div class="mgs-empty-state">
          <p class="mgs-empty-title">{{__('Your connection list is empty')}}</p>
          <p class="mgs-empty-subtitle">{{__('No server has been added yet .')}}</p>
          <span class="mgs-empty-line"></span>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from "vue-router"
import { __ , MagicalConnection } from '../../config'
import { useConnectionList } from '@/composables/useConnectionList'
import { useLoading } from '@/composables/useLoading.js'
import ConnectionItem from './components/ConnectionItem.vue'

const router = useRouter()

const {
  mgsCnButtonDisabled,
  mgsConnectionList,
  getConnectionList,
  mgsRefreshConnection,
  test,
  mgsCheckingConnection,
  deleteConnection,
  editConnectionInfo,
  addNewConnection
} = useConnectionList()

const { start: startLoading, stop: stopLoading } = useLoading()

const emptyStateImage =
    (MagicalConnection?.url ?? '') + 'assets/icons/no-connections.svg'

onMounted(async () => {
  startLoading()

  await getConnectionList()

  stopLoading()

  mgsCheckingConnection()
  mgsCnButtonDisabled.value = false
})
</script>

<style scoped>
.mgs-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  direction: rtl;
}

.mgs-empty-title {
  font-family: Vazirmatn, Vazir, Tahoma, sans-serif;
  font-size: 19px;
  font-weight: 600;
  color: #4c1d95;
  letter-spacing: 0.3px;
  margin: 0 0 10px 0;
}

.mgs-empty-subtitle {
  font-family: Vazirmatn, Vazir, Tahoma, sans-serif;
  font-size: 13px;
  color: #94a3b8;
  margin: 0 0 18px 0;
}

.mgs-empty-line {
  display: block;
  width: 140px;
  height: 1.5px;
  background: #c4b5fd;
  border-radius: 2px;
  opacity: 0.6;
  animation: mgs-line-pulse 2.4s ease-in-out infinite;
}

@keyframes mgs-line-pulse {
  0%, 100% { opacity: 0.6; }
  50%      { opacity: 0.2; }
}
</style>