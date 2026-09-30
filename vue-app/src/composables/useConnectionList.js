import { ref } from "vue"
import { useRouter } from "vue-router"
import { MagicalConnection, AJAX_HEADERS, __ } from "../config"
import { useNotify } from "./useNotify"
import { useLoading } from "./useLoading"

export function useConnectionList() {
  const router = useRouter()
  const notify = useNotify()
  const loading = useLoading()

  const mgsCnButtonDisabled = ref(true)
  const mgsConnectionList = ref([])
  const connectionStatuss = ref([])

  async function getConnectionList() {
    const postData = { action: "magical_get_connections", nonce: MagicalConnection.nonce }
    try {
      const { data } = await axios.post(MagicalConnection.ajax_url, postData, { headers: AJAX_HEADERS })
      mgsConnectionList.value = data.data.connections
      connectionStatuss.value = mgsConnectionList.value.map(({ id }) => ({
        id: parseInt(id), status: "checking"
      }))
    } catch (error) { console.log(error) }
  }

  function mgsRefreshConnection(connection_id) {
    const exists = connectionStatuss.value.find((i) => parseInt(i.id) === parseInt(connection_id))
    if (exists == null) {
      connectionStatuss.value.push({ id: parseInt(connection_id), status: "checking" })
    } else {
      connectionStatuss.value = connectionStatuss.value.map((item) =>
          item.id === parseInt(connection_id) ? { id: parseInt(connection_id), status: "checking" } : item
      )
    }
    mgsCnButtonDisabled.value = true
    mgsCheckingConnection()
    mgsCnButtonDisabled.value = false
  }

  function test(idp) {
    let status_p = ""
    connectionStatuss.value.forEach(({ id, status }) => {
      if (parseInt(id) === parseInt(idp)) status_p = status
    })
    return status_p
  }

  async function mgsCheckingConnection() {
    for (const { id } of connectionStatuss.value) {
      const postData = { action: "magical_test_connection", connection_id: parseInt(id), nonce: MagicalConnection.nonce }
      try {
        const { data } = await axios.post(MagicalConnection.ajax_url, postData, { headers: AJAX_HEADERS })
        connectionStatuss.value = connectionStatuss.value.map((item) =>
            item.id === parseInt(id) ? { id, status: data.success ? "success" : "interrupted" } : item
        )
      } catch {
        connectionStatuss.value = connectionStatuss.value.map((item) =>
            item.id === parseInt(id) ? { id, status: "interrupted" } : item
        )
      }
    }
  }

  async function deleteConnection(connection_id) {
    if (!confirm("Are you sure?")) return
    mgsCnButtonDisabled.value = true
    const notifyID = notify.info(__("Processing"), __("Deleting Connection..."), -1)
    const postData = { action: "magical_delete_connection", nonce: MagicalConnection.nonce, connection_id }
    try {
      const response = await axios.post(MagicalConnection.ajax_url, postData, { headers: AJAX_HEADERS })
      if (response.data.success) {
        mgsConnectionList.value = mgsConnectionList.value.filter((i) => i.id !== connection_id)
        notify.update(notifyID, "success", __("Done"), __("your connection deleted successfully."), 5000)
      } else {
        notify.update(notifyID, "error", __("Failed"), __("An error occurred while deleting connection. Please try again."), 5000)
      }
    } catch {
      notify.update(notifyID, "error", __("Failed"), __("An error occurred while deleting connection. Please try again."), 5000)
    } finally {
      mgsCnButtonDisabled.value = false
    }
  }

  function editConnectionInfo(connection_id) {
    mgsCnButtonDisabled.value = true
    loading.start()
    router.push({ name: "edit-connection", params: { id: connection_id } })
    mgsCnButtonDisabled.value = false
  }

  function addNewConnection() {
    router.push({ name: "add-connection" })
  }

  function updateConnection(id, newData) {
    const index = mgsConnectionList.value.findIndex((i) => i.connectionID === id)
    if (index !== -1) {
      mgsConnectionList.value.splice(index, 1, { ...mgsConnectionList.value[index], ...newData })
    }
  }

  return {
    mgsCnButtonDisabled, mgsConnectionList, connectionStatuss,
    getConnectionList, mgsRefreshConnection, test, mgsCheckingConnection,
    deleteConnection, editConnectionInfo, addNewConnection, updateConnection
  }
}