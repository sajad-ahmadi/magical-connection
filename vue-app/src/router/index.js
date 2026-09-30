import { createRouter, createWebHashHistory } from "vue-router"

const routes = [
  { path: "/", redirect: { name: "connection-list" } },
  {
    path: "/connections",
    name: "connection-list",
    component: () => import("../views/connectionsList/ConnectionListView.vue"),
    meta: { title: "your connections", icon: "mg-server-icon" }
  },
  {
    path: "/connections/new",
    name: "add-connection",
    component: () => import("../views/newConnection/EditAddConnectionView.vue"),
    meta: { title: "new connection", parent: "connection-list" }
  },
  {
    path: "/connections/:id/edit",
    name: "edit-connection",
    component: () => import("../views/newConnection/EditAddConnectionView.vue"),
    props: true,
    meta: { title: "edit connection", parent: "connection-list" }
  },
  {
    path: "/settings",
    name: "plugin-settings",
    component: () => import("../views/pluginSettings/PluginSettingsView.vue"),
    meta: { title: "plugin settings", icon: "mg-setting-icon" }
  },
  {
    path: "/cron-job",
    name: "cron-job",
    component: () => import("../views/cronJob/CronJobView.vue"),
    meta: { title: "cron job", icon: "mgs-cron-job-icon" }
  },
  {
    path: "/faq",
    name: "faq",
    component: () => import("../views/faq/FaqView.vue"),
    meta: { title: "Frequently Questions", icon: "mg-question-icon" }
  },
  {
    path: "/scanner",
    name: "file-scanner",
    component: () => import("../views/fileScanner/FileScannerView.vue"),
    meta: { title: "File Scanner", icon: "mg-scanning-icon" }
  },
  { path: "/:pathMatch(.*)*", redirect: { name: "connection-list" } }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

export default router