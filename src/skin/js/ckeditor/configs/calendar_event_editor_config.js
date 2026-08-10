/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 */

CKEDITOR.editorConfig = function (config) {
    config.fileTools_requestHeaders = { 'X-CSRF-Token': window.CRM.csrfToken };
    config.height = '250px';
    config.contentsCss = window.CRM.contentsExternalCssFont;
    config.versionCheck = false;

    var documentTools = ['Preview', 'Print'];

    //the next line add the new font to the combobox in CKEditor
    config.font_names = "Arial/Arial, Helvetica, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif";

    if (window.CRM.extraFont != "") {
        config.font_names = window.CRM.extraFont + ';' + config.font_names;
    }

    var clipboard = ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'];

    config.toolbar = [
        {name: 'document', items: documentTools.concat(clipboard)},
        {name: 'colors', items: ['TextColor', 'BGColor']},        
        {
            name: 'basicstyles',
            items: ['Bold', 'Italic', 'Underline']
        },
        {name: 'links', items: ['Link', 'Unlink', 'Anchor']},
        {
            name: 'insert',
            items: ['Image', 'Html5video', 'Table', 'HorizontalRule']
        },
        {
            name: 'tools',
            items: ['Maximize']
        }
    ];

    config.plugins =
        'about,' +
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
        'html5video';
};
