<?php

use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';

$partnerColumn = $direction === 'received' ? _('Shared by') : _('Shared with');
$pageTitle = $direction === 'received' ? _('Shared with me') : _('Shared with others');
?>

<div class="card shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-2 mb-md-0">
            <i class="fas <?= $direction === 'received' ? 'fa-folder-open' : 'fa-share-alt' ?> mr-2 text-primary"></i>
            <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
        </h3>
        <div class="btn-group btn-group-sm">
            <a class="btn <?= $direction === 'received' ? 'btn-primary' : 'btn-outline-primary' ?>"
               href="<?= $sRootPath ?>/v2/edrive/shared-with-me"><?= _('Shared with me') ?></a>
            <a class="btn <?= $direction === 'sent' ? 'btn-primary' : 'btn-outline-primary' ?>"
               href="<?= $sRootPath ?>/v2/edrive/shared-with-others"><?= _('Shared with others') ?></a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover w-100" id="edrive-shares-table">
            <thead>
                <tr>
                    <th><?= _('Icon') ?></th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Type') ?></th>
                    <th><?= $partnerColumn ?></th>
                    <th><?= _('Access') ?></th>
                    <th><?= _('Status') ?></th>
                    <th><?= _('Size') ?></th>
                    <th><?= _('Modification Date') ?></th>
                    <th><?= _('Actions') ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/edrive/CollaboraEditor.js"></script>
<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(function () {
        const rows = <?= json_encode($shareRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const escapeHtml = function (value) {
            return $('<div>').text(value == null ? '' : value).html();
        };
        const tableOptions = $.extend(true, {}, window.CRM.plugin.dataTable, {
            data: rows,
            order: [[1, 'asc']],
            columns: [
                {
                    data: 'icon',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        const disabled = row.canPreview ? '' : ' disabled';
                        return `<button type="button" class="btn btn-link p-0 preview-shared-document" title="${escapeHtml(row.name)}"${disabled}>
                            <img src="${escapeHtml(data)}" width="32" alt="${escapeHtml(row.type)}">
                        </button>`;
                    }
                },
                {
                    data: 'name',
                    render: function (data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        return row.canNavigate && row.openUrl
                            ? `<a class="shared-document-location-link" href="${escapeHtml(row.openUrl)}">${escapeHtml(data)}</a>`
                            : escapeHtml(data);
                    }
                },
                {
                    data: 'type',
                    render: function (data, type) {
                        return type === 'display' ? escapeHtml(data) : data;
                    }
                },
                {
                    data: 'partner',
                    title: <?= json_encode($partnerColumn) ?>,
                    render: function (data, type) {
                        return type === 'display' ? escapeHtml(data) : data;
                    }
                },
                {
                    data: 'access',
                    render: function (data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        const icon = row.accessLevel === 2
                            ? '<i class="fas fa-eye text-info"></i>'
                            : (row.accessLevel === 3
                                ? '<i class="fas fa-pen-to-square text-success"></i>'
                                : '<i class="fas fa-question-circle text-muted"></i>');
                        return `<span title="${escapeHtml(data)}" aria-label="${escapeHtml(data)}">${icon}</span>`;
                    }
                },
                {
                    data: 'status',
                    render: function (data, type) {
                        return type === 'display' ? escapeHtml(data) : data;
                    }
                },
                {
                    data: 'sizeBytes',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return escapeHtml(row.size);
                        }
                        return data == null ? -1 : data;
                    }
                },
                {
                    data: 'modified',
                    render: function (data, type) {
                        if (type !== 'display') {
                            return data == null ? 0 : data;
                        }
                        if (data == null) {
                            return '—';
                        }
                        return new Intl.DateTimeFormat(undefined, {
                            dateStyle: 'medium',
                            timeStyle: 'short'
                        }).format(new Date(data * 1000));
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        if (type !== 'display') {
                            return '';
                        }
                        let actions = '';
                        if (row.canDownload && row.downloadUrl) {
                            actions += `<a class="btn btn-outline-primary btn-sm" href="${escapeHtml(row.downloadUrl)}"
                                title="${escapeHtml(i18next.t('Download'))}" download>
                                <i class="fas fa-download"></i>
                            </a>`;
                        }
                        if (row.canEdit) {
                            actions += `<button type="button" class="btn btn-outline-info btn-sm edit-shared-document"
                                title="${escapeHtml(i18next.t('Edit'))}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>`;
                        }
                        return actions ? `<div class="btn-group" role="group">${actions}</div>` : '';
                    }
                }
            ],
            responsive: true,
            pageLength: 25
        });

        const table = $('#edrive-shares-table').DataTable(tableOptions);

        const openPreview = function (row) {
            const dialog = bootbox.dialog({
                title: `<span class="d-inline-flex align-items-center"><span class="badge badge-primary badge-pill mr-2">EDrive</span><span class="font-weight-bold">${i18next.t('Preview')}</span></span>`,
                message: `<div class="card border-0 shadow-sm mb-0">
                    <div class="card-body p-3 p-md-4 bg-light">
                        <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                            <img class="shared-preview-icon mr-3" width="40" alt="">
                            <div>
                                <div class="text-uppercase text-muted small font-weight-bold">${i18next.t('Document preview')}</div>
                                <div class="shared-preview-title h5 mb-0 text-dark"></div>
                            </div>
                            <a class="shared-preview-download btn btn-primary ml-auto d-none" href="#" download>
                                <i class="fas fa-download mr-1"></i>${i18next.t('Download')}
                            </a>
                        </div>
                        <div class="shared-preview-content card border bg-white rounded-lg text-center overflow-auto">
                            <div class="py-5 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>${i18next.t('preview currently being created')}</div>
                        </div>
                    </div>
                </div>`,
                closeButton: true,
                backdrop: true,
                onEscape: true,
                size: 'extra-large',
                buttons: {
                    close: {
                        label: '<i class="fas fa-times"></i> ' + i18next.t('Close'),
                        className: 'btn-outline-secondary'
                    }
                }
            });

            window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/getSharedPreview',
                data: JSON.stringify({ shareId: row.shareId })
            }, function (data) {
                if (!data || !data.success) {
                    dialog.find('.shared-preview-content').html(
                        `<div class="alert alert-warning m-3 text-left">${i18next.t('Unable to create the preview')}</div>`
                    );
                    return;
                }

                dialog.find('.shared-preview-icon').attr('src', data.icon).attr('alt', row.type);
                dialog.find('.shared-preview-title').text(data.name);
                dialog.find('.shared-preview-content').html(data.path);
                if (!data.isDirectory && data.downloadUrl) {
                    dialog.find('.shared-preview-download').attr('href', data.downloadUrl).removeClass('d-none');
                }
            }, function (error) {
                dialog.find('.shared-preview-content').html(
                    `<div class="alert alert-warning m-3 text-left">${escapeHtml(i18next.t(error.message))}</div>`
                );
            });
        };

        $('#edrive-shares-table tbody').on('click', '.preview-shared-document', function () {
            const row = table.row($(this).closest('tr')).data();
            if (row && row.canPreview) {
                openPreview(row);
            }
        });

        $('#edrive-shares-table tbody').on('click', '.edit-shared-document', function () {
            const row = table.row($(this).closest('tr')).data();
            if (row && row.canEdit) {
                window.CRM.openEDriveCollaboraEditor(row.name, row.shareId);
            }
        });
    });
</script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
