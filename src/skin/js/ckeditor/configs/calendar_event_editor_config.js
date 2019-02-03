/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/terms-of-use/#open-source-licences
 */

CKEDITOR.editorConfig = function( config ) {
  config.height = '150px';
    
  config.toolbarGroups = [
    { name: 'styles', groups: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
    { name: 'document', groups: [ 'mode' ] },//'source',
    '/',
    { name: 'clipboard', groups: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
    { name: 'colors', groups: [ 'colors' ] },
    { name: 'tools', groups: [ 'tools' ] },
    { name: 'others', groups: [ 'others' ] }
  ];
  
  config.mathJaxLib = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML';

  config.plugins =
    'undo,' +
    'colorbutton,' +
    'colordialog,' +
    'font,' +
    'format,' +
    'horizontalrule,' +
    'image,' +
    'justify,' +
    'link,' +
    'list,' +
    'pastefromword,' +
    'pastetext,' +
    'resize,' +
    'tableselection,' +
    'smiley,' +
    'maximize,' +
    'mathjax,' +
    'wysiwygarea';
};
