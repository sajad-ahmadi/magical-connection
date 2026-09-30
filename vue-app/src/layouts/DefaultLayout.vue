<template>
  <div id="mg-main-setting-wrapper" class="mg-main-setting-wrapper">
    <div class="mg-setting-main-container">

      <aside class="mg-setting-side-menu">
        <SideMenu />
      </aside>

      <div class="mg-setting-body">
        <div class="mg-content-wrapper">
          <LoadingOverlay />
          <slot />
        </div>
      </div>

    </div>
    <NotifyContainer />
  </div>
</template>

<script setup>
import SideMenu from "../components/SideMenu.vue"
import LoadingOverlay from "../components/LoadingOverlay.vue"
import NotifyContainer from "../components/NotifyContainer.vue"
</script>