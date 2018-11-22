/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 */

CKEDITOR.editorConfig = function( config ) {
  config.height = '400px';
    
  config.dropdownmenumanager = {
    'mergeTagsMailChimp': {
      items: [
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
        },
        {
          name: 'Phone',
          label: i18next.t('Phone'),
          command: 'optionPhone',
          order: 4
        },
        {
          name: 'Address',
          label: i18next.t('Address'),
          command: 'optionAddress',
          order: 5
        },
        {
          name: 'ListName',
          label: i18next.t('List') + ' : ' + i18next.t('Name'),
          command: 'optionListName',
          order: 6
        },
        {
          name: 'ListCompany',
          label: i18next.t('List') +' : '+i18next.t('Company'),
          command: 'optionListCompany',
          order: 7
        },
        {
          name: 'ListUnsub',
          label: i18next.t('List') +' : ' + i18next.t('Unsub'),
          command: 'optionListUnsub',
          order: 8
        },
        {
          name: 'ListVCard',
          label: i18next.t('List') + ' : ' + i18next.t('VCard'),
          command: 'optionListAddressVcard',
          order: 8
        }
      ],
      label: {
        text: i18next.t('Merge Tags'),
        width: 45,
        visible:true //default value
      },
      iconPath:'/images/widgetIcon.png',
      //toolbar: 'tools' // to specify toolbar group for button
    },
  };

  config.toolbar = [
    { name: 'document', items: [ 'Preview', 'Print', '-' , 'mergeTagsMailChimp' ] },
    { name: 'export', items: [ 'export', 'SaveAsWordFileButton' ] },
    { name: 'template', items: [ 'document', 'ApplyTemplateButton','ManageTemplateButton','SaveTemplateButton'] },//'source',
    { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
    { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll'] },
    //{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
    '/',
    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
    { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
    { name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe','-', 'mathjax' ] },
    '/',
    { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
    { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
    { name: 'about', items: [ 'About' ] }
  ];
   
  config.mathJaxLib = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML';

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
    'iframe,'+
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
