import { ref } from 'vue'
import { MagicalConnection, AJAX_HEADERS , __ } from '../config'
let startScanDate = null;
let scanCounter;

function createEmptyScanningData() {
    return {
        elapsedTime: '00::00:00',
        scannedFiles: '-',
        scannedVolume: 0 ,
        filesFound: '-',
        scanPercent: '0%',
        currentPath: '/wp-content/uploads',
        filePerSec: 0,
        estimatedTimeRemaining: '~'
    }
}

function createEmptyScanObject() {
    return {
        scanning: false,
        showScanData: false,
        scanningData: createEmptyScanningData() ,
    }
}

function createEmptySummary() {
    const empty = () => ({ count: '0', percentage: '0%' })
    return {
        document: empty(),
        image:    empty(),
        audio:    empty(),
        video:    empty(),
        other:    empty()
    }
}

export function useFileScanner() {
    const uuid = ref('')
    const scanStats = ref({
        filesPerSecond: 0,
        fileCount: 0,
        scanStartTime: null
    })
    const scanTimer = ref(null)
    const scannerRequestController = ref(null)

    const fileScannerObject = ref(createEmptyScanObject())
    const scanSummery = ref(createEmptySummary())
    const scannedFilesDetailsByType = ref([])
    const extension_total = ref({})


    async function getScanSummeryData() {
        const postData = {
            action: 'magical_scanner_data',
            nonce: MagicalConnection.nonce
        }

        try {
            const response = await axios.post(MagicalConnection.ajax_url, postData, {
                headers: AJAX_HEADERS
            })

            if (response.data.success) {
                updateSummaryFromSource(response.data.data.summary)
                scannedFilesDetailsByType.value = response.data.data.extension
                extension_total.value = response.data.data.extension_total
            }
        } catch (error) {
            console.log(error)
        }
    }

    function updateSummaryFromSource(newData) {
        newData.forEach((item) => {
            if (scanSummery.value.hasOwnProperty(item.type)) {
                scanSummery.value[item.type].count = item.count
                scanSummery.value[item.type].percentage = item.percentage + '%'
            }
        })
    }

    async function getCurrentScanData() {
        // ⭐ اگه controller قبلی هست، abort کن
        if (scannerRequestController.value != null) {
            scannerRequestController.value.abort()
        }

        scannerRequestController.value = new AbortController()

        const formData = new FormData()
        formData.append('action', 'magical_scanner_real_time')
        formData.append('nonce', MagicalConnection.nonce)
        formData.append('uuid', uuid.value)

        await transferAttachment(formData)
    }


    async function transferAttachment(formData) {
        startScanCounter()

        try {
            const response = await fetch(MagicalConnection.ajax_url, {
                method: 'POST',
                body: formData,
                signal: scannerRequestController.value.signal   // ⭐ این خط
            })

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`)
            }

            const reader = response.body.getReader()
            const decoder = new TextDecoder()

            let buffer = ''

            while (true) {
                const { value, done } = await reader.read()
                if (done) break

                buffer += decoder.decode(value, { stream: true })

                const events = buffer.split('\n\n')
                buffer = events.pop()

                for (const event of events) {
                    handleStreamEvent(event)
                }
            }
        } catch (error) {
            // ⭐ اگه کاربر کنسل کرد، خطا رو نادیده بگیر
            if (error.name === 'AbortError') {
                console.log('اسکن توسط کاربر متوقف شد')
            } else {
                console.error('خطا در اسکن:', error)
            }
        } finally {
            stopScanCounter()
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

        switch (eventName) {
            case 'log':
                handleLogEvent(data)
                break
            case 'error':
                stopScanCounter()
                console.error(data)
                break
            case 'finish':
                stopScanCounter()
                break
        }
    }


    function handleLogEvent(data) {
        if (data.event_method !== 'scan') return

        if (data.status === 'started') {
            fileScannerObject.value.showScanData = true
            uuid.value = data.uuid
            fileScannerObject.value.scanningData.scannedFiles = 0
            fileScannerObject.value.scanningData.scannedVolume = 0
            scanStats.value.scanStartTime = Date.now()
            startScanDate = new Date();
            counter();
        } else if (data.status === 'scanning') {
            scanStats.value.fileCount++

            const info = data.data
            fileScannerObject.value.scanningData.scannedVolume += info.file.size
            const totalFiles = parseInt(info.total) || 0
            const scannedFiles =
                parseInt(fileScannerObject.value.scanningData.scannedFiles) + 1

            fileScannerObject.value.scanningData.filesFound = totalFiles
            fileScannerObject.value.scanningData.scannedFiles = scannedFiles

            if (totalFiles > 0) {
                const percent = Math.floor((scannedFiles / totalFiles) * 100)
                fileScannerObject.value.scanningData.scanPercent = `${percent}%`
            }

            fileScannerObject.value.scanningData.currentPath =
                truncateMiddle(info.file.relative_path, 70)
        } else if (data.status === 'complete') {
            fileScannerObject.value.scanningData.filePerSec = 0;
            fileScannerObject.value.scanningData.estimatedTimeRemaining = '0';
            stopScanCounter()
            if (scanCounter != null) {
                clearInterval(scanCounter)
            }
        }
    }

    function counter() {
        scanCounter = setInterval( function () {
            const now = new Date()
            fileScannerObject.value.scanningData.elapsedTime = getTimeDifference( startScanDate , now )
        } , 1000 )
    }

    function getTimeDifference(start, end) {    const diff = Math.abs(end - start)
        const totalSeconds = Math.floor(diff / 1000)
        const hours = Math.floor(totalSeconds / 3600)
        const minutes = Math.floor((totalSeconds % 3600) / 60)
        const seconds = totalSeconds % 60
        return [String(hours).padStart(2, '0'), String(minutes).padStart(2, '0'),String(seconds).padStart(2, '0')].join(':')}

    function startScanCounter() {
        if (scanTimer.value) return

        scanTimer.value = setInterval(() => {
            scanStats.value.filesPerSecond = scanStats.value.fileCount
            scanStats.value.fileCount = 0
            fileScannerObject.value.scanningData.filePerSec = scanStats.value.filesPerSecond

            const totalFiles = parseInt(fileScannerObject.value.scanningData.filesFound) || 0
            const scannedFiles = parseInt(fileScannerObject.value.scanningData.scannedFiles) || 0

            if (totalFiles > 0 && scannedFiles > 0 && scannedFiles < totalFiles && scanStats.value.scanStartTime) {
                const elapsedSeconds = (Date.now() - scanStats.value.scanStartTime) / 1000
                if (elapsedSeconds > 0) {
                    const avgPerSec = scannedFiles / elapsedSeconds
                    const remainingFiles = totalFiles - scannedFiles
                    const remainingSeconds = Math.ceil(remainingFiles / avgPerSec)
                    fileScannerObject.value.scanningData.estimatedTimeRemaining =
                        formatRemainingTime(remainingSeconds)
                }
            }

            if (totalFiles > 0 && scannedFiles >= totalFiles) {
                fileScannerObject.value.scanningData.estimatedTimeRemaining = '0' + ' ' + __('Sec')
            }
        }, 1000)
    }

    function stopScanCounter() {
        if (scanTimer.value) {
            clearInterval(scanTimer.value)
            scanTimer.value = null
        }
    }


    function formatRemainingTime(seconds) {
        if (seconds <= 0) return '0' + ' ' + __('Sec')
        if (seconds < 60) return seconds +' ' + __('Sec')

        const minutes = Math.floor(seconds / 60)
        const remainingSeconds = seconds % 60

        if (remainingSeconds === 0) return minutes + ' ' + __('Min')
        return minutes + ' ' + __('Min') + remainingSeconds +' ' + __('Sec')
    }

    function truncateMiddle(str, maxLength) {
        if (str.length <= maxLength) return str

        const available = maxLength - 3
        const leftLength = Math.ceil(available / 2)
        const rightLength = Math.floor(available / 2)

        return `${str.substring(0, leftLength)}...${str.substring(str.length - rightLength)}`
    }


    async function startNewScanBtn(tag) {
        fileScannerObject.value = createEmptyScanObject()

        const btn = tag.target
        btn.disabled = true
        btn.classList.add('mgs-loading')

        await getCurrentScanData()

        btn.disabled = false
        btn.classList.remove('mgs-loading')
    }

    function stopScanBtn() {
        if (scannerRequestController.value != null) {
            scannerRequestController.value.abort()
            scannerRequestController.value = null
        }

        fileScannerObject.value.showScanData = false
        fileScannerObject.value.scanning = false
        stopScanCounter()
    }

    return {
        fileScannerObject,
        scanSummery,
        scannedFilesDetailsByType,
        extension_total,
        getScanSummeryData,
        getCurrentScanData,
        startNewScanBtn,
        stopScanBtn
    }
}