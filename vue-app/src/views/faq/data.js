import {__} from "@/config/index.js";

export const faqItems = [

    {
        id: 'mgs-getting-started',
        title: __('Getting Started with Magical Connection'),
        summary: __('A quick overview of what Magical Connection does and how to set it up.'),
        content: __('Magical Connection is a WordPress plugin that lets you transfer files from your media library to a remote server (via FTP or HTTP) and restore them back whenever needed. It automatically updates all database references, so your posts and pages keep working after every transfer.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Make sure you have access to a remote server.') },
            { title: __('Step 2'), text: __('Go to "Magical Connection" from your WordPress admin menu.') },
            { title: __('Step 3'), text: __('Create your first connection (FTP or HTTP).') },
            { title: __('Step 4'), text: __('Scan your media library to see what files you have.') },
            { title: __('Step 5'), text: __('Transfer your first file from the Media Library.') }
        ],
        delay: 0.1
    },

    {
        id: 'mgs-ftp-setup',
        title: __('How to Create a New FTP Connection'),
        summary: __('Connect your WordPress site to a remote FTP server in 10 steps.'),
        content: __('The FTP protocol allows you to transfer files directly from your WordPress media library to a remote server using standard FTP credentials. Before creating the connection, make sure you have the server IP address, FTP username, FTP password, and the base path (usually "public_html" or "/").'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Magical Connection" → "Your Connections" and click "Add New Connection".') },
            { title: __('Step 2'), text: __('Enter a descriptive name (e.g., "My Download Server").') },
            { title: __('Step 3'), text: __('Select "FTP" from the "Connection Protocol" dropdown.') },
            { title: __('Step 4'), text: __('Enter the "Server Address (IP)" — for example: 192.168.1.1') },
            { title: __('Step 5'), text: __('Enter the "Port" — the default FTP port is 21.') },
            { title: __('Step 6'), text: __('Enter your "Username" and "Password".') },
            { title: __('Step 7'), text: __('In the "PATH" field, enter the destination folder (e.g., public_html/).') },
            { title: __('Step 8'), text: __('Enter the "Domain" of the remote server (e.g., https://example.com).') },
            { title: __('Step 9'), text: __('Enable "Passive Mode" if your server is behind a firewall.') },
            { title: __('Step 10'), text: __('Click "Save Connection".') }
        ],
        delay: 0.2
    },

    {
        id: 'mgs-http-setup',
        title: __('How to Create a New HTTP Connection'),
        summary: __('Connect your WordPress site using the HTTP API — no FTP required.'),
        content: __(
            'The HTTP protocol uses a lightweight API file that must be installed on the destination server before you can transfer files. This method is useful when FTP ports are blocked or when you prefer a simpler setup.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Magical Connection" → "Your Connections" and click "Add New Connection".') },
            { title: __('Step 2'), text: __('Enter a descriptive name (e.g., "My HTTP Server").') },
            { title: __('Step 3'), text: __('Select "HTTP" from the "Connection Protocol" dropdown.') },
            { title: __('Step 4'), text: __('Copy the generated "API Key" — you will need it during API installation.') },
            { title: __('Step 5'), text: __('Enter the "Domain" of the destination server.') },
            { title: __('Step 6'), text: __('Click "Download API" to get the http-api.zip file.') },
            { title: __('Step 7'), text: __('Upload and extract http-api.zip on the destination server.') },
            { title: __('Step 8'), text: __('Open the API config file and paste your API Key.') },
            { title: __('Step 9'), text: __('Test the API by visiting https://your-domain.com/http-api/') },
            { title: __('Step 10'), text: __('Return to WordPress and click "Save Connection".') }
        ],
        delay: 0.3
    },

    {
        id: 'mgs-api-setup',
        title: __('How to Install the HTTP API on the Remote Server'),
        summary: __('A complete step-by-step guide to installing the API required for HTTP connections.'),
        content: __(
            'The HTTP connection type requires a small PHP API file to be installed on the destination server. This API acts as a bridge between your WordPress site and the remote server.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Create a new HTTP connection and copy its API Key.') },
            { title: __('Step 2'), text: __('Click "Download API" to get the http-api.zip file.') },
            { title: __('Step 3'), text: __('Connect to your destination server via cPanel, FTP, or SSH.') },
            { title: __('Step 4'), text: __('Upload http-api.zip to the public_html directory.') },
            { title: __('Step 5'), text: __('Extract the zip file. You should see a folder named "http-api".') },
            { title: __('Step 6'), text: __('Open the config file inside and paste your API Key.') },
            { title: __('Step 7'), text: __('Save the file. Set permissions to 644.') },
            { title: __('Step 8'), text: __('Test the API by visiting https://your-domain.com/http-api/') },
            { title: __('Step 9'), text: __('If everything is correct, you will see a success response.') }
        ],
        delay: 0.4
    },

    {
        id: 'mgs-transfer-file',
        title: __('How to Transfer a File to the Remote Server'),
        summary: __('Move files from your WordPress media library to a connected server.'),
        content: __(
            'Once you have at least one active connection, you can transfer any file from your WordPress media library to the remote server. The transfer process automatically updates all references in your database.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Media" → "Library".') },
            { title: __('Step 2'), text: __('Click on the file you want to transfer.') },
            { title: __('Step 3'), text: __('Click the "Transfer to Server" button.') },
            { title: __('Step 4'), text: __('Select one of your active servers from the list.') },
            { title: __('Step 5'), text: __('Click "Start Transfer" and wait for completion.') },
            { title: __('Step 6'), text: __('The plugin checks references and uploads every image size.') },
            { title: __('Step 7'), text: __('Once completed, the file link is automatically updated.') }
        ],
        delay: 0.5
    },

    {
        id: 'mgs-restore-file',
        title: __('How to Restore a File from the Remote Server'),
        summary: __('Bring a transferred file back to your WordPress media library.'),
        content: __(
            'If you ever need to bring a file back from the remote server, the restore process works exactly like the transfer but in reverse. All image sizes will be downloaded back and all references updated.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Media" → "Library" and locate the transferred file.') },
            { title: __('Step 2'), text: __('Click the "Restore from Server" button.') },
            { title: __('Step 3'), text: __('Review the file information.') },
            { title: __('Step 4'), text: __('Click "Start Restore" to begin.') },
            { title: __('Step 5'), text: __('The plugin downloads the original file and all thumbnails.') },
            { title: __('Step 6'), text: __('All references are updated to point back to local files.') }
        ],
        delay: 0.6
    },

    {
        id: 'mgs-cron-job',
        title: __('How to Automate Transfers with Cron Job'),
        summary: __('Schedule automatic file transfers at regular intervals.'),
        content: __(
            'The Cron Job feature allows you to automatically transfer scanned files to your remote server at regular intervals. This is useful for high-traffic sites.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Magical Connection" → "Cron Job".') },
            { title: __('Step 2'), text: __('Toggle the "Cron Job" switch to enable.') },
            { title: __('Step 3'), text: __('Choose the "Run Interval" (e.g., every 5 minutes).') },
            { title: __('Step 4'), text: __('Set "Files Per Run".') },
            { title: __('Step 5'), text: __('Set "Retry for Failed Files".') },
            { title: __('Step 6'), text: __('Click "Save Settings".') },
            { title: __('Step 7'), text: __('Click "Run Manually Now" to trigger immediately.') }
        ],
        delay: 0.7
    },

    {
        id: 'mgs-file-scanner',
        title: __('How to Scan Files in the Media Library'),
        summary: __('Analyze your WordPress files to prepare them for transfer.'),
        content: __(
            'The File Scanner scans your WordPress media library and identifies all files by type (images, videos, audio, documents).'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Magical Connection" → "File Scanner".') },
            { title: __('Step 2'), text: __('Click "Start New Scan".') },
            { title: __('Step 3'), text: __('Wait for the scan to complete.') },
            { title: __('Step 4'), text: __('Review the summary — total files and sizes.') },
            { title: __('Step 5'), text: __('Use the "File Details by Type" table for details.') },
            { title: __('Step 6'), text: __('Transfer scanned files using FTP or HTTP.') }
        ],
        delay: 0.8
    },

    {
        id: 'mgs-plugin-settings',
        title: __('How to Configure Plugin Settings'),
        summary: __('Set up the default server, auto-transfer, and user permissions.'),
        content: __(
            'The Plugin Settings page lets you configure global behavior — including the default storage server, automatic transfers, and user permissions.'
        ),
        steps: [
            { title: __('Step 1'), text: __('Go to "Magical Connection" → "Plugin Settings".') },
            { title: __('Step 2'), text: __('Open the "WP Media Config" tab.') },
            { title: __('Step 3'), text: __('Enable "Direct Upload To Remote Server".') },
            { title: __('Step 4'), text: __('Choose your "Default Storage Server".') },
            { title: __('Step 5'), text: __('Enable allowed WordPress roles under "Access Statuses".') },
            { title: __('Step 6'), text: __('Click "Save Settings".') }
        ],
        delay: 0.9
    },

    {
        id: 'mgs-troubleshooting',
        title: __('Troubleshooting Common Issues'),
        summary: __('Fix the most common connection and transfer problems.'),
        content: __(
            'Most problems are caused by incorrect credentials, firewall restrictions, or file permission errors. Use the checklist below to diagnose them.'
        ),
        steps: [
            { title: __('Issue 1'), text: __('Connection failed — double-check IP, username, password, and port.') },
            { title: __('Issue 2'), text: __('HTTP API error — verify the API Key in the config file.') },
            { title: __('Issue 3'), text: __('Transfer stalls — check server disk space and PHP max_execution_time.') },
            { title: __('Issue 4'), text: __('File not found after transfer — check the base path.') },
            { title: __('Issue 5'), text: __('Permission denied — set folder 755 and files 644.') },
            { title: __('Issue 6'), text: __('Slow transfers — enable passive mode or switch to HTTP.') }
        ],
        delay: 1.0
    },

    {
        id: 'mgs-security',
        title: __('Security Best Practices'),
        summary: __('Keep your connections and files safe.'),
        content: __(
            'When transferring files between servers, security is critical. Always use secure protocols and keep API Keys private.'
        ),
        steps: [
            { title: __('Tip 1'), text: __('Use SFTP or FTPS instead of plain FTP.') },
            { title: __('Tip 2'), text: __('Never share your API Key.') },
            { title: __('Tip 3'), text: __('Restrict transfer permissions to administrators only.') },
            { title: __('Tip 4'), text: __('Keep WordPress, plugins, and PHP up to date.') },
            { title: __('Tip 5'), text: __('Use HTTPS on both WordPress and destination servers.') },
            { title: __('Tip 6'), text: __('Regularly check the connection status.') },
            { title: __('Tip 7'), text: __('Set strong, unique FTP passwords.') }
        ],
        delay: 1.1
    },

    {
        id: 'mgs-faq',
        title: __('Frequently Asked Questions'),
        summary: __('Quick answers to the most common questions.'),
        content: __('Here are the answers to the questions we hear most often.'),
        steps: [
            { title: __('Q: Does it work with any hosting?'), text: __('Yes, as long as FTP or HTTP access is available.') },
            { title: __('Q: Will existing posts break?'), text: __('No. All database references are automatically updated.') },
            { title: __('Q: Can I transfer multiple files?'), text: __('Yes. Use File Scanner with Cron Job for bulk transfers.') },
            { title: __('Q: Can I restore a transferred file?'), text: __('Yes. Click "Restore from Server" in the Media Library.') },
            { title: __('Q: What if transfer fails?'), text: __('The plugin retries failed files up to the retry limit.') },
            { title: __('Q: Is my data safe?'), text: __('Yes. Use SFTP/FTPS and HTTPS for maximum security.') },
            { title: __('Q: Can I delete a connection?'), text: __('Yes. Files already transferred are not deleted.') },
            { title: __('Q: How do I update the API Key?'), text: __('Regenerate it in your HTTP connection settings.') }
        ],
        delay: 1.2
    }
]