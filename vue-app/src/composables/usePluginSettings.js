import { ref } from 'vue'
import { MagicalConnection, AJAX_HEADERS , __ } from '../config'
import { useNotify } from './useNotify'

export function usePluginSettings() {
    const notify = useNotify()

    const settingActiveTab = ref('media')
    const pluginSettings = ref({
        auto_transfer: {
            enabled: false,
            connection_id: null
        },
        permissions: {
            allowed_roles: []
        }
    })
    const wpRoles = ref({})


    async function getAllSettings() {
        const postData = {
            action: 'magical_get_settings',
            nonce: MagicalConnection.nonce,
            group_key: ['auto_transfer', 'permissions']
        }

        try {
            const { data } = await axios.post(
                MagicalConnection.ajax_url,
                postData,
                { headers: AJAX_HEADERS }
            )

            if (data.success) {
                pluginSettings.value = data.data
            }
        } catch (error) {
            console.log(error)
        }
    }


    async function getWpRoles() {
        const postData = {
            action: 'magical_get_available_roles',
            nonce: MagicalConnection.nonce
        }

        try {
            const { data } = await axios.post(
                MagicalConnection.ajax_url,
                postData,
                { headers: AJAX_HEADERS }
            )

            if (data.success) {
                wpRoles.value = data.data
            }
        } catch (error) {
            console.log(error)
        }
    }


    function settingTabBtn(key) {
        settingActiveTab.value = key
    }


    function roleChanges(key, event) {
        const isChecked = event.target.checked
        const list = pluginSettings.value?.permissions?.allowed_roles ?? []
        const index = list.indexOf(key)

        if (isChecked && index < 0) {
            list.push(key)
        } else if (!isChecked && index >= 0) {
            list.splice(index, 1)
        }
    }


    function roleExists(key) {
        return (pluginSettings.value?.permissions?.allowed_roles ?? []).includes(key)
    }

    function savePluginSettings(event) {
        const btn = event.target
        btn.disabled = true

        const notifyID = notify.info(__('Saving'), __('please waite...'), -1)

        const postData = {
            action: 'magical_save_settings',
            nonce: MagicalConnection.nonce,
            group_setting: pluginSettings.value
        }

        axios.post(MagicalConnection.ajax_url, postData, { headers: AJAX_HEADERS })
            .then(({ data }) => {
                if (data.success) {
                    notify.update(notifyID, 'success', __('Done'), __('your changes saved successfully'), 5000)
                } else {
                    notify.update(notifyID, 'error', __('Failed'), __('An error occurred while saving. Please try again.'), 5000)
                }
                btn.disabled = false
            })
            .catch((error) => {
                notify.update(notifyID, 'error', __('Failed'), __('An error occurred while saving. Please try again.'), 5000)
                btn.disabled = false
            })
    }

    return {
        settingActiveTab,
        pluginSettings,
        wpRoles,
        getAllSettings,
        getWpRoles,
        settingTabBtn,
        roleChanges,
        roleExists,
        savePluginSettings
    }
}