window.CRM.openEDriveCollaboraEditor = function (name, shareId = null, preOpenedWindow = null) {
    const win = preOpenedWindow || window.open(
        '',
        '_blank',
        `popup=yes,left=0,top=0,width=${screen.availWidth},height=${screen.availHeight}`
    );

    if (!win) {
        bootbox.alert(i18next.t('Please allow pop-up windows for this site.'));
        return;
    }

    win.document.write('<p style="font-family:sans-serif">' + i18next.t('Loading') + '...</p>');
    win.focus();

    const requestData = shareId === null
        ? { personID: window.CRM.currentPersonID, name: name }
        : { shareId: shareId };

    window.CRM.APIRequest({
        method: 'POST',
        path: shareId === null ? 'collabora/edit' : 'collabora/editShared',
        data: JSON.stringify(requestData)
    }, function (data) {
        if (!data || !data.success) {
            win.close();
            return;
        }

        const esc = (value) => $('<div>').text(value).html().replace(/"/g, '&quot;');
        const collaboraOrigin = new URL(data.url).origin;

        win.document.open();
        win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>${esc(data.title)}</title>
<style>html,body{margin:0;height:100%;overflow:hidden}iframe{width:100%;height:100%;border:0}</style></head>
<body><form id="collabora-form" method="post" target="collabora-frame" action="${esc(data.url)}">
<input type="hidden" name="access_token" value="${esc(data.access_token)}">
<input type="hidden" name="access_token_ttl" value="${esc(String(data.access_token_ttl))}">
</form><iframe name="collabora-frame" allow="clipboard-read ${esc(collaboraOrigin)}; clipboard-write ${esc(collaboraOrigin)}; fullscreen ${esc(collaboraOrigin)}"></iframe></body></html>`);
        win.document.close();
        win.document.getElementById('collabora-form').submit();
        win.focus();

        win.addEventListener('message', function (event) {
            if (event.origin !== collaboraOrigin) {
                return;
            }

            let message = event.data;
            if (typeof message === 'string') {
                try {
                    message = JSON.parse(message);
                } catch (e) {
                    return;
                }
            }

            if (message.MessageId === 'App_LoadingStatus' && message.Values && message.Values.Status === 'Frame_Ready') {
                win.frames['collabora-frame'].postMessage(JSON.stringify({ MessageId: 'Host_PostmessageReady', SendTime: Date.now() }), collaboraOrigin);
            } else if (message.MessageId === 'UI_Close') {
                win.close();
            }
        });
    }, function (error) {
        win.close();
        bootbox.alert(i18next.t(error.message));
    });
};
