import { createApp } from "vue"
import axios from "axios"
import App from "./App.vue"
import router from "./router"

window.axios = axios
axios.defaults.headers.post["Content-Type"] =
    "application/x-www-form-urlencoded; charset=UTF-8"

import './css/main.css';
import '../src/css/icon-pack.css';
import '../src/css/variables.css';
import './css/notification.css';
import '../src/css/connection-list.css';
import '../src/css/cron-job.css';
import '../src/css/faq.css';
import '../src/css/file-scanner.css';
import '../src/css/edit-add-connection.css';
import '../src/css/plugin-settings.css';

const app = createApp(App)
app.use(router)
app.mount("#mg-main-setting-wrapper")