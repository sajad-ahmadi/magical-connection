import { createApp } from "vue"
import axios from "axios"
import MediaDialogApp from "./MediaDialogApp.vue"
import './css/media-dialog.css'

window.axios = axios

const wrapper = document.createElement('div')
wrapper.id = 'mg-main-dialog-wrapper'
document.body.appendChild(wrapper)

const app = createApp(MediaDialogApp)
app.mount('#mg-main-dialog-wrapper')

document.addEventListener('click', function (event) {
    const button = event.target.closest('.magical-transfer-media');
    if (!button) return;

    event.preventDefault();

    const attachmentId = button.dataset.attachmentId;
    if (!attachmentId) return;

    const action = event.target.getAttribute('data-action');

    if (window.MagicalConnectionAppDialog) {
        window.MagicalConnectionAppDialog.list_hosts = [];
        window.MagicalConnectionAppDialog.attachment_info = {};
        window.MagicalConnectionAppDialog.restore_file = (action === 'restore');
        window.MagicalConnectionAppDialog.openDialog(attachmentId);
    }
});