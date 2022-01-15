/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 */

CKEDITOR.editorConfig = function (config) {
    config.height = '400px';

    config.dropdownmenumanager = {
        'mergeTagsMailChimp': {
            items: [
                {
                    name: 'fName',
                    label: 'First Name',
                    command: 'optionFNAME',
                    order: 1
                },
                {
                    name: 'LName',
                    label: 'Last Name',
                    command: 'optionLNAME',
                    order: 2
                },
                {
                    name: 'Email',
                    label: 'Email',
                    command: 'optionEMail',
                    order: 3
                },
            ],
            label: {
                text: 'Widgets',
                width: 45,
                visible: true //default value
            },
            iconPath: '/images/widgetIcon.png',
            //toolbar: 'tools' // to specify toolbar group for button
        },
    };

    var documentTools = ['Preview', 'Print'];

    if (window.CRM.bHtmlSourceEditor != "") {
        documentTools = ['Source', '-', 'Preview', 'Print'];
    }


    config.toolbar = [
        {name: 'document', items: documentTools},
        {name: 'export', items: ['export', ((window.CRM.bEDrive) ? 'SaveAsWordFileButton' : 'none')]},
        {name: 'template', items: ['document', 'ApplyTemplateButton', 'ManageTemplateButton', 'SaveTemplateButton']},//'source',
        {name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
        {name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll']},
        //{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
        '/',
        {
            name: 'basicstyles',
            items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat']
        },
        {
            name: 'paragraph',
            items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-']
        },
        '/',
        {
            name: 'textmanagement',
            items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language']
        },
        {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
        {
            name: 'insert',
            items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe', '-', 'mathjax']
        },
        '/',
        {name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
        {name: 'colors', items: ['TextColor', 'BGColor']},
        {name: 'tools', items: ['Maximize', 'ShowBlocks']},
        {name: 'about', items: ['About']}
    ];

    config.mathJaxLib = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML';

    config.plugins =
        'about,' +
        'sourcearea,' +
        //'forms,' +
        'blockquote,' +
        'clipboard,' +
        'colorbutton,' +
        'colordialog,' +
        'copyformatting,' +
        'dialogadvtab,' +
        'elementspath,' +
        'enterkey,' +
        'entities,' +
        'basicstyles,' +
        'iframe,' +
        'find,' +
        'floatingspace,' +
        'font,' +
        'format,' +
        'horizontalrule,' +
        'image,' +
        'indentlist,' +
        'indentblock,' +
        'justify,' +
        'link,' +
        'list,' +
        'magicline,' +
        'maximize,' +
        'pastefromword,' +
        'pastetext,' +
        'preview,' +
        'print,' +
        'removeformat,' +
        'resize,' +
        'selectall,' +
        'smiley,' +
        'specialchar,' +
        'stylescombo,' +
        'tab,' +
        'table,' +
        'tableselection,' +
        'tabletools,' +
        'undo,' +
        'wysiwygarea,' +
        'mathjax,' +
        'dropdownmenumanager';
};
