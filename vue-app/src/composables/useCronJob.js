import { ref, computed } from 'vue'
import { MagicalConnection, AJAX_HEADERS , __ } from '../config'
import { useNotify } from './useNotify'

export function useCronJob() {
    const notify = useNotify()


    const cronJobData = ref({
        enabled: false,
        files_per_run: null,
        interval: null,
        max_retries: null,
        method: null,
        hasPendingTransfer: false
    })

    const cronStates = ref({
        interval: null,
        nextRun: null,
        nextRunDayLabel: null,
        leftToNextRun: null,
        maxRuns: 10,
        lastRun: null,
        percent: null
    })

    const cronFormData = ref({
        enabled: false,
        files_per_run: null,
        interval: null,
        max_retries: null,
        method: null
    })

    const timingList = ref({})
    const next5Runs = ref([])
    const timerId = ref(null)
    const cronInterval = ref('')

    const enabledModel = computed({
        get: () => cronFormData.value.enabled === true,
        set: (val) => { cronFormData.value.enabled = val }
    })

    async function getCronJobData() {
        const postData = {
            action: 'magical_get_setting_cron_job',
            nonce: MagicalConnection.nonce
        }

        try {
            const { data } = await axios.post(
                MagicalConnection.ajax_url,
                postData,
                { headers: AJAX_HEADERS }
            )

            if (data.success) {
                timingList.value = data.data.list_time

                const cron = { ...data.data.cron }
                cron.enabled =
                    cron.enabled === true ||
                    cron.enabled === 'true' ||
                    cron.enabled === 1 ||
                    cron.enabled === '1'

                cronFormData.value = { ...cron }
                cronJobData.value = { ...cron }
                cronInterval.value = timingList.value[cronJobData.value.interval]?.interval ?? 0
                cronJobData.value.hasPendingTransfer = data.data.has_pending_transfer

                if (cronJobData.value.hasPendingTransfer) {
                    startCountdown()
                }
            }
        } catch (error) {
            console.log(error)
        }
    }


    function updateData() {
        cronStates.value.interval = formatDuration(cronInterval.value)
        getSecondsUntilNextRun(cronInterval.value)
        getNextRunText(cronInterval.value)
        updateLastRun()
        calculateNextRuns()
        getProgressPercent()
    }


    function formatDuration(totalSecs, showSecond = false) {
        const m = Math.floor(totalSecs / 60)
        const s = totalSecs % 60
        if (showSecond) return `${m}m ${s}s`
        return `${m}`
    }

    function getSecondsUntilNextRun(intervalSeconds) {
        if (!intervalSeconds) return 0

        const now = new Date()
        const currentTotalSeconds =
            now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()

        const nextRunTotalSeconds =
            Math.ceil(currentTotalSeconds / intervalSeconds) * intervalSeconds

        let secondsLeft = nextRunTotalSeconds - currentTotalSeconds
        cronStates.value.leftToNextRun = secondsLeft

        if (secondsLeft <= 0) secondsLeft += intervalSeconds

        cronStates.value.nextRun = formatDuration(secondsLeft, true)
        return secondsLeft
    }

    function getNextRunText(intervalSeconds) {
        const secondsLeft = getSecondsUntilNextRun(intervalSeconds)
        const now = new Date()
        const nextRunDate = new Date(now.getTime() + secondsLeft * 1000)

        const hours = String(nextRunDate.getHours()).padStart(2, '0')
        const minutes = String(nextRunDate.getMinutes()).padStart(2, '0')
        const timeString = `${hours}:${minutes}`

        const todayMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        const nextRunMidnight = new Date(
            nextRunDate.getFullYear(),
            nextRunDate.getMonth(),
            nextRunDate.getDate()
        )
        const diffDays = Math.round(
            (nextRunMidnight - todayMidnight) / (1000 * 60 * 60 * 24)
        )

        let dayLabel = ''
        if (diffDays === 0) dayLabel = __('Today');
        else if (diffDays === 1) dayLabel = __('Tomorrow')
        else if (diffDays >= 3 && diffDays <= 7) dayLabel = diffDays + ' ' + __('Days Left')
        else
            dayLabel = nextRunDate.toLocaleDateString('fa-IR', {
                day: 'numeric',
                month: 'long'
            })

        cronStates.value.nextRunDayLabel = `${dayLabel} ${timeString}`
        return `${dayLabel} ${timeString}`
    }


    async function startCountdown() {
        const tick = async () => {
            updateData()

            if (cronStates.value.leftToNextRun <= 1) {
                await getCronJobData()
                if (!cronJobData.value.hasPendingTransfer) {
                    clearTimeout(timerId.value)
                }
            } else {
                timerId.value = setTimeout(tick, 1000)
            }
        }

        timerId.value = setTimeout(tick, 1000)
    }

    function calculateNextRuns() {
        let intervalSeconds = cronStates.value.interval
        if (intervalSeconds < 60) intervalSeconds = intervalSeconds * 60

        const now = new Date()
        const currentTotalSeconds =
            now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()

        const nextRunTotalSeconds =
            Math.ceil(currentTotalSeconds / intervalSeconds) * intervalSeconds

        const nextRunDate = new Date(now)
        nextRunDate.setHours(0, 0, 0, 0)
        nextRunDate.setSeconds(nextRunTotalSeconds)

        if (nextRunDate.getTime() <= Date.now()) {
            nextRunDate.setTime(nextRunDate.getTime() + intervalSeconds * 1000)
        }

        next5Runs.value = []
        for (let i = 0; i < 5; i++) {
            const futureDate = new Date(nextRunDate.getTime() + i * intervalSeconds * 1000)
            next5Runs.value.push({ runIn: formatTimeWithSeconds(futureDate) })
        }
    }

    function updateLastRun() {
        let intervalSeconds = cronStates.value.interval
        if (intervalSeconds < 60) intervalSeconds = intervalSeconds * 60

        const now = new Date()
        const currentTotalSeconds =
            now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()

        const lastRunTotalSeconds =
            Math.floor(currentTotalSeconds / intervalSeconds) * intervalSeconds

        const lastRunDate = new Date(now)
        lastRunDate.setHours(0, 0, 0, 0)
        lastRunDate.setSeconds(lastRunTotalSeconds)

        const todayMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        const lastRunMidnight = new Date(
            lastRunDate.getFullYear(),
            lastRunDate.getMonth(),
            lastRunDate.getDate()
        )

        const diffDays = Math.round(
            (todayMidnight - lastRunMidnight) / (1000 * 60 * 60 * 24)
        )

        let dayLabel
        if (diffDays === 0) dayLabel = __('Today')
        else if (diffDays === 1) dayLabel = __('Yesterday')
        else if (diffDays >= 2 && diffDays <= 7) dayLabel = diffDays +' '+ __('days ago')
        else
            dayLabel = lastRunDate.toLocaleDateString('fa-IR', {
                day: 'numeric',
                month: 'long'
            })

        const timeString = formatTimeWithSeconds(lastRunDate).substring(0, 5)
        cronStates.value.lastRun = `${dayLabel} ${timeString}`
    }

    function formatTimeWithSeconds(date) {
        const h = String(date.getHours()).padStart(2, '0')
        const m = String(date.getMinutes()).padStart(2, '0')
        const s = String(date.getSeconds()).padStart(2, '0')
        return `${h}:${m}:${s}`
    }

    async function saveSettingsBtn(event) {
        const btn = event.target
        btn.disabled = true
        notify.closeAll()

        const notifyId = notify.info(__('Saving Settings') , __('please waite') , -1)

        const postData = {
            action: 'magical_save_setting_cron_job',
            nonce: MagicalConnection.nonce,
            cron: {
                ...cronFormData.value,
                enabled: cronFormData.value.enabled ? 1 : 0
            }
        }

        try {
            const { data } = await axios.post(
                MagicalConnection.ajax_url,
                postData,
                { headers: AJAX_HEADERS }
            )

            if (data.success) {
                getCronJobData()
                notify.update(notifyId, 'success', __('Done'), __('your setting saved'), 5000)
            } else {
                notify.update(notifyId, 'error', __('Failed'), __('Saving Data Failed'), 5000)
            }
            btn.disabled = false
        } catch (error) {
            notify.update(notifyId, 'error', __('Failed' ,), __('Saving Data Failed'), 5000)
            btn.disabled = false
        }
    }

    async function runCronManuallyBtn(event) {
        const btn = event.target
        btn.disabled = true
        notify.closeAll()

        const notifyId = notify.info(__('Saving Data'), __('Saving data, please wait.'), -1)

        const postData = {
            action: 'magical_cron_job_run',
            nonce: MagicalConnection.nonce
        }

        try {
            const { data } = await axios.post(
                MagicalConnection.ajax_url,
                postData,
                { headers: AJAX_HEADERS }
            )

            if (data.success) {
                notify.update(notifyId, 'success', __('Done'), __('Cron job executed successfully.'), 5000)
                cronFormData.value = structuredClone(data.data.cron)
                cronJobData.value = structuredClone(data.data.cron)
                cronInterval.value = timingList.value[cronJobData.value.interval]?.interval ?? 0
                cronJobData.value.hasPendingTransfer = data.data.has_pending_transfer

                if (cronJobData.value.hasPendingTransfer) {
                    startCountdown()
                }
            } else {
                notify.update(notifyId, 'error', __('Failed'), __('Cron job execution failed.'), 5000)
            }
            btn.disabled = false
        } catch (error) {
            notify.update(notifyId, 'error', __('Failed'), __('Cron job execution failed.'), 5000)
            btn.disabled = false
        }
    }

    function getProgressPercent() {
        const elapsed = cronStates.value.interval - cronStates.value.leftToNextRun
        const percent = (elapsed / cronStates.value.interval) * 100
        cronStates.value.percent = Math.min(100, Math.max(0, percent))
    }

    return {
        cronJobData,
        cronStates,
        cronFormData,
        timingList,
        next5Runs,
        enabledModel,
        getCronJobData,
        saveSettingsBtn,
        runCronManuallyBtn,
    }
}