import { ref } from 'vue'
import { MagicalConnection, AJAX_HEADERS, __ } from '../config'
import { useNotify } from '../composables/useNotify'
import { useTools } from '../composables/useTools'

const show_dialog_transfer = ref(false)
const list_hosts = ref([])
const attachment_info = ref({})
const restore_file = ref(false)
const dialogSelectedServer = ref(null)

const referenceReview = ref({
    reviewed: false,
    reviewStarted: false,
    referenceFound: 0
})

const fileTransportDetails = ref({
    transferStarted: false,
    transferFinished: false,
    doneCount: 0
})

const referenceFiles = ref([])

const RING_CIRC = 106.8

export function useMediaDialog() {
    const notify = useNotify()
    const { formatBytes, toFa } = useTools()

    function openDialog(attachment_id) {
        const data = {
            action: 'magical_media_info',
            nonce: MagicalConnection.nonce,
            attachment_id: attachment_id
        }

        axios.post(MagicalConnection.ajax_url, data, { headers: AJAX_HEADERS })
            .then(res => {
                const result = res.data.data
                list_hosts.value = Array.isArray(result.hosts) ? result.hosts : []
                referenceFiles.value = []

                Object.entries(result.media.sizes).forEach(([key, item]) => {
                    referenceFiles.value.push({ key, ...item, status: 'queue' })
                })

                attachment_info.value = result.media
            })
            .catch(error => console.error(error))

        show_dialog_transfer.value = true
    }

    function loadingHosts() {
        return !(Array.isArray(list_hosts.value) && list_hosts.value.length > 0)
    }

    function loadingMedia() {
        return !(attachment_info.value && attachment_info.value.id)
    }

    function startTransfer(attachment_id) {
        if (restore_file.value) {
            restoreFile(attachment_id)
            return
        }


        if (dialogSelectedServer.value == null) {
            console.log( notify )
            notify.warning(
                __('Select the Server'),
                __('Select your server to start the transfer.'),
                5000
            )
            return
        }

        startReferenceReviewBtn(attachment_id)

        const formData = new FormData()
        formData.append('action', 'magical_media_transfer')
        formData.append('nonce', MagicalConnection.nonce)
        formData.append('attachment_id', parseInt(attachment_id))
        formData.append('connection_id', parseInt(dialogSelectedServer.value))

        transferAttachment(formData)
    }

    function restoreFile(attachment_id) {
        const data = {
            action: 'magical_media_restore',
            nonce: MagicalConnection.nonce,
            attachment_id: attachment_id
        }

        axios.post(MagicalConnection.ajax_url, data, { headers: AJAX_HEADERS })
            .then(res => window.location.reload())
            .catch(error => console.error(error))
    }

    async function transferAttachment(formData) {
        const response = await fetch(MagicalConnection.ajax_url, {
            method: 'POST',
            body: formData
        })

        if (!response.ok) throw new Error(`HTTP ${response.status}`)

        const reader = response.body.getReader()
        const decoder = new TextDecoder()
        let buffer = ''

        while (true) {
            const { value, done } = await reader.read()
            if (done) break
            buffer += decoder.decode(value, { stream: true })
            const events = buffer.split('\n\n')
            buffer = events.pop()
            for (const event of events) handleStreamEvent(event)
        }
    }

    function handleStreamEvent(rawEvent) {
        const lines = rawEvent.split('\n')
        let eventName = null
        let data = null

        for (const line of lines) {
            if (line.startsWith('event:')) eventName = line.substring(6).trim()
            if (line.startsWith('data:')) data = line.substring(5).trim()
        }

        if (!data) return

        try {
            data = JSON.parse(data)
        } catch (error) {
            console.error('Invalid SSE data:', data)
            return
        }

        let movedFiles = 0

        switch (eventName) {
            case 'log':
                if (data.event_method === 'start') {
                    fileTransportDetails.value.transferStarted = true
                } else if (data.event_method === 'transfer') {
                    if (data.status === 'uploading') {
                        referenceFiles.value.forEach(item => {
                            const fileName = data.data.path.split(/[\\/]/).pop()
                            if (fileName === item.file) item.status = 'uploading'
                        })
                    } else if (data.status === 'done') {
                        referenceFiles.value.forEach(item => {
                            const fileName = data.data.local_path.split(/[\\/]/).pop()
                            if (fileName === item.file) item.status = 'done'
                            movedFiles += 1
                            updateOverall(movedFiles)
                        })
                    } else if (data.status === 'failed') {
                        referenceFiles.value.forEach(item => {
                            const fileName = data.data.path.split(/[\\/]/).pop()
                            if (fileName === item.file) item.status = 'failed'
                        })
                    }
                }
                break
            case 'error':
                console.error(data)
                break
            case 'finish':
                window.location.reload();
                break
        }
    }

    async function startReferenceReviewBtn(attachment_id) {
        referenceReview.value.reviewed = false
        referenceReview.value.reviewStarted = true
        fileTransportDetails.value.transferFinished = false
        fileTransportDetails.value.transferStarted = false

        const postData = {
            action: 'magical_media_refernce',
            nonce: MagicalConnection.nonce,
            attachment_id: attachment_id
        }

        try {
            const response = await axios.post(
                MagicalConnection.ajax_url,
                postData,
                { headers: AJAX_HEADERS }
            )

            if (response.data.success) {
                referenceReview.value.reviewed = true
                referenceReview.value.referenceFound = response.data.data.found
            }
        } catch (error) {
            console.log(error)
        }
    }

    function updateOverall(doneCount) {
        fileTransportDetails.value.doneCount = doneCount
        if (doneCount === referenceFiles.value.length) {
            fileTransportDetails.value.transferFinished = true
        }
    }

    function selectTransferServer(serverID) {
        dialogSelectedServer.value = serverID
    }

    function closeDialog() {
        show_dialog_transfer.value = false
    }

    return {
        show_dialog_transfer,
        list_hosts,
        attachment_info,
        restore_file,
        dialogSelectedServer,
        referenceReview,
        fileTransportDetails,
        referenceFiles,
        RING_CIRC,
        openDialog,
        loadingHosts,
        loadingMedia,
        startTransfer,
        restoreFile,
        startReferenceReviewBtn,
        updateOverall,
        selectTransferServer,
        closeDialog,
        formatBytes,
        toFa
    }
}