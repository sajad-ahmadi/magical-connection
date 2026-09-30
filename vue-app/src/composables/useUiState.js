import { ref } from 'vue'

export function useUiState() {
    function show() {
        mgsShowContent.value = true
    }

    function hide() {
        mgsShowContent.value = false
    }

    let mgsShowContent;
    return { mgsShowContent, show, hide }
}