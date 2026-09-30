import {ref, computed} from "vue"
import {useRouter, useRoute} from "vue-router"
import {MagicalConnection, AJAX_HEADERS, __} from "../config"
import {useNotify} from "./useNotify"

function createEmptyConnection() {
    return {
        protocol: "FTP", name: "", host: "", port: 21,
        username: "", password: "", base_path: "/", domain: "",
        passive_mod: true, private_key: "", token: crypto.randomUUID(),
        httpData: {httpAddress: ""}
    }
}

function createEmptySteps() {
    return {
        connectionStep: {pending: true, checking: false, interrupted: false, success: false},
        uploadingStep: {pending: true, checking: false, interrupted: false, success: false},
        finalStep: {pending: true, checking: false, interrupted: false, success: false}
    }
}

export function useEditAddConnection() {
    const router = useRouter()
    const route = useRoute()
    const notify = useNotify()

    const inputsDisabled = ref(false)
    const connectionData = ref(createEmptyConnection())
    const connectionStatus = ref({success: false, steps: createEmptySteps()})

    const passive_mode = computed({
        get: () => {
            const v = connectionData.value.passive_mod
            return v === true || v === "true" || v === 1
        },
        set: (val) => {
            connectionData.value.passive_mod = val
        }
    })

    const isEditMode = computed(() => !!route.params.id)

    function resetFormData() {
        connectionData.value = createEmptyConnection()
    }

    function resetConnectionTestStatus() {
        connectionStatus.value = {success: false, steps: createEmptySteps()}
    }

    async function getConnectionInfo(connection_id) {
        const postData = {action: "magical_get_connection_single", nonce: MagicalConnection.nonce, connection_id}
        try {
            const {data} = await axios.post(MagicalConnection.ajax_url, postData, {headers: AJAX_HEADERS})
            if (data.success) {
                connectionData.value = data.data.connection
            } else {
                notify.error(__("Error"), __("An error occurred while getting connection data."), 5000)
                router.push({name: "connection-list"})
            }
        } catch {
            notify.error(__("Error"), __("An error occurred while getting connection data."), 5000)
            router.push({name: "connection-list"})
        }
    }

    async function saveConnectionBtn(tag) {
        const btn = tag.target
        btn.disabled = true
        inputsDisabled.value = true
        notify.closeAll()
        if (!(await mgsConnectionFormValidation())) {
            btn.disabled = false
            inputsDisabled.value = false
            return
        }
        btn.classList.add("mgs-loading")
        try {
            if (isEditMode.value) await updateConnectionData()
            else await addNewConnectionData()
        } finally {
            btn.disabled = false
            inputsDisabled.value = false
            btn.classList.remove("mgs-loading")
        }
    }

    async function addNewConnectionData() {
        const notifyID = notify.info(__("Please Waite"), __("Information submitted,do not close the page..."), -1)
        const postData = {action: "magical_create_connection", ...connectionData.value, nonce: MagicalConnection.nonce}
        try {
            const {data} = await axios.post(MagicalConnection.ajax_url, postData, {headers: AJAX_HEADERS})
            if (data.success) {
                notify.update(notifyID, "success", __("Created Successfully"), __("your connection created successfully."), 5000)
                router.push({name: "connection-list"})
            } else {
                notify.update(notifyID, "error", __("Creation Failed"), __("An error occurred while creating connection. Please try again."), 5000)
            }
        } catch {
            notify.update(notifyID, "error", __("Creation Failed"), __("An error occurred while creating connection. Please try again."), 5000)
        }
    }

    async function updateConnectionData() {
        const notifyID = notify.info(__("Please Waite"), __("Information submitted,do not close the page..."), -1)
        const postData = {
            action: "magical_update_connection", ...connectionData.value,
            connection_id: route.params.id, nonce: MagicalConnection.nonce
        }
        try {
            const {data} = await axios.post(MagicalConnection.ajax_url, postData, {headers: AJAX_HEADERS})
            if (data.success) {
                notify.update(notifyID, "success", __("Update Successfully"), __("your connection data updated successfully."), 5000)
                router.push({name: "connection-list"})
            } else {
                notify.update(notifyID, "error", __("Update Failed"), __("An error occurred while updating information. Please try again."), 5000)
            }
        } catch {
            notify.update(notifyID, "error", __("Update Failed"), __("An error occurred while updating information. Please try again."), 5000)
        }
    }

    async function startConnectionTest(tag) {
        const btn = tag.target
        btn.disabled = true
        inputsDisabled.value = true
        resetConnectionTestStatus()
        if (connectionData.value.protocol === "FTP" && !ftpFormValidation()) {
            btn.disabled = false;
            inputsDisabled.value = false;
            return
        }
        if (connectionData.value.protocol === "HTTP" && !httpFormValidation()) {
            btn.disabled = false;
            inputsDisabled.value = false;
            return
        }
        try {
            await testConnectionToServer()
        } finally {
            btn.disabled = false;
            inputsDisabled.value = false
        }
    }

    async function testConnectionToServer() {
        const postData = {action: "magical_testing_connection_to_server", connection_data: connectionData.value}
        connectionStatus.value.steps.connectionStep.pending = false
        connectionStatus.value.steps.connectionStep.checking = true
        const {data} = await axios.post(MagicalConnection.ajax_url, postData, {headers: AJAX_HEADERS})
        if (data.success) {
            connectionStatus.value.steps.connectionStep.checking = false
            if (data.data.connectionStep === "success") {
                connectionStatus.value.steps.connectionStep.success = true
                await testUploadToServer()
            } else {
                connectionStatus.value.steps.connectionStep.interrupted = true
            }
        }
    }

    async function testUploadToServer() {
        const postData = {action: "magical_testing_upload_to_server", connection_data: connectionData.value}
        connectionStatus.value.steps.uploadingStep.pending = false
        connectionStatus.value.steps.uploadingStep.checking = true
        const {data} = await axios.post(MagicalConnection.ajax_url, postData, {headers: AJAX_HEADERS})
        if (data.status) {
            connectionStatus.value.steps.uploadingStep.checking = false
            if (data.data.uploadStep === "success") {
                connectionStatus.value.steps.uploadingStep.success = true
                await finalTestConnection()
            } else {
                connectionStatus.value.steps.uploadingStep.interrupted = true
            }
        }
    }

    async function finalTestConnection() {
        const postData = {action: "magical_testing_final_step", connection_data: connectionData.value}
        connectionStatus.value.steps.finalStep.pending = false
        connectionStatus.value.steps.finalStep.checking = true
        const {data} = await axios.post(MagicalConnection.ajax_url, postData, {headers: AJAX_HEADERS})
        if (data.status) {
            connectionStatus.value.steps.finalStep.checking = false
            if (data.data.finalStep === "success") {
                connectionStatus.value.steps.finalStep.success = true
                connectionStatus.value.success = true
            } else {
                connectionStatus.value.steps.finalStep.interrupted = true
            }
        }
    }

    async function mgsConnectionFormValidation() {
        if (connectionData.value.name.length <= 3) {
            notify.error(__("Connection Name"), __("Please enter a title for your connection."), 5000)
            return false
        }
        if (connectionData.value.protocol === "FTP" && !ftpFormValidation()) return false
        if (connectionData.value.protocol === "HTTP" && !httpFormValidation()) return false
        return true
    }

    function ftpFormValidation() {
        const ipRegex = /^(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)\.){3}(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)$/
        if (!ipRegex.test(connectionData.value.host)) {
            notify.error(__("Server Address"), __("Please enter your server IP address."), 5000);
            return false
        }
        if (connectionData.value.username.length <= 0) {
            notify.error(__("User Name"), __("Please enter your username to login to the server."), 5000);
            return false
        }
        if (connectionData.value.password.length <= 0) {
            notify.error(__("Password"), __("Please enter your password to login to the server."), 5000);
            return false
        }
        if (connectionData.value.base_path.length <= 0) connectionData.value.base_path = "/"

        if (!isValidUrl(connectionData.value.domain)) {
            notify.error(__("Domain"), __("Please enter valid domain to login to the server."), 5000);
            return false
        }
        return true
    }

    function isValidUrl(url) {
        try {
            const parsed = new URL(url)
            return ((parsed.protocol === 'http:' || parsed.protocol === 'https:') && parsed.hostname !== '')
        } catch {
            return false
        }
    }

    function httpFormValidation() {
        if (!isValidUrl(connectionData.value.domain)) {
            notify.error(__("Domain"), __("Please enter valid domain to login to the server."), 5000);
            return false
        }
        return true
    }

    function mgsCancelEditConnectionInfo() {
        router.push({name: "connection-list"})
    }

    return {
        inputsDisabled, connectionData, connectionStatus, passive_mode, isEditMode,
        resetFormData, resetConnectionTestStatus, getConnectionInfo,
        saveConnectionBtn, startConnectionTest,
        mgsConnectionFormValidation, ftpFormValidation, httpFormValidation,
        mgsCancelEditConnectionInfo
    }
}