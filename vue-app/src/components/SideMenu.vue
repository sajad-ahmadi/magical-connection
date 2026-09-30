<template>
  <div class="mg-side-menu">

    <div class="mg-side-menu-logo-container">

      <div class="mg-sml-row">
        <h4 class="mg-sm-plugin-name mgs-cur-d">
          {{ __('Magical Connection') }}
        </h4>
      </div>

      <div class="mg-sml-row">
        <div class="mg-sub-title-row mgs-cur-d">
          <span class="mg-primary-sub">{{ __('Manage') }}</span>
          {{ __('Your Server Easily') }}
        </div>
      </div>

    </div>

    <div class="mg-side-menu-navigation-container">
      <nav class="mg-sm-nav">
        <router-link
            v-for="item in mgsSideMenuItems"
            :key="item.id"
            :to="{ name: item.name }"
            :title="item.title"
            class="mg-side-nav-btn"
            :class="{ active: isActive(item.name) }">

          <i :class="['mg-nav-icon', item.icon]"></i>
          <span class="mg-nav-text">{{ item.title }}</span>
        </router-link>
      </nav>
    </div>

    <div class="mg-side-menu-footer-container">
      <span class="mg-version mgs-cur-d">
        {{ __('version') }} {{MagicalConnection.pluginVersion}}
      </span>
    </div>

  </div>
</template>

<script setup>
import { computed } from "vue"
import { useRoute } from "vue-router"
import { __ , MagicalConnection } from "../config"

const route = useRoute()

const mgsSideMenuItems = computed(() => [
  { name: "connection-list", title: __("your connections"), icon: "mg-server-icon" },
  { name: "plugin-settings", title: __("plugin settings"),  icon: "mg-setting-icon" },
  { name: "cron-job",        title: __("cron job"),         icon: "mgs-cron-job-icon" },
  { name: "faq",             title: __("Frequently Questions"), icon: "mg-question-icon" },
  { name: "file-scanner",    title: __("File Scanner"),     icon: "mg-scanning-icon" }
])

function isActive(key) {
  return route.name === key
}
</script>