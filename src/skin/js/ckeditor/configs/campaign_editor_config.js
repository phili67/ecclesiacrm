/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 * Copyright : Philippe Logel not MIT
 */

CKEDITOR.editorConfig = function (config) {
    config.height = '400px';

    config.contentsCss = window.CRM.contentsExternalCssFont;

    //the next line add the new font to the combobox in CKEditor
    config.font_names = "Arial/Arial, Helvetica, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif";

    if (window.CRM.extraFont != "") {
        config.font_names = window.CRM.extraFont + ';' + config.font_names;
    }

    var menuItems = [
        {
            name: 'fName',
            label: i18next.t('First Name'),
            command: 'optionFNAME',
            order: 1
        },
        {
            name: 'lName',
            label: i18next.t('Last Name'),
            command: 'optionLNAME',
            order: 2
        },
        {
            name: 'Email',
            label: i18next.t('Email'),
            command: 'optionEmail',
            order: 3
        }
    ];

    if (window.CRM.bWithAddressPhone) {
        menuItems.push(
            {
                name: 'Address',
                label: i18next.t('Address'),
                command: 'optionAddress',
                order: 4
            },
            {
                name: 'Phone',
                label: i18next.t('Phone'),
                command: 'optionPhone',
                order: 5
            }
        );
    }

    menuItems.push(
        {
            name: 'ListName',
            label: i18next.t('List') + ' : ' + i18next.t('Name'),
            command: 'optionListName',
            order: 6
        },
        {
            name: 'ListCompany',
            label: i18next.t('List') + ' : ' + i18next.t('Company'),
            command: 'optionListCompany',
            order: 7
        },
        {
            name: 'ListDescription',
            label: i18next.t('List') + ' : ' + i18next.t('Description'),
            command: 'optionListDescription',
            order: 8
        },
        {
            name: 'ListUnsub',
            label: i18next.t('List') + ' : ' + i18next.t('Unsub'),
            command: 'optionListUnsub',
            order: 9
        },
        {
            name: 'ListVCard',
            label: i18next.t('List') + ' : ' + i18next.t('VCard'),
            command: 'optionListAddressVcard',
            order: 10
        },
        {
            name: 'Date',
            label: i18next.t('Date'),
            command: 'optionDate',
            order: 11
        },
        {
            name: 'Language',
            label: i18next.t('Language'),
            command: 'optionTRANSLATE',
            order: 12
        }
    );


    config.dropdownmenumanager = {
        'mergeTagsMailChimp': {
            items: menuItems,
            label: {
                text: i18next.t('Merge Tags'),
                width: 45,
                visible: true //default value
            },
            iconPath: '/images/widgetIcon.png',
            //toolbar: 'tools' // to specify toolbar group for button
        },
    };

    config.toolbar = [
        {name: 'document', items: ['Source', '-', 'Preview', 'Print', '-', 'mergeTagsMailChimp']},
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
            items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language', '-', 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-',]
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
        'sourcedialog,' +
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
