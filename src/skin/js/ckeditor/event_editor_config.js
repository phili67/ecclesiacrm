/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 */

CKEDITOR.editorConfig = function( config ) {
  config.height = '250px';
  
    config.toolbarGroups = [
    { name: 'document', groups: [ 'mode', 'document'] },
    { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
    { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
    { name: 'forms', groups: [ 'forms' ] },
    '/',
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
    { name: 'links', groups: [ 'links' ] },
    { name: 'insert', groups: [ 'insert' ] },
    '/',
    { name: 'styles', groups: [ 'styles' ] },
    { name: 'colors', groups: [ 'colors' ] },
    { name: 'tools', groups: [ 'tools' ] },
    { name: 'others', groups: [ 'others' ] }
  ];
  
  config.mathJaxLib = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML';

  config.plugins =
    'about,' +
    'blockquote,' +
    'clipboard,' +
    'colorbutton,' +
    'colordialog,' +
    'copyformatting,' +
    'dialogadvtab,' +
    'elementspath,' +
    'enterkey,' +
    'entities,' +
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
    'templates,' +
    'undo,' +
    'mathjax,' +
    'wysiwygarea';
};
