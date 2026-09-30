<div align="center">

# 🔌 Magical Connection

**Transfer WordPress media files to a remote server via FTP or HTTP — and restore them back with one click.**

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue?logo=wordpress)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?logo=php)](https://www.php.net/)
[![Vue](https://img.shields.io/badge/Vue-3-4fc08d?logo=vue.js)](https://vuejs.org/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-blue)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Vite](https://img.shields.io/badge/Vite-5-646cff?logo=vite)](https://vitejs.dev/)

[Features](#-features) • [Installation](#-installation) • [Usage](#-usage) • [Development](#-development) • [FAQ](#-faq)

</div>

---

## ✨ What Is This?

**Magical Connection** is a WordPress plugin that lets you **offload your media library** to a remote server — via **FTP** or a **self-hosted HTTP API** — and bring files back whenever you need. Every database reference to a transferred file is **automatically updated**, so your posts, pages, and widgets keep working without broken links.

Built with **Vue 3** for a fast, modern admin experience.

--- 

## 🎯 Features

- 🔁 **Two connection types** — FTP and HTTP (self-hosted API).
- 📦 **Automatic reference updates** — post content, custom fields, widgets, theme settings.
- 🔍 **File Scanner** — analyze your media library by type and size.
- ⏰ **Cron Job automation** — schedule transfers with configurable intervals and retries.
- ↩️ **One-click restore** — bring any transferred file back to WordPress.
- 🔐 **Role-based access** — control which user roles can transfer files.
- 🌐 **Multi-server support** — manage unlimited FTP / HTTP connections.
- 🎨 **Modern UI** — built with Vue 3, Vue Router, and Vite.
- 🌍 **i18n ready** — fully translatable.
- 🛡️ **Security-first** — nonces, permission checks, SFTP/FTPS and HTTPS friendly.

---

## 📋 Requirements

| Component | Minimum |
|-----------|---------|
| WordPress | 5.8 |
| PHP | 7.4 |
| Node.js *(for development)* | 18 |
| `allow_url_fopen` | enabled *(HTTP transfers only)* |

---

## 🚀 Installation

### From WordPress Admin

1. Go to **Plugins → Add New**.
2. Search for **Magical Connection**.
3. Click **Install** → **Activate**.
4. Go to **Magical Connection** in the admin menu and create your first connection.

### Manual

1. Download the latest release.
2. Upload the ZIP via **Plugins → Add New → Upload Plugin**.
3. Activate and configure.

### Building from source

```bash
git clone https://github.com/your-username/magical-connection.git
cd magical-connection/vue-app
npm install
npm run build
```

---

## 🧭 Usage

### 1. Create a connection

Go to **Magical Connection → Your Connections → Add New Connection**.

**FTP**
- Server IP, port, username, password, base path, domain
- Enable passive mode if behind a firewall

**HTTP**
- Domain of your destination server
- Copy the auto-generated **API Key**
- Download and install the HTTP API on your remote server (see below)

### 2. Install the HTTP API (HTTP connections only)

1. Click **Download API** in the connection form.
2. Upload `http-api.zip` to your remote server's `public_html/`.
3. Extract it and open `http-api/index.php`.
4. Paste your API Key, save, set permissions to `644`.

> 💡 **No external service.** The API runs on **your own server**. This plugin never contacts our servers or any third-party service.

### 3. Transfer a file

1. Go to **Media → Library**.
2. Open any attachment.
3. Click **Transfer to Server**.
4. Select a server, review references, click **Start Transfer**.

### 4. Restore a file

Open a transferred file and click **Restore from Server**. Same process, in reverse.

---

## 🛠️ Development

### Project structure

```
magical-connection/
├── includes/              PHP backend
│   ├── Core/
│   ├── Database/
│   ├── Functions/
│   ├── Modules/
│   ├── Support/
│   ├── Views/
│   ├── enqueue-scripts.php
│   └── enqueue-media-dialog.php
├── resources/
│   └── http-api/          Self-hosted API source
├── vue-app/               Vue 3 frontend
│   ├── src/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── views/
│   │   ├── router/
│   │   └── main.js
│   ├── vite.config.js
│   └── package.json
├── magical-connection.php Main plugin file
├── readme.txt             WordPress.org readme
└── readme-api.txt         HTTP API notes
```

### Available scripts

Inside `vue-app/`:

| Command | Description |
|---------|-------------|
| `npm install` | Install dependencies |
| `npm run build` | Production build → `dist/` |
| `npm run dev` | Watch mode for development |

### Tech stack

- **Vue 3** (Composition API + `<script setup>`)
- **Vue Router 4** (hash history)
- **Axios** with global interceptors for loading state
- **Vite 5** for bundling
- **Font Awesome Free** for icons

---

## ❓ FAQ

**Does this plugin send data to an external server?**
No. The plugin only talks to (1) your own WordPress site and (2) the remote servers **you** configure. There is no telemetry, no analytics, and no phone-home.

**Can I use it without FTP?**
Yes. Use the HTTP connection type — it uses a self-hosted PHP API that you install on your own server.

**Will my posts break after a transfer?**
No. All database references are updated automatically during the transfer.

**Can I transfer files in bulk?**
Yes. Use the File Scanner together with the Cron Job feature.

**Is the HTTP API required?**
No. It's only needed for HTTP connections. FTP works without it.

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/my-feature`.
3. Commit your changes: `git commit -m "Add my feature"`.
4. Push: `git push origin feature/my-feature`.
5. Open a Pull Request.

Please follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/) for PHP and [Vue Style Guide](https://vuejs.org/style-guide/) for JavaScript.

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 or later**.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

See [LICENSE](LICENSE) for the full text.

---

## 🙏 Credits

- [Vue.js](https://vuejs.org/) — MIT
- [Vue Router](https://router.vuejs.org/) — MIT
- [Axios](https://axios-http.com/) — MIT
- [Vite](https://vitejs.dev/) — MIT
- [Font Awesome Free](https://fontawesome.com/) — CC BY 4.0 / SIL OFL 1.1 / MIT

---

## 📬 Support

- 🐛 **Bug reports:** [GitHub Issues](https://github.com/your-username/magical-connection/issues)
- 💬 **Discussions:** [GitHub Discussions](https://github.com/your-username/magical-connection/discussions)
- 📧 **Email:** your-email@example.com

---

<div align="center">

**Made with ❤️ for the WordPress community**

⭐ If this plugin helps you, consider giving it a star!

</div>