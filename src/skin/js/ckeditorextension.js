/*******************************************************************************
 *
 *  filename    : Calendar.php
 *  last change : 2018-05-13
 *  description : manage the full Calendar
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 *
 * to use it :
 *
 * Before the code see for example : CalendarV2.js
 *
 *  var editor = null; //the first time
 *
 *  if (editor != null) {
 *     editor.destroy(false);
 *     editor = null;              
 *  }
 * 
 *  editor = CKEDITOR.replace('NoteText',{
 *     customConfig: '<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/note_editor_config.js',
 *     language : window.CRM.lang
 *  });
 *   
 *  add_ckeditor_buttons(editor);
 *
 ******************************************************************************/
  
  // we add the special extraPlugin
  CKEDITOR.plugins.addExternal('dropdownmenumanager',window.CRM.root+'/skin/js/ckeditorExtraPlugin/dropdownmenumanager/', 'plugin.js');

  function add_ckeditor_buttons(editor)
  {
    CKEDITOR.dialog.add( 'saveDialog', function ( editor ) {
      return {
          title: i18next.t('CKEditor Template Creation'),
          minWidth: 400,
          minHeight: 200,

          contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'text',
                        className : 'name_ckeditor_custom_save',
                        id: 'nameID',
                        label: i18next.t("Template Name"),
                        validate: CKEDITOR.dialog.validate.notEmpty( i18next.t("Explanation field cannot be empty.") )
                    },
                    {
                        type: 'text',
                        className : 'description_ckeditor_custom_save',
                        id: 'descID',
                        label: i18next.t("Template Description"),
                        validate: CKEDITOR.dialog.validate.notEmpty( i18next.t("Explanation field cannot be empty.") )
                    }
                ]
            }
        ],
        onOk: function() {
          var dialog = this;
        
          var  title = $('.name_ckeditor_custom_save :input').val();
          var  desc = $('.description_ckeditor_custom_save  :input').val();
          var  text = editor.getData();
        
          window.CRM.APIRequest({
            method: 'POST',
            path: 'ckeditor/savetemplate',
            data: JSON.stringify({"personID":window.CRM.iPersonId,"title":title,"desc":desc,"text":text})
          }).done(function(data) {
            // reload toolbar
          }); 
        }    
      };
    });
    
    CKEDITOR.dialog.add( 'saveAsWordFileDialog', function ( editor ) {
      return {
          title: i18next.t('Save as Word file in EDrive'),
          minWidth: 400,
          minHeight: 100,

          contents: [
            {
                id: 'tab-basic',
                label: 'Basic Settings',
                elements: [
                    {
                        type: 'text',
                        className : 'name_ckeditor_custom_save',
                        id: 'nameID',
                        label: i18next.t("Word file name"),
                        validate: CKEDITOR.dialog.validate.notEmpty( i18next.t("Explanation field cannot be empty.") )
                    },
                    {
                        type : 'html',
                        html : '<div id="myDiv">'+i18next.t("The file will be uploaded to your EDrive<br><small>This part is for test only <b>(Beta)</b>.</small>")+'</div>'
                    }
                ]
            }
        ],
        onOk: function() {
          var dialog = this;
        
          var  title = $('.name_ckeditor_custom_save :input').val();
          var  text = editor.getData();
        
          window.CRM.APIRequest({
            method: 'POST',
            path: 'ckeditor/saveAsWordFile',
            data: JSON.stringify({"personID":window.CRM.iPersonId,"title":title,"text":text})
          }).done(function(data) {
            alert (i18next.t('Your note is saved in your EDrive'));
          }); 
        }    
      };
    });
  
    CKEDITOR.dialog.add("templatesDialog", function(c) {
        var currentTemplateID    = 0;
        var currentTitle         = "";
        var currentDesc          = "";
        
        function addTemplates(ed)
        {
          var a = ed.getContentElement("selectTpl", "templatesList");
          g = a.getElement();
          window.CRM.APIRequest({
            method: 'POST',
            path: 'ckeditor/alltemplates',
            data: JSON.stringify({"personID":window.CRM.iPersonId})
          }).done(function(b) {    
              if (b.length) {
                  var c = g;
                  c.setHtml("");

                  var l = 0;
                  for (var d = 0; d < b.length; d++) {
                      var p = r(b[d], window.CRM.root+"/Images/");
                      
                      p.setAttribute("aria-posinset", l++ + 1);
                      p.setAttribute("aria-setsize", b.length);
                      c.append(p)
                  }
                  a.focus()
              } else
                  g.setHtml('\x3cdiv class\x3d"cke_tpl_empty"\x3e\x3cspan\x3e' + i18next.t("(No templates defined)") + "\x3c/span\x3e\x3c/div\x3e")
          });
          ed._.element.on("keydown", k);  
        }
        
        function r(a, b) {
          var m = CKEDITOR.dom.element.createFromHtml('\x3ca href\x3d"javascript:void(0)" tabIndex\x3d"-1" role\x3d"option" \x3e\x3cdiv class\x3d"cke_tpl_item"\x3e\x3c/div\x3e\x3c/a\x3e'),
              d = '\x3ctable style\x3d"width:350px;" class\x3d"cke_tpl_preview" role\x3d"presentation"\x3e\x3ctr\x3e';
          a.image && b && (d += '\x3ctd class\x3d"cke_tpl_preview_img"\x3e\x3cimg src\x3d"' + CKEDITOR.getUrl(b + a.image) + '"' + (CKEDITOR.env.ie6Compat ? ' onload\x3d"this.width\x3dthis.width"' :
          "") + ' alt\x3d"" title\x3d""\x3e\x3c/td\x3e');
          d += '\x3ctd style\x3d"white-space:normal;"\x3e\x3cspan class\x3d"cke_tpl_title"\x3e' + a.title + "\x3c/span\x3e\x3cbr/\x3e";
          a.description && (d += "\x3cspan\x3e" + a.description + "\x3c/span\x3e");
          d += "\x3c/td\x3e\x3c/tr\x3e\x3c/table\x3e";
          m.getFirst().setHtml(d);
          m.on("click", function() {
              t(a.title,a.description,a.id)
          });
          return m
        }

        function t(title,desc,id) {
            $('.name_ckeditor_custom_template :input').val(title);
            $('.description_ckeditor_custom_template :input').val(desc);
            
            currentTemplateID = id;
            currentTitle      = title;
            currentDesc       = desc;
        }
        
        function k(a) {
            var b = a.data.getTarget(),
                c = g.equals(b);
            if (c || g.contains(b)) {
                var d = a.data.getKeystroke(),
                    f = g.getElementsByTag("a"),
                    e;
                if (f) {
                    if (c)
                        e = f.getItem(0);
                    else
                        switch (d) {
                        case 40:
                            e = b.getNext();
                            break;
                        case 38:
                            e = b.getPrevious();
                            break;
                        case 13:
                        case 32:
                            b.fire("click")
                        }
                    e && (e.focus(), a.data.preventDefault())
                }
            }
        }
        
        
        var h = "cke_tpl_list_label_" + CKEDITOR.tools.getNextNumber(),
            templates_files = [ '/api/ckeditor/'+window.CRM.iPersonId+'/templates' ];// we use api to get the templates in the DB
        return {
            title: i18next.t("Content Templates"),
            minWidth: CKEDITOR.env.ie ? 440 : 400,
            minHeight: 340,
            contents: [{
                id: "selectTpl",
                label: i18next.t("Content Templates"),
                elements: [{
                    type: "vbox",
                    padding: 5,
                    children: [{
                        id: "selectTplText",
                        type: "html",
                        html: "\x3cspan\x3e" + i18next.t("Please select the template to open in the editor") + "\x3c/span\x3e"
                    }, {
                        id: "templatesList",
                        type: "html",
                        focus: !0,
                        html: '\x3cdiv class\x3d"cke_tpl_list" tabIndex\x3d"-1" role\x3d"listbox" aria-labelledby\x3d"' +
                        h + '"\x3e\x3cdiv class\x3d"cke_tpl_loading"\x3e\x3cspan\x3e\x3c/span\x3e\x3c/div\x3e\x3c/div\x3e\x3cspan class\x3d"cke_voice_label" id\x3d"' + h + '"\x3e' + i18next.t("Template Options")+ "\x3c/span\x3e"
                    },
                    {
                        type: 'text',
                        className : 'name_ckeditor_custom_template',
                        id: 'nameID',
                        label: i18next.t("Template Name"),
                        validate: CKEDITOR.dialog.validate.notEmpty( i18next.t("Explanation field cannot be empty.") )
                    },
                    {
                        type: 'text',
                        className : 'description_ckeditor_custom_template',
                        id: 'descID',
                        label: i18next.t("Template Description"),
                        validate: CKEDITOR.dialog.validate.notEmpty( i18next.t("Explanation field cannot be empty.") )
                    }]
                }]
            }],
            onShow: function() {
                addTemplates(this);
            },
            onHide: function() {
                this._.element.removeListener("keydown", k)
            },
            buttons: [
              CKEDITOR.dialog.cancelButton,
              {
                  type: 'button',
                  id: 'modifyId',
                  label: i18next.t("Modify"),
                  className : 'cke_dialog_ui_button_modify',
                  title: i18next.t("Modify the template you select"),
                  onClick: function() {
                    if (currentTemplateID > 0) {
                      var  title = $('.name_ckeditor_custom_template :input').val();                        
                      var  desc = $('.description_ckeditor_custom_template  :input').val();

                      window.CRM.APIRequest({
                        method: 'POST',
                        path: 'ckeditor/renametemplate',
                        data: JSON.stringify({"templateID":currentTemplateID,"title":title,"desc":desc})
                      }).done(function(data) {
                        addTemplates(CKEDITOR.dialog.getCurrent());
                      });
                    }
                  }
              },
              {
                  type: 'button',
                  id: 'deleteId',
                  label: i18next.t("Delete"),
                  className : 'cke_dialog_ui_button_danger',
                  title: i18next.t("Delete the template you select"),
                  onClick: function() {
                    var r = confirm(i18next.t("Are you sure to delete this template? This can't be undone."));
              
                    if (r == true && currentTemplateID > 0) {
                      window.CRM.APIRequest({
                        method: 'POST',
                        path: 'ckeditor/deletetemplate',
                        data: JSON.stringify({"templateID":currentTemplateID})
                      }).done(function(data) {
                        addTemplates(CKEDITOR.dialog.getCurrent());
                      });
                    }
                  }
              },
              {
                  type: 'button',
                  id: 'okId',
                  label: i18next.t("OK"),
                  className : 'cke_dialog_ui_button_ok',
                  title: i18next.t("Delete the template you select"),
                  onClick: function() {
                    CKEDITOR.dialog.getCurrent().hide();
                  }
              }
            ]
        }
    });
    
    CKEDITOR.dialog.add("templatesApplyDialog", function(c) {
        function r(a, b) {
          var m = CKEDITOR.dom.element.createFromHtml('\x3ca href\x3d"javascript:void(0)" tabIndex\x3d"-1" role\x3d"option" \x3e\x3cdiv class\x3d"cke_tpl_item"\x3e\x3c/div\x3e\x3c/a\x3e'),
              d = '\x3ctable style\x3d"width:350px;" class\x3d"cke_tpl_preview" role\x3d"presentation"\x3e\x3ctr\x3e';
          a.image && b && (d += '\x3ctd class\x3d"cke_tpl_preview_img"\x3e\x3cimg src\x3d"' + CKEDITOR.getUrl(b + a.image) + '"' + (CKEDITOR.env.ie6Compat ? ' onload\x3d"this.width\x3dthis.width"' :
          "") + ' alt\x3d"" title\x3d""\x3e\x3c/td\x3e');
          d += '\x3ctd style\x3d"white-space:normal;"\x3e\x3cspan class\x3d"cke_tpl_title"\x3e' + a.title + "\x3c/span\x3e\x3cbr/\x3e";
          a.description && (d += "\x3cspan\x3e" + a.description + "\x3c/span\x3e");
          d += "\x3c/td\x3e\x3c/tr\x3e\x3c/table\x3e";
          m.getFirst().setHtml(d);
          m.on("click", function() {
              t(a.html)
          });
          return m
        }

        function t(a) {
            var b = CKEDITOR.dialog.getCurrent();
            b.getValueOf("selectTpl", "chkInsertOpt") ? (c.fire("saveSnapshot"), c.setData(a, function() {
                b.hide();
                var a = c.createRange();
                a.moveToElementEditStart(c.editable());
                a.select();
                setTimeout(function() {
                    c.fire("saveSnapshot")
                }, 0)
            })) : (c.insertHtml(a), b.hide())
        }
        function k(a) {
            var b = a.data.getTarget(),
                c = g.equals(b);
            if (c || g.contains(b)) {
                var d = a.data.getKeystroke(),
                    f = g.getElementsByTag("a"),
                    e;
                if (f) {
                    if (c)
                        e = f.getItem(0);
                    else
                        switch (d) {
                        case 40:
                            e = b.getNext();
                            break;
                        case 38:
                            e = b.getPrevious();
                            break;
                        case 13:
                        case 32:
                            b.fire("click")
                        }
                    e && (e.focus(), a.data.preventDefault())
                }
            }
        }
        
        var h = "cke_tpl_list_label_" + CKEDITOR.tools.getNextNumber(),
            templates_files = [ '/api/ckeditor/'+window.CRM.iPersonId+'/templates' ];// we use api to get the templates in the DB
        return {
            title: i18next.t("Content Templates"),
            minWidth: CKEDITOR.env.ie ? 440 : 400,
            minHeight: 340,
            contents: [{
                id: "selectTpl",
                label: i18next.t("Content Templates"),
                elements: [{
                    type: "vbox",
                    padding: 5,
                    children: [{
                        id: "selectTplText",
                        type: "html",
                        html: "\x3cspan\x3e" + i18next.t("Please select the template to open in the editor") + "\x3c/span\x3e"
                    }, {
                        id: "templatesList",
                        type: "html",
                        focus: !0,
                        html: '\x3cdiv class\x3d"cke_tpl_list" tabIndex\x3d"-1" role\x3d"listbox" aria-labelledby\x3d"' +
                        h + '"\x3e\x3cdiv class\x3d"cke_tpl_loading"\x3e\x3cspan\x3e\x3c/span\x3e\x3c/div\x3e\x3c/div\x3e\x3cspan class\x3d"cke_voice_label" id\x3d"' + h + '"\x3e' + i18next.t("Template Options")+ "\x3c/span\x3e"
                    }, {
                        id: "chkInsertOpt",
                        type: "checkbox",
                        label: i18next.t("Replace actual content"),
                        "default": true
                    }]
                }]
            }],
            buttons: [CKEDITOR.dialog.cancelButton],
            onShow: function() {
                var a = this.getContentElement("selectTpl", "templatesList");
                g = a.getElement();
                window.CRM.APIRequest({
                  method: 'POST',
                  path: 'ckeditor/alltemplates',
                  data: JSON.stringify({"personID":window.CRM.iPersonId})
                }).done(function(b) {    
                    if (b.length) {
                        var c = g;
                        c.setHtml("");

                        var l = 0;
                        for (var d = 0; d < b.length; d++) {
                          var p = r(b[d], window.CRM.root+"/Images/");
                            
                          p.setAttribute("aria-posinset", l++ + 1);
                          p.setAttribute("aria-setsize", b.length);
                          c.append(p)
                        }
                        a.focus()
                    } else
                        g.setHtml('\x3cdiv class\x3d"cke_tpl_empty"\x3e\x3cspan\x3e' + i18next.t("(No templates defined)") + "\x3c/span\x3e\x3c/div\x3e")
                });
                this._.element.on("keydown", k)
            },
            onHide: function() {
                this._.element.removeListener("keydown", k)
            }
        }
    });
      
    editor.addCommand( 'saveTemplates', new CKEDITOR.dialogCommand( 'saveDialog' ) );
    editor.addCommand( 'manageTemplates', new CKEDITOR.dialogCommand( 'templatesDialog' ) );
    editor.addCommand( 'applyTemplates', new CKEDITOR.dialogCommand( 'templatesApplyDialog' ) );
    editor.addCommand( 'saveAsWordFile', new CKEDITOR.dialogCommand( 'saveAsWordFileDialog' ) );
    

    // create the templates command
    editor.ui.addButton('ManageTemplateButton', { // add new button and bind our command to the template group
      label: i18next.t("Manage templates"),
      command: 'manageTemplates',
      toolbar: 'template',
      icon: window.CRM.root+'/skin/external/ckeditor/plugins/newpage/icons/hidpi/newpage.png'
    });

    editor.ui.addButton('SaveTemplateButton', { // add new button and bind our command to the template group
      label: i18next.t("Save templates"),
      command: 'saveTemplates',
      toolbar: 'template',
      icon: window.CRM.root+'/skin/external/ckeditor/plugins/save/icons/hidpi/save.png'
    });
    
    editor.ui.addButton('ApplyTemplateButton', { // add new button and bind our command to the template group
      label: i18next.t("Apply templates"),
      command: 'applyTemplates',
      toolbar: 'template',
      icon: window.CRM.root+'/skin/external/ckeditor/plugins/templates/icons/hidpi/templates.png'
    });

    editor.ui.addButton('SaveAsWordFileButton', { // add new button and bind our command to the export group
      label: i18next.t("Save As Word File in EDrive"),
      command: 'saveAsWordFile',
      toolbar: 'export',
      icon: window.CRM.root+'/skin/external/ckeditor/plugins/save/icons/hidpi/save.png'
    });
    
    //  create the MathJax command
    editor.ui.addButton('mathjax',{
      label: 'Add Math Formula',
      command: 'mathjax',
      icon: CKEDITOR.plugins.getPath('mathjax') + 'edu_mathematics.png'
    });
  }
  
  function add_ckeditor_buttons_merge_tag_mailchimp (editor) {
    editor.addCommand( 'optionFNAME', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|FNAME|*' );
        }
    });
    
    editor.addCommand( 'optionLNAME', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|LNAME|*' );
        }
    });
    
    editor.addCommand( 'optionEmail', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|EMAIL|*' );
        }
    });
    
    editor.addCommand( 'optionPhone', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|PHONE|*' );
        }
    });
    
    editor.addCommand( 'optionAddress', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|ADDRESS|*' );
        }
    });

    editor.addCommand( 'optionListName', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|LIST:NAME|*' );
        }
    });
    
    editor.addCommand( 'optionListCompany', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|LIST:COMPANY|*' );
        }
    });
    
    editor.addCommand( 'optionListUnsub', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|UNSUB|*' );
        }
    });
    
    editor.addCommand( 'optionListAddressVcard', {
        exec: function( editor ) {
            var now = new Date();
            editor.insertHtml( '*|LIST:ADDRESS_VCARD|*' );
        }
    });
  }
