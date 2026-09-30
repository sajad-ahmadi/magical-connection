import { ref } from 'vue'

export function useFaq() {
    const openItemId = ref(null)

    function toggle(itemId) {
        openItemId.value = openItemId.value === itemId ? null : itemId
    }

    function closeAll() {
        openItemId.value = null
    }

    function openById(itemId) {
        openItemId.value = itemId
    }

    return { openItemId, toggle, closeAll, openById }
}