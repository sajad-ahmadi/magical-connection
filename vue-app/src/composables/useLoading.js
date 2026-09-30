import { ref } from "vue"

if (!window.__MGS_LOADING_REF__) {
  window.__MGS_LOADING_REF__ = ref(false)
}

const isLoading = window.__MGS_LOADING_REF__

export function useLoading() {
  function start() {
    isLoading.value = true
  }
  function stop() {
    isLoading.value = false
  }
  async function wrap(fn) {
    start()
    try { return await fn() } finally { stop() }
  }
  return { isLoading, start, stop, wrap }
}