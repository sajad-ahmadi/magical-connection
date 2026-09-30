jQuery(function ($) {

    let data = {
        action: 'magical_media_transfer',

        /*cron:{
            enabled : 0,
            interval : 'magical_connection_1_minute',
            method : 'wordpress',
            files_per_run : 9,
            max_retries : 4,
        }*/
        /*id : 1,
        step : 'connection'*/
        /*protocol : 'FTP',
        host : '88.135.68.17',
        username : '',
        password : '',
        domain : 'https://code-art.ir',
        passive_mod : 1,*/
    }


    /*$.ajax({
        url: MagicalConnection.ajaxUrl,
        type: 'POST',
        data: {
            nonce: MagicalConnection.nonce,
            ...data
        },

        beforeSend: function () {
            console.log('Saving...');
        },

        success: function (response) {
            if (response.success) {
                console.log(response);
            } else {
                console.error(response);
            }

        },

        error: function (xhr) {
            console.error('AJAX Error:', xhr.responseText);
        },

        complete: function () {
            console.log('Finished');
        }
    });*/

    const formData = new FormData();

    /*formData.append('action', 'magical_media_transfer');
    formData.append('nonce', MagicalConnection.nonce);
    startScan(formData);*/
    async function startScan(formData) {

        const response = await fetch(MagicalConnection.ajaxUrl, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        let buffer = '';

        while (true) {

            const { value, done } = await reader.read();

            if (done) {
                break;
            }

            buffer += decoder.decode(value, {
                stream: true
            });

            const events = buffer.split('\n\n');

            buffer = events.pop();

            for (const event of events) {
                handleStreamEvent(event);
            }
        }
    }

    function handleStreamEvent(rawEvent) {

        let event = 'message';
        let data = '';

        const lines = rawEvent.split('\n');

        for (const line of lines) {

            if (line.startsWith('event:')) {
                event = line.substring(6).trim();
            }

            if (line.startsWith('data:')) {
                data += line.substring(5).trim();
            }
        }

        if (!data) {
            return;
        }

        const payload = JSON.parse(data);

        switch (event) {

            case 'log':
                console.log(payload)
                break;
            case 'error':
                console.error(payload, payload.code)
                break;

            case 'finish':
                console.log(payload)
                break;
        }
    }


});