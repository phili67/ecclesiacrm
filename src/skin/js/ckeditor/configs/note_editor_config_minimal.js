/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 */

CKEDITOR.editorConfig = function (config) {
    config.height = '400px';
    

    config.toolbar = [        
        {
            name: 'basicstyles',
            items: ['Bold', 'Italic', 'Underline']
        },        
        {
            name: 'textmanagement',
            items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language', '-', 'TextColor', 'BGColor']
        }
    ];

    config.plugins =
        'about,' +
        'sourcearea,' +
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
        'dropdownmenumanager,' +
        'html5video';
};
