/*******************************************************************************
 *
 *  CKEditor 5 integration and EcclesiaCRM-specific editor tools.
 *  Copyright 2018 Philippe Logel. All rights reserved; not MIT licensed.
 *  This code cannot be incorporated into another software without authorization.
 *
 ******************************************************************************/
(function () {
    'use strict';

    const CRM = window.CRM;
    const CK = window.CKEDITOR;
    const editorPromises = new Map();
    const editorInstances = new Map();
    const pendingDestructions = new Map();
    const editorIds = new WeakMap();
    let mathJaxPromise = null;

    if (!CK || !CK.ClassicEditor) {
        throw new Error('CKEditor 5 must be loaded before ckeditorextension.js.');
    }

    function translate(key) {
        return window.i18next ? i18next.t(key) : key;
    }

    function reportEditorError(error) {
        console.error('CKEditor 5 error:', error);
        if (CRM.DisplayNormalAlert) {
            CRM.DisplayNormalAlert(translate('Error'), error.message || String(error));
        } else if (window.bootbox) {
            bootbox.alert(error.message || String(error));
        }
    }

    function readTranslation(locale) {
        if (!locale || locale === 'en') {
            return Promise.resolve([]);
        }

        const normalizedLocale = locale.replace('_', '-').toLowerCase();
        const language = normalizedLocale.split('-')[0];
        const translationKey = window.CKEDITOR_TRANSLATIONS &&
            (window.CKEDITOR_TRANSLATIONS[normalizedLocale] || window.CKEDITOR_TRANSLATIONS[language]);

        if (translationKey) {
            return Promise.resolve([translationKey]);
        }

        const script = document.createElement('script');
        script.src = CRM.root + '/skin/external/ckeditor5/' + language + '.umd.js';
        const loaded = new Promise(function (resolve, reject) {
            script.onload = function () {
                const translations = window.CKEDITOR_TRANSLATIONS &&
                    window.CKEDITOR_TRANSLATIONS[language];
                if (!translations) {
                    reject(new Error('CKEditor 5 translation was not registered: ' + language));
                    return;
                }
                resolve([translations]);
            };
            script.onerror = function () {
                reject(new Error('Unable to load CKEditor 5 translation: ' + language));
            };
        });
        document.head.appendChild(script);
        return loaded;
    }

    function loadStyles() {
        const stylesheetUrl = CRM.root + '/skin/external/ckeditor5/ckeditor5.css';
        if (!document.querySelector('link[data-ckeditor5-styles]')) {
            const stylesheet = document.createElement('link');
            stylesheet.rel = 'stylesheet';
            stylesheet.href = stylesheetUrl;
            stylesheet.dataset.ckeditor5Styles = 'true';
            document.head.appendChild(stylesheet);
        }

        (CRM.contentsExternalCssFont || '')
            .split(';')
            .map(function (url) {
                return url.trim();
            })
            .filter(Boolean)
            .forEach(function (url) {
                const href = new URL(url, document.baseURI).href;
                const alreadyLoaded = Array.from(
                    document.querySelectorAll('link[data-ckeditor-content-fonts]')
                ).some(function (stylesheet) {
                    return stylesheet.href === href;
                });

                if (alreadyLoaded) {
                    return;
                }

                const fontStylesheet = document.createElement('link');
                fontStylesheet.rel = 'stylesheet';
                fontStylesheet.href = href;
                fontStylesheet.dataset.ckeditorContentFonts = 'true';
                document.head.appendChild(fontStylesheet);
            });
    }

    function buildToolbar(preset, options) {
        if (preset === 'minimal') {
            return ['bold', 'italic', 'underline', '|', 'fontColor', 'fontBackgroundColor', '|', 'alignment'];
        }

        const toolbar = [
            'undo', 'redo', '|',
            'findAndReplace', 'selectAll', 'preview', 'print', '|',
            'heading', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
            'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', 'removeFormat', '|',
            'alignment', 'outdent', 'indent', 'numberedList', 'bulletedList', 'blockQuote', '|',
            'link',
            'insertTable', 'mediaEmbed', 'insertVideo', 'htmlEmbed',
            'horizontalLine', 'pageBreak', 'specialCharacters', 'emoji'
        ];

        if (CRM.bEDrive && options.uploadType) {
            toolbar.splice(toolbar.indexOf('insertTable'), 0, 'uploadImage');
        } else {
            toolbar.splice(toolbar.indexOf('insertTable'), 0, 'insertImageUrl');
        }
        if (CRM.bHtmlSourceEditor) {
            toolbar.push('sourceEditing');
        }
        if (preset === 'notes' || preset === 'campaign') {
            toolbar.push('|', 'applyTemplate', 'manageTemplates', 'saveTemplate');
            if (CRM.bEDrive) {
                toolbar.push('saveAsWordFile');
            }
        }
        if (preset === 'campaign') {
            toolbar.push('mergeTags');
            toolbar.push('insertMath');
        }
        if (Array.isArray(options.toolbarItems) && options.toolbarItems.length) {
            if (options.toolbarItemsPosition === 'start') {
                toolbar.unshift(...options.toolbarItems, '|');
            } else {
                toolbar.push('|', ...options.toolbarItems);
            }
        }
        if (CRM.bEDrive) {
            toolbar.push('browseImage');
        }
        toolbar.push('|', 'fullscreen');
        return toolbar;
    }

    function createUploadAdapter(editor, loader, uploadType) {
        return {
            xhr: null,
            upload: function () {
                return loader.file.then(function (file) {
                    return new Promise(function (resolve, reject) {
                        const xhr = new XMLHttpRequest();
                        const data = new FormData();
                        this.xhr = xhr;

                        xhr.open('POST', CRM.root + '/uploader/upload.php?type=' +
                            encodeURIComponent(uploadType), true);
                        xhr.setRequestHeader('X-CSRF-Token', CRM.csrfToken);
                        xhr.responseType = 'json';

                        xhr.addEventListener('error', function () {
                            reject('Upload failed.');
                        });
                        xhr.addEventListener('abort', function () {
                            reject();
                        });
                        xhr.addEventListener('load', function () {
                            const response = xhr.response;
                            if (xhr.status < 200 || xhr.status >= 300 || !response || !response.url) {
                                reject(response && response.error ? response.error.message : 'Upload failed.');
                                return;
                            }
                            resolve({default: response.url});
                        });

                        data.append('upload', file);
                        xhr.send(data);
                    }.bind(this));
                }.bind(this));
            },
            abort: function () {
                if (this.xhr) {
                    this.xhr.abort();
                }
            }
        };
    }

    let fileDropCounter = 0;

    function createUploadProgress(editor) {
        const editableElement = editor.ui.view.editable.element;
        const editorElement = editor.ui.view.element ||
            (editableElement && editableElement.closest('.ck-editor'));
        if (!editorElement) {
            reportEditorError(new Error('Unable to display the file upload progress.'));
            return {
                update: function () {},
                finish: function () {},
                remove: function () {}
            };
        }

        const progressContainer = document.createElement('div');
        progressContainer.className = 'crm-ckeditor-upload-progress';
        const progressTrack = document.createElement('div');
        progressTrack.className = 'crm-ckeditor-upload-progress-track';
        progressTrack.setAttribute('role', 'progressbar');
        progressTrack.setAttribute('aria-label', translate('Upload progress'));
        progressTrack.setAttribute('aria-valuemin', '0');
        progressTrack.setAttribute('aria-valuemax', '100');
        progressTrack.setAttribute('aria-valuenow', '0');
        const progressBar = document.createElement('div');
        progressBar.className = 'crm-ckeditor-upload-progress-bar';
        progressTrack.appendChild(progressBar);
        const percentage = document.createElement('span');
        percentage.className = 'crm-ckeditor-upload-progress-percentage';
        percentage.textContent = '0%';
        progressContainer.append(progressTrack, percentage);

        const editorMain = editorElement.querySelector('.ck-editor__main');
        if (editorMain) {
            editorElement.insertBefore(progressContainer, editorMain);
        } else {
            editorElement.appendChild(progressContainer);
        }

        let removalTimeout = null;
        const remove = function () {
            if (removalTimeout !== null) {
                window.clearTimeout(removalTimeout);
                removalTimeout = null;
            }
            progressContainer.remove();
        };

        return {
            update: function (value) {
                const progress = Math.max(0, Math.min(100, Math.round(value)));
                progressBar.style.width = progress + '%';
                progressTrack.setAttribute('aria-valuenow', String(progress));
                percentage.textContent = progress + '%';
            },
            finish: function (hasErrors) {
                this.update(100);
                progressContainer.classList.toggle('has-errors', hasErrors);
                removalTimeout = window.setTimeout(remove, 1200);
            },
            remove: remove
        };
    }

    function uploadDroppedFile(file, uploadType, onProgress) {
        return new Promise(function (resolve, reject) {
            const xhr = new XMLHttpRequest();
            const data = new FormData();
            xhr.open('POST', CRM.root + '/uploader/upload.php?type=' +
                encodeURIComponent(uploadType), true);
            xhr.setRequestHeader('X-CSRF-Token', CRM.csrfToken);
            xhr.responseType = 'json';
            xhr.upload.addEventListener('progress', function (event) {
                if (event.lengthComputable) {
                    onProgress(event.loaded, event.total);
                }
            });
            xhr.addEventListener('error', function () {
                reject(new Error('Unable to upload ' + file.name + '.'));
            });
            xhr.addEventListener('abort', function () {
                reject(new Error('Upload cancelled for ' + file.name + '.'));
            });
            xhr.addEventListener('load', function () {
                const response = xhr.response;
                if (xhr.status < 200 || xhr.status >= 300 || !response ||
                    response.uploaded !== 1 || !response.url) {
                    reject(new Error(response && response.error
                        ? response.error.message
                        : 'Unable to upload ' + file.name + '.'));
                    return;
                }
                resolve({file: file, url: response.url});
            });
            data.append('upload', file);
            xhr.send(data);
        });
    }

    function createFileDropPlugin(uploadType) {
        return class CRMFileDropUpload extends CK.Plugin {
            static get pluginName() {
                return 'CRMFileDropUpload';
            }

            init() {
                const editor = this.editor;
                editor.editing.view.document.on('clipboardInput', function (event, data) {
                    if (data.method !== 'drop') {
                        return;
                    }

                    const files = Array.from(data.dataTransfer.files || []);
                    if (!files.length) {
                        return;
                    }

                    event.stop();
                    if (data.domEvent && typeof data.domEvent.preventDefault === 'function') {
                        data.domEvent.preventDefault();
                    }

                    const targetRange = data.targetRanges && data.targetRanges[0];
                    const insertionRange = targetRange
                        ? editor.editing.mapper.toModelRange(targetRange)
                        : editor.model.document.selection.getFirstRange();
                    if (!insertionRange) {
                        reportEditorError(new Error('Unable to determine where to insert the dropped file.'));
                        return;
                    }

                    const markerName = 'crm-file-drop-' + (++fileDropCounter);
                    editor.model.change(function (writer) {
                        writer.addMarker(markerName, {
                            range: writer.createRange(insertionRange.start, insertionRange.end),
                            usingOperation: false,
                            affectsData: false
                        });
                    });

                    const uploadProgress = createUploadProgress(editor);
                    const fileProgress = files.map(function () {
                        return 0;
                    });
                    const totalSize = files.reduce(function (sum, file) {
                        return sum + file.size;
                    }, 0);
                    const updateUploadProgress = function () {
                        const completeFraction = totalSize > 0
                            ? files.reduce(function (sum, file, index) {
                                return sum + file.size * fileProgress[index];
                            }, 0) / totalSize
                            : fileProgress.reduce(function (sum, progress) {
                                return sum + progress;
                            }, 0) / files.length;
                        uploadProgress.update(completeFraction * 100);
                    };

                    const uploads = files.map(function (file, index) {
                        const isImage = file.type.toLowerCase().startsWith('image/') ||
                            /\.(apng|avif|bmp|gif|jpe?g|png|svg|webp)$/i.test(file.name);
                        const targetType = uploadType.replace(/(Images|Documents)$/, isImage ? 'Images' : 'Documents');
                        return uploadDroppedFile(file, targetType, function (loaded, total) {
                            fileProgress[index] = Math.min(1, loaded / total);
                            updateUploadProgress();
                        }).then(function (uploaded) {
                            fileProgress[index] = 1;
                            updateUploadProgress();
                            uploaded.isImage = isImage;
                            return uploaded;
                        }, function (error) {
                            fileProgress[index] = 1;
                            updateUploadProgress();
                            return {file: file, error: error};
                        });
                    });

                    Promise.all(uploads).then(function (results) {
                        if (editor.state === 'destroyed') {
                            uploadProgress.remove();
                            return;
                        }

                        const uploadedFiles = results.filter(function (result) {
                            return !result.error;
                        });
                        const failedFiles = results.filter(function (result) {
                            return result.error;
                        });
                        uploadProgress.finish(failedFiles.length > 0);

                        if (uploadedFiles.length) {
                            const container = document.createElement('div');
                            uploadedFiles.forEach(function (result) {
                                if (result.isImage) {
                                    const figure = document.createElement('figure');
                                    figure.className = 'image';
                                    const image = document.createElement('img');
                                    image.src = result.url;
                                    image.alt = result.file.name;
                                    figure.appendChild(image);
                                    container.appendChild(figure);
                                } else {
                                    const paragraph = document.createElement('p');
                                    const link = document.createElement('a');
                                    link.href = result.url;
                                    link.textContent = result.file.name;
                                    paragraph.appendChild(link);
                                    container.appendChild(paragraph);
                                }
                            });

                            const view = editor.data.processor.toView(container.innerHTML);
                            const model = editor.data.toModel(view);
                            editor.model.change(function (writer) {
                                const marker = editor.model.markers.get(markerName);
                                if (marker) {
                                    editor.model.insertContent(model, marker.getRange());
                                    writer.removeMarker(markerName);
                                }
                            });
                        } else {
                            editor.model.change(function (writer) {
                                if (editor.model.markers.get(markerName)) {
                                    writer.removeMarker(markerName);
                                }
                            });
                        }

                        if (failedFiles.length) {
                            reportEditorError(new Error(failedFiles.map(function (result) {
                                return result.error.message;
                            }).join('\n')));
                        }
                    }).catch(function (error) {
                        uploadProgress.finish(true);
                        if (editor.state !== 'destroyed') {
                            editor.model.change(function (writer) {
                                if (editor.model.markers.get(markerName)) {
                                    writer.removeMarker(markerName);
                                }
                            });
                        }
                        reportEditorError(error);
                    });
                }, {priority: 'high'});
            }
        };
    }

    function insertHtml(editor, html) {
        const view = editor.data.processor.toView(html);
        const model = editor.data.toModel(view);
        editor.model.change(function () {
            editor.model.insertContent(model);
        });
    }

    function addButton(editor, name, label, icon, callback) {
        editor.ui.componentFactory.add(name, function (locale) {
            const button = new CK.ButtonView(locale);
            button.set({
                label: translate(label),
                tooltip: true,
                icon: icon,
                withText: false
            });
            button.on('execute', callback);
            return button;
        });
    }

    function apiRequest(path, data, callback) {
        CRM.APIRequest({
            method: 'POST',
            path: path,
            data: JSON.stringify(data)
        }, callback, reportEditorError);
    }

    function checkApiSuccess(result, message) {
        if (result && result.status === 'success') {
            return true;
        }
        reportEditorError(new Error(translate(message)));
        return false;
    }

    function previewEditor(editor, shouldPrint) {
        const iframe = document.createElement('iframe');
        iframe.title = translate(shouldPrint ? 'Print preview' : 'Preview');
        iframe.sandbox = 'allow-same-origin allow-modals';
        iframe.style.width = '100%';
        iframe.style.height = '60vh';
        const dialog = window.bootbox.dialog({
            title: translate(shouldPrint ? 'Print' : 'Preview'),
            message: $(iframe),
            size: 'large'
        });
        iframe.srcdoc = '<!doctype html><html><head><meta charset="utf-8"></head><body>' +
            editor.getData() + '</body></html>';

        if (shouldPrint) {
            let isShown = false;
            let didPrint = false;
            const printFrame = function () {
                if (isShown && !didPrint && iframe.contentWindow) {
                    didPrint = true;
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }
            };
            iframe.addEventListener('load', printFrame);
            dialog.on('shown.bs.modal', function () {
                isShown = true;
                printFrame();
            });
        }
    }

    function loadMathJax() {
        if (window.MathJax && window.MathJax.Hub) {
            return Promise.resolve();
        }
        if (mathJaxPromise) {
            return mathJaxPromise;
        }

        mathJaxPromise = new Promise(function (resolve, reject) {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML';
            script.onload = resolve;
            script.onerror = function () {
                mathJaxPromise = null;
                reject(new Error('Unable to load MathJax.'));
            };
            document.head.appendChild(script);
        });
        return mathJaxPromise;
    }

    function createTemplateBrowser(templates, onSelect) {
        const list = document.createElement('div');
        list.className = 'crm-template-list';
        const entries = new Map();
        let selectedTemplate = null;

        if (!templates.length) {
            list.textContent = translate('(No templates defined)');
        }

        templates.forEach(function (template) {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'crm-template-item';
            row.setAttribute('aria-pressed', 'false');

            const image = document.createElement('img');
            const imageName = /^template[1-3]\.gif$/.test(template.image || '')
                ? template.image
                : 'template1.gif';
            image.src = CRM.root + '/Images/' + imageName;
            image.alt = '';
            image.setAttribute('aria-hidden', 'true');

            const details = document.createElement('span');
            details.className = 'crm-template-details';
            const title = document.createElement('strong');
            title.className = 'crm-template-title';
            title.textContent = template.title;
            const description = document.createElement('span');
            description.className = 'crm-template-description';
            description.textContent = template.description;
            details.append(title, description);
            row.append(image, details);
            row.addEventListener('click', function () {
                browser.select(template);
            });
            list.appendChild(row);
            entries.set(String(template.id), {
                row: row,
                title: title,
                description: description
            });
        });

        const browser = {
            element: list,
            entries: entries,
            getSelected: function () {
                return selectedTemplate;
            },
            select: function (template) {
                selectedTemplate = template;
                entries.forEach(function (entry) {
                    const isSelected = String(template.id) === entry.row.dataset.templateId;
                    entry.row.setAttribute('aria-pressed', String(isSelected));
                    entry.row.classList.toggle('is-selected', isSelected);
                });
                if (onSelect) {
                    onSelect(template);
                }
            }
        };

        entries.forEach(function (entry, id) {
            entry.row.dataset.templateId = id;
        });
        if (templates.length) {
            browser.select(templates[0]);
        }
        return browser;
    }

    function openTemplateList(editor, mode) {
        apiRequest('ckeditor/alltemplates', {personID: CRM.iPersonId}, function (templates) {
            if (!Array.isArray(templates)) {
                reportEditorError(new Error(translate('Unable to load content templates.')));
                return;
            }

            if (mode === 'manage') {
                openManageTemplates(editor, templates);
                return;
            }

            let selectedTemplate = null;
            const content = document.createElement('div');
            const browser = createTemplateBrowser(templates, function (template) {
                selectedTemplate = template;
            });
            const replaceLabel = document.createElement('label');
            replaceLabel.className = 'crm-template-replace';
            const replaceCheckbox = document.createElement('input');
            replaceCheckbox.type = 'checkbox';
            replaceCheckbox.checked = true;
            replaceLabel.append(replaceCheckbox, document.createTextNode(' ' + translate('Replace actual content')));
            content.append(browser.element, replaceLabel);

            window.bootbox.dialog({
                title: translate('Content Templates'),
                message: $(content),
                size: 'large',
                className: 'crm-template-dialog',
                buttons: {
                    cancel: {
                        label: translate('Cancel'),
                        className: 'btn-secondary'
                    },
                    apply: {
                        label: translate('Apply'),
                        className: 'btn-primary',
                        callback: function () {
                            if (!selectedTemplate) {
                                return false;
                            }
                            if (replaceCheckbox.checked) {
                                editor.setData(selectedTemplate.html);
                            } else {
                                insertHtml(editor, selectedTemplate.html);
                            }
                            window.CRM.ckedt = {
                                title: selectedTemplate.title,
                                desc: selectedTemplate.description
                            };
                        }
                    }
                }
            });
        });
    }

    function openManageTemplates(editor, templates) {
        const form = document.createElement('div');
        const titleInput = document.createElement('input');
        titleInput.className = 'form-control';
        titleInput.placeholder = translate('Template Name');
        titleInput.setAttribute('aria-label', translate('Template Name'));
        const descriptionInput = document.createElement('input');
        descriptionInput.className = 'form-control';
        descriptionInput.placeholder = translate('Template Description');
        descriptionInput.setAttribute('aria-label', translate('Template Description'));

        const fields = document.createElement('div');
        fields.className = 'crm-template-fields';
        fields.append(titleInput, descriptionInput);

        let selectedTemplate = null;
        const browser = createTemplateBrowser(templates, function (template) {
            selectedTemplate = template;
            titleInput.value = template.title || '';
            descriptionInput.value = template.description || '';
        });
        const updateButtonDisabledState = function (disabled) {
            titleInput.disabled = disabled;
            descriptionInput.disabled = disabled;
        };
        updateButtonDisabledState(!templates.length);
        form.append(browser.element, fields);

        window.bootbox.dialog({
            title: translate('Manage templates'),
            message: $(form),
            size: 'large',
            className: 'crm-template-dialog',
            buttons: {
                cancel: {
                    label: translate('Cancel'),
                    className: 'btn-secondary'
                },
                modify: {
                    label: translate('Modify'),
                    className: 'btn-default',
                    callback: function () {
                        if (!selectedTemplate) {
                            return false;
                        }
                        const title = titleInput.value.trim();
                        const description = descriptionInput.value.trim();
                        if (!title || !description) {
                            reportEditorError(new Error(translate('Explanation field cannot be empty.')));
                            return false;
                        }

                        apiRequest('ckeditor/renametemplate', {
                            templateID: selectedTemplate.id,
                            title: title,
                            desc: description
                        }, function (result) {
                            if (!checkApiSuccess(result, 'Unable to update content template.')) {
                                return;
                            }
                            selectedTemplate.title = title;
                            selectedTemplate.description = description;
                            const entry = browser.entries.get(String(selectedTemplate.id));
                            if (entry) {
                                entry.title.textContent = title;
                                entry.description.textContent = description;
                            }
                            window.CRM.ckedt = {title: title, desc: description};
                        });
                        return false;
                    }
                },
                delete: {
                    label: translate('Delete'),
                    className: 'btn-danger',
                    callback: function () {
                        if (!selectedTemplate) {
                            return false;
                        }
                        const template = selectedTemplate;
                        window.bootbox.confirm({
                            message: translate("Are you sure to delete this template? This can't be undone."),
                            callback: function (confirmed) {
                                if (!confirmed) {
                                    return;
                                }
                                apiRequest('ckeditor/deletetemplate', {templateID: template.id}, function (result) {
                                    if (!checkApiSuccess(result, 'Unable to delete content template.')) {
                                        return;
                                    }
                                    window.bootbox.hideAll();
                                    openTemplateList(editor, 'manage');
                                });
                            }
                        });
                        return false;
                    }
                },
                ok: {
                    label: translate('OK'),
                    className: 'btn-primary',
                    callback: function () {
                        if (!selectedTemplate) {
                            return false;
                        }
                        editor.setData(selectedTemplate.html);
                        window.CRM.ckedt = {
                            title: selectedTemplate.title,
                            desc: selectedTemplate.description
                        };
                    }
                }
            }
        });
    }

    function promptTemplateFields(title, description, callback) {
        const form = document.createElement('div');
        const titleInput = document.createElement('input');
        titleInput.className = 'form-control mb-2';
        titleInput.placeholder = translate('Template Name');
        titleInput.value = title || '';
        const descriptionInput = document.createElement('input');
        descriptionInput.className = 'form-control';
        descriptionInput.placeholder = translate('Template Description');
        descriptionInput.value = description || '';
        form.append(titleInput, descriptionInput);

        window.bootbox.dialog({
            title: translate('CKEditor Template Creation'),
            message: $(form),
            buttons: {
                cancel: {
                    label: translate('Cancel'),
                    className: 'btn-secondary'
                },
                save: {
                    label: translate('Save'),
                    className: 'btn-primary',
                    callback: function () {
                        if (!titleInput.value.trim() || !descriptionInput.value.trim()) {
                            reportEditorError(new Error(translate('Explanation field cannot be empty.')));
                            return false;
                        }
                        callback(titleInput.value.trim(), descriptionInput.value.trim());
                    }
                }
            }
        });
    }

    class CRMEditorTools extends CK.Plugin {
        static get pluginName() {
            return 'CRMEditorTools';
        }

        init() {
            const editor = this.editor;
            const preset = editor.config.get('crm.preset');

            if (preset === 'notes' || preset === 'campaign') {
                addButton(editor, 'saveTemplate', 'Save templates', CK.IconDocumentWithCheckmark, function () {
                    const previous = CRM.ckedt || {};
                    promptTemplateFields(previous.title, previous.desc, function (title, description) {
                        CRM.ckedt = {title: title, desc: description};
                        apiRequest('ckeditor/savetemplate', {
                            personID: CRM.iPersonId,
                            title: title,
                            desc: description,
                            text: editor.getData()
                        }, function (result) {
                            if (!checkApiSuccess(result, 'Unable to save content template.')) {
                                return;
                            }
                        });
                    });
                });

                addButton(editor, 'manageTemplates', 'Manage templates', CK.IconDocumentWithLoupe, function () {
                    openTemplateList(editor, 'manage');
                });

                addButton(editor, 'applyTemplate', 'Apply templates', CK.IconTemplate, function () {
                    openTemplateList(editor, 'apply');
                });

                if (CRM.bEDrive) {
                    addButton(editor, 'saveAsWordFile', 'Save As Word File in EDrive', CK.IconExportWord, function () {
                        window.bootbox.prompt({
                            title: translate('Word file name'),
                            callback: function (title) {
                                if (!title) {
                                    return;
                                }
                                apiRequest('ckeditor/saveAsWordFile', {
                                    personID: CRM.iPersonId,
                                    title: title,
                                    text: editor.getData()
                                }, function (result) {
                                    if (!result.success) {
                                        reportEditorError(new Error(translate('Unable to save file.')));
                                        return;
                                    }
                                    window.bootbox.alert(translate('Your note is saved in your EDrive'));
                                });
                            }
                        });
                    });
                }
            }

            if (preset === 'campaign') {
                editor.ui.componentFactory.add('mergeTags', function (locale) {
                    const configuredTags = editor.config.get('crm.mergeTags');
                    const tags = Array.isArray(configuredTags)
                        ? configuredTags.map(function (tag) {
                            return [tag.label, tag.value];
                        })
                        : [
                            ['First Name', '*|FNAME|*'],
                            ['Last Name', '*|LNAME|*'],
                            ['Email', '*|EMAIL|*'],
                            ['List: Name', '*|LIST:NAME|*'],
                            ['List: Company', '*|LIST:COMPANY|*'],
                            ['List: Description', '*|LIST:DESCRIPTION|*'],
                            ['List: Unsub', '*|UNSUB|*'],
                            ['List: VCard', '*|LIST:ADDRESS_VCARD|*'],
                            ['Date', '*|DATE:' + CRM.sDateFormatLong + '|*'],
                            ['Language', '*|TRANSLATE:' + CRM.lang + '|*']
                        ];

                    if (!Array.isArray(configuredTags) && CRM.bWithAddressPhone) {
                        tags.splice(3, 0, ['Address', '*|ADDRESS|*'], ['Phone', '*|PHONE|*']);
                    }

                    const dropdown = CK.createDropdown(locale);
                    dropdown.buttonView.set({
                        label: translate('Merge Tags'),
                        tooltip: true,
                        icon: CK.IconInsertMergeField,
                        withText: false
                    });

                    const items = new CK.Collection();
                    const valuesByLabel = new Map();
                    tags.forEach(function (tag) {
                        const label = translate(tag[0]);
                        valuesByLabel.set(label, tag[1]);
                        items.add({
                            type: 'button',
                            model: new CK.UIModel({
                                label: label,
                                withText: true
                            })
                        });
                    });

                    CK.addListToDropdown(dropdown, items, {
                        ariaLabel: translate('Merge Tags'),
                        role: 'menu'
                    });
                    dropdown.on('execute', function (event) {
                        const value = valuesByLabel.get(event.source.label);
                        if (!value) {
                            return;
                        }
                        editor.model.change(function (writer) {
                            editor.model.insertContent(writer.createText(value));
                        });
                    });
                    return dropdown;
                });

                addButton(editor, 'insertMath', 'Add Math Formula', CK.IconCode, function () {
                    window.bootbox.prompt({
                        title: translate('Add Math Formula'),
                        callback: function (formula) {
                            if (!formula) {
                                return;
                            }
                            const math = document.createElement('span');
                            math.className = 'math-tex';
                            math.textContent = '\\(' + formula + '\\)';
                            insertHtml(editor, math.outerHTML);
                            loadMathJax().then(function () {
                                window.MathJax.Hub.Queue([
                                    'Typeset',
                                    window.MathJax.Hub,
                                    editor.ui.view.editable.element
                                ]);
                            }).catch(reportEditorError);
                        }
                    });
                });
            }

            addButton(editor, 'preview', 'Preview', CK.IconEye, function () {
                previewEditor(editor, false);
            });
            addButton(editor, 'print', 'Print', CK.IconPrint, function () {
                previewEditor(editor, true);
            });

            if (CRM.bEDrive) {
                addButton(editor, 'browseImage', 'Browse EDrive', CK.IconBrowseFiles, function () {
                    const fileType = (editor.config.get('crm.uploadType') || 'publicImages')
                        .replace('Documents', 'Images');
                    const popup = window.open(
                        CRM.root + '/browser/browse.php?type=' + encodeURIComponent(fileType),
                        'crmImageBrowser',
                        'width=1000,height=700,resizable=yes,scrollbars=yes'
                    );
                    if (!popup) {
                        reportEditorError(new Error(translate('Please allow pop-up windows for this site.')));
                        return;
                    }

                    function receiveFile(event) {
                        if (event.origin !== window.location.origin ||
                            event.source !== popup ||
                            !event.data ||
                            event.data.type !== 'crm-file-selected' ||
                            typeof event.data.url !== 'string') {
                            return;
                        }
                        window.removeEventListener('message', receiveFile);
                        let fileUrl;
                        try {
                            fileUrl = new URL(event.data.url, window.location.origin);
                        } catch (error) {
                            reportEditorError(new Error(translate('Please select a valid image file.')));
                            return;
                        }
                        if (fileUrl.protocol !== 'http:' && fileUrl.protocol !== 'https:') {
                            reportEditorError(new Error(translate('Please select a valid image file.')));
                            return;
                        }
                        const selectedFileName = typeof event.data.fileName === 'string'
                            ? event.data.fileName
                            : fileUrl.pathname.split('/').pop();
                        const fileName = selectedFileName.split(/[\\/]/).pop() || selectedFileName;
                        if (/\.(apng|avif|bmp|gif|jpe?g|png|svg|webp)$/i.test(fileName)) {
                            const image = document.createElement('img');
                            image.src = fileUrl.href;
                            image.alt = '';
                            image.style.maxWidth = '100%';
                            insertHtml(editor, image.outerHTML);
                        } else {
                            const link = document.createElement('a');
                            link.href = fileUrl.href;
                            link.textContent = fileName || translate('Download file');
                            insertHtml(editor, link.outerHTML);
                        }
                    }

                    window.addEventListener('message', receiveFile);
                    const closeWatcher = window.setInterval(function () {
                        if (popup.closed) {
                            window.clearInterval(closeWatcher);
                            window.removeEventListener('message', receiveFile);
                        }
                    }, 500);
                });
            }

            addButton(editor, 'insertImageUrl', 'Insert image URL', CK.IconImageUrl, function () {
                window.bootbox.prompt({
                    title: translate('Image URL'),
                    callback: function (source) {
                        if (!source) {
                            return;
                        }
                        let sourceUrl;
                        try {
                            sourceUrl = new URL(source, window.location.origin);
                        } catch (error) {
                            reportEditorError(new Error(translate('Please enter a valid image URL.')));
                            return;
                        }
                        if (sourceUrl.protocol !== 'http:' && sourceUrl.protocol !== 'https:') {
                            reportEditorError(new Error(translate('Please enter a valid image URL.')));
                            return;
                        }
                        const image = document.createElement('img');
                        image.src = sourceUrl.href;
                        image.alt = '';
                        insertHtml(editor, image.outerHTML);
                    }
                });
            });

            addButton(editor, 'insertVideo', 'Insert video', CK.IconMedia, function () {
                const form = document.createElement('div');
                const fields = {};
                const textField = function (key, label, type) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'form-group';
                    const labelElement = document.createElement('label');
                    labelElement.textContent = translate(label);
                    const input = document.createElement('input');
                    input.className = 'form-control';
                    input.type = type || 'text';
                    wrapper.append(labelElement, input);
                    form.appendChild(wrapper);
                    fields[key] = input;
                };
                const checkboxField = function (key, label, checked) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'form-check';
                    const input = document.createElement('input');
                    input.className = 'form-check-input';
                    input.type = 'checkbox';
                    input.checked = checked;
                    const labelElement = document.createElement('label');
                    labelElement.className = 'form-check-label';
                    labelElement.textContent = translate(label);
                    wrapper.append(input, labelElement);
                    form.appendChild(wrapper);
                    fields[key] = input;
                };

                textField('source', 'Video URL', 'url');
                textField('poster', 'Poster URL', 'url');
                textField('width', 'Width');
                textField('height', 'Height');
                textField('title', 'Title');
                checkboxField('controls', 'Show controls', true);
                checkboxField('responsive', 'Responsive video', true);
                checkboxField('autoplay', 'Autoplay', false);
                checkboxField('loop', 'Loop', false);
                checkboxField('allowDownload', 'Allow download', true);

                const alignment = document.createElement('select');
                alignment.className = 'form-control mt-2';
                [['none', 'None'], ['left', 'Left'], ['center', 'Center'], ['right', 'Right']]
                    .forEach(function (option) {
                        const item = document.createElement('option');
                        item.value = option[0];
                        item.textContent = translate(option[1]);
                        alignment.appendChild(item);
                    });
                form.appendChild(alignment);

                window.bootbox.dialog({
                    title: translate('Insert video'),
                    message: $(form),
                    buttons: {
                        cancel: {
                            label: translate('Cancel'),
                            className: 'btn-secondary'
                        },
                        insert: {
                            label: translate('Insert'),
                            className: 'btn-primary',
                            callback: function () {
                                let sourceUrl;
                                try {
                                    sourceUrl = new URL(fields.source.value, window.location.origin);
                                } catch (error) {
                                    reportEditorError(new Error(translate('Please enter a valid video URL.')));
                                    return false;
                                }
                                if (!fields.source.value ||
                                    (sourceUrl.protocol !== 'http:' && sourceUrl.protocol !== 'https:')) {
                                    reportEditorError(new Error(translate('Please enter a valid video URL.')));
                                    return false;
                                }

                                const wrapper = document.createElement('div');
                                wrapper.className = 'ckeditor-html5-video';
                                if (fields.responsive.checked) {
                                    wrapper.dataset.responsive = 'true';
                                }
                                if (alignment.value === 'center') {
                                    wrapper.style.textAlign = 'center';
                                } else if (alignment.value === 'left' || alignment.value === 'right') {
                                    wrapper.style.float = alignment.value;
                                    wrapper.style[alignment.value === 'left' ? 'marginRight' : 'marginLeft'] = '10px';
                                }

                                const video = document.createElement('video');
                                video.src = sourceUrl.href;
                                if (fields.controls.checked) {
                                    video.controls = true;
                                }
                                if (fields.autoplay.checked) {
                                    video.autoplay = true;
                                }
                                if (fields.loop.checked) {
                                    video.loop = true;
                                }
                                if (!fields.allowDownload.checked) {
                                    video.setAttribute('controlslist', 'nodownload');
                                }
                                if (fields.poster.value) {
                                    let posterUrl;
                                    try {
                                        posterUrl = new URL(fields.poster.value, window.location.origin);
                                    } catch (error) {
                                        reportEditorError(new Error(translate('Please enter a valid image URL.')));
                                        return false;
                                    }
                                    if (posterUrl.protocol !== 'http:' && posterUrl.protocol !== 'https:') {
                                        reportEditorError(new Error(translate('Please enter a valid image URL.')));
                                        return false;
                                    }
                                    video.poster = posterUrl.href;
                                }
                                if ((fields.width.value && !/^[1-9]\d*$/.test(fields.width.value)) ||
                                    (fields.height.value && !/^[1-9]\d*$/.test(fields.height.value))) {
                                    reportEditorError(new Error(translate('Please enter valid video dimensions.')));
                                    return false;
                                }
                                if (/^\d+$/.test(fields.width.value)) {
                                    video.width = Number(fields.width.value);
                                }
                                if (/^\d+$/.test(fields.height.value)) {
                                    video.height = Number(fields.height.value);
                                }
                                if (fields.title.value) {
                                    video.title = fields.title.value;
                                }
                                if (fields.responsive.checked) {
                                    video.style.maxWidth = '100%';
                                    video.style.height = 'auto';
                                }
                                wrapper.appendChild(video);
                                insertHtml(editor, wrapper.outerHTML);
                            }
                        }
                    }
                });
            });
        }
    }

    function createEditor(elementOrId, options) {
        options = options || {};
        const element = typeof elementOrId === 'string'
            ? document.getElementById(elementOrId)
            : elementOrId;
        if (!element) {
            const error = new Error('CKEditor 5 source element was not found: ' + elementOrId);
            reportEditorError(error);
            return Promise.reject(error);
        }

        const id = element.id;
        const previous = editorPromises.get(id);
        const pendingDestroy = pendingDestructions.get(id);
        const preset = options.preset || 'notes';
        const uploadType = options.uploadType || null;
        const locale = (CRM.lang || 'en').replace('_', '-').toLowerCase();
        const customFonts = (CRM.extraFont || '')
            .split(';')
            .map(function (font) {
                const separator = font.indexOf('/');
                const title = (separator === -1 ? font : font.slice(0, separator)).trim();
                const model = (separator === -1 ? font : font.slice(separator + 1)).trim();
                return title && model ? {title: title, model: model} : null;
            })
            .filter(function (font) {
                return font !== null;
            });
        const oldEditor = pendingDestroy || (previous
            ? previous.then(function (instance) {
                if (instance && instance.state !== 'destroyed') {
                    return instance.destroy();
                }
            })
            : Promise.resolve());

        const promise = oldEditor
            .then(function () {
                loadStyles();
                return readTranslation(locale);
            })
            .then(function (translations) {
                const plugins = [
                    CK.Essentials, CK.Paragraph, CK.Autoformat, CK.Heading, CK.Bold, CK.Italic,
                    CK.Underline, CK.Strikethrough, CK.Subscript, CK.Superscript,
                    CK.Font, CK.FontFamily, CK.FontSize, CK.FontColor, CK.FontBackgroundColor,
                    CK.Alignment, CK.List, CK.ListProperties, CK.Indent, CK.BlockQuote,
                    CK.Link, CK.Table, CK.TableToolbar, CK.Image, CK.ImageToolbar,
                    CK.TableProperties, CK.TableCellProperties, CK.ImageCaption,
                    CK.ImageStyle, CK.ImageResize,
                    CK.MediaEmbed, CK.HtmlEmbed, CK.SourceEditing, CK.GeneralHtmlSupport,
                    CK.PasteFromOffice, CK.FindAndReplace, CK.SelectAll, CK.RemoveFormat,
                    CK.SpecialCharacters, CK.SpecialCharactersEssentials, CK.Emoji,
                    CK.HorizontalLine, CK.PageBreak, CK.Fullscreen,
                    CK.Highlight, CRMEditorTools
                ];
                if (Array.isArray(options.plugins)) {
                    plugins.push(...options.plugins);
                }
                const language = locale.split('-')[0];
                const config = {
                    // CKEditor 5 is used under the GPL option selected for this deployment.
                    licenseKey: 'GPL',
                    plugins: plugins,
                    toolbar: {
                        items: buildToolbar(preset, options),
                        shouldNotGroupWhenFull: true
                    },
                    language: {ui: language, content: language},
                    translations: translations,
                    crm: {
                        preset: preset,
                        uploadType: uploadType,
                        mergeTags: options.mergeTags
                    },
                    fontFamily: {
                        options: ['default'].concat(customFonts, [
                            'Arial', 'Comic Sans MS', 'Courier New', 'Georgia',
                            'Lucida Sans Unicode', 'Tahoma', 'Times New Roman',
                            'Trebuchet MS', 'Verdana'
                        ]),
                        supportAllValues: true
                    },
                    fontSize: {
                        options: [9, 10, 11, 'default', 14, 16, 18, 20, 24, 28, 32],
                        supportAllValues: true
                    },
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
                    },
                    htmlSupport: {
                        allow: [
                            {name: 'div', classes: ['ckeditor-html5-video'], attributes: ['data-responsive'], styles: ['text-align', 'float', 'margin-left', 'margin-right']},
                            {name: 'video', attributes: ['src', 'poster', 'controls', 'autoplay', 'width', 'height', 'loop', 'controlslist', 'title'], styles: ['max-width', 'height']},
                            {name: 'source', attributes: ['src', 'type']},
                            {name: 'iframe', attributes: ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder']},
                            {name: /^(p|h[1-6]|td|th|table)$/, styles: ['text-align', 'vertical-align', 'width', 'height', 'border', 'background-color']},
                            {name: 'span', classes: ['math-tex'], styles: ['color', 'background-color', 'font-family', 'font-size', 'font-weight', 'font-style', 'text-decoration']},
                            {name: 'img', attributes: ['src', 'alt', 'width', 'height'], styles: ['width', 'height', 'max-width', 'float']},
                            {name: 'font', attributes: ['face', 'color', 'size']},
                            {name: 'a', attributes: ['href', 'target', 'rel', 'name']}
                        ].concat(Array.isArray(options.htmlSupportAllow) ? options.htmlSupportAllow : [])
                    },
                    mediaEmbed: {previewsInData: true}
                };

                if (typeof options.initialData === 'string') {
                    config.initialData = options.initialData;
                }

                if (uploadType) {
                    plugins.push(CK.ImageUpload);
                    plugins.push(class UploadAdapterPlugin extends CK.Plugin {
                        static get pluginName() {
                            return 'CRMUploadAdapter';
                        }

                        init() {
                            const fileRepository = this.editor.plugins.get('FileRepository');
                            fileRepository.createUploadAdapter = function (loader) {
                                return createUploadAdapter(this.editor, loader, uploadType);
                            }.bind(this);
                        }
                    });
                    plugins.push(createFileDropPlugin(uploadType));
                }

                const instance = CK.ClassicEditor.create(element, config);
                return instance.then(function (editor) {
                    const editingArea = editor.ui.view.editable.element;
                    editingArea.style.minHeight = preset === 'minimal' || preset === 'event' ? '250px' : '400px';
                    if (CRM.bDarkMode) {
                        editor.ui.view.element.classList.add('crm-dark-editor');
                    }
                    editorInstances.set(id, editor);
                    editorIds.set(editor, id);
                    return editor;
                });
            });

        editorIds.set(promise, id);
        editorPromises.set(id, promise);
        pendingDestructions.delete(id);
        promise.catch(function (error) {
            if (editorPromises.get(id) === promise) {
                editorPromises.delete(id);
            }
            reportEditorError(error);
        });
        return promise;
    }

    function destroyEditor(editorOrPromise) {
        if (!editorOrPromise) {
            return Promise.resolve();
        }

        const id = editorIds.get(editorOrPromise);
        const destruction = Promise.resolve(editorOrPromise).then(function (editor) {
            if (!editor) {
                return;
            }
            editorInstances.delete(id);
            if (editor.state !== 'destroyed') {
                return editor.destroy();
            }
        });
        if (id) {
            pendingDestructions.set(id, destruction);
            if (editorPromises.get(id) === editorOrPromise) {
                editorPromises.delete(id);
            }
        }
        destruction.catch(reportEditorError);
        return destruction;
    }

    CRM.createEditor = createEditor;
    CRM.destroyEditor = destroyEditor;
    CRM.getEditor = function (elementOrId) {
        const id = typeof elementOrId === 'string' ? elementOrId : elementOrId.id;
        const editor = editorInstances.get(id);
        if (!editor) {
            throw new Error('CKEditor 5 is not ready for element: ' + id);
        }
        return editor;
    };
})();
