document.addEventListener('click', function (event) {
    const button = event.target.closest('.magical-transfer-media');
    if (!button) {
        return;
    }
    event.preventDefault();
    const attachmentId = button.dataset.attachmentId;
    if (!attachmentId) {
        return;
    }
    let action = event.target.getAttribute('data-action');
    getMediaInfo(attachmentId,action);
});

function getMediaInfo(attachmentId,action) {
    window.MagicalConnectionAppDialog.list_hosts = [];
    window.MagicalConnectionAppDialog.attachment_info = {};
    if (action === "restore" ){
        window.MagicalConnectionAppDialog.restore_file = true;
    }else {
        window.MagicalConnectionAppDialog.restore_file = false;

    }
    window.MagicalConnectionAppDialog.openDialog(attachmentId)
}

function openMediaTransferModal(data) {

    console.log(
        'Opening transfer modal:',
        data
    );

    renderMediaInfo(
        data.media
    );

    renderHosts(
        data.hosts
    );

    const modal = document.getElementById(
        'magical-media-transfer-modal'
    );

    modal.hidden = false;
}

function renderMediaInfo(media) {

    const container = document.getElementById(
        'magical-media-info'
    );

    const size = formatFileSize(
        media.size
    );

    container.innerHTML = `
        <div class="magical-media-file">

            <div class="magical-media-file__name">
                ${escapeHtml(media.name)}
            </div>

            <div class="magical-media-file__meta">
                ${escapeHtml(media.mime)}
                ·
                ${size}
            </div>

            ${
        media.is_image
            ? `
                        <div class="magical-media-file__dimensions">
                            ${media.width} × ${media.height}
                        </div>
                    `
            : ''
    }

        </div>
    `;
}


function renderHosts(hosts) {

    const container = document.getElementById(
        'magical-media-host-list'
    );

    container.innerHTML = '';

    if (!hosts.length) {

        container.innerHTML = `
            <div class="magical-media-empty">
                No download hosts are available.
            </div>
        `;

        return;
    }

    hosts.forEach(host => {

        const item = document.createElement(
            'label'
        );

        item.className =
            'magical-media-host';

        item.innerHTML = `
            <input
                type="radio"
                name="magical_media_host"
                value="${host.id}"
            >

            <span class="magical-media-host__content">

                <strong>
                    ${escapeHtml(host.name)}
                </strong>

                <small>
                    ${escapeHtml(host.protocol)}
                    ·
                    ${escapeHtml(host.host)}
                    :${host.port}
                </small>

            </span>
        `;

        container.appendChild(item);
    });
}

document.addEventListener(
    'change',
    function (event) {

        if (
            event.target.name !==
            'magical_media_host'
        ) {
            return;
        }

        const transferButton =
            document.getElementById(
                'magical-media-transfer'
            );

        transferButton.disabled = false;
    }
);

function formatFileSize(bytes) {

    if (!Number.isFinite(bytes) || bytes < 0) {
        return '0 Bytes';
    }

    if (bytes === 0) {
        return '0 Bytes';
    }

    const units = [
        'Bytes',
        'KB',
        'MB',
        'GB',
        'TB'
    ];

    const index = Math.floor(
        Math.log(bytes) / Math.log(1024)
    );

    const unitIndex = Math.min(
        index,
        units.length - 1
    );

    const size = bytes / Math.pow(
        1024,
        unitIndex
    );

    return `${parseFloat(size.toFixed(2))} ${units[unitIndex]}`;
}

function escapeHtml(value) {

    const div = document.createElement('div');

    div.textContent = String(value ?? '');

    return div.innerHTML;
}