/**
 *  Plugin that registers dropdowns which description are read from the configuration.
 *  @author Radoslav Petkov
 **/
'use strict';

CKEDITOR.plugins.add('dropdownmenumanager', {
    requires: 'menu,menubutton',
    icons: 'dropdown',
    init: pluginInit
});

/**
 *  Init function that registers a DropdownMenuManager instance
 *  to manage the passed from the CKEditor config dropdown menus' description.
 *  @param {Object}
 */
function pluginInit(editor) {
    var dropdownMenuManager = new DropdownMenuManager();
    dropdownMenuManager.readConfiguration(editor);

    var menusArray = [];
    var menus = dropdownMenuManager.getMenus();
    for (var menu in menus) {
        if (menus.hasOwnProperty(menu)) {
            menusArray.push(menus[menu]);
        }
    }

    menusArray.forEach(addDropdownIntoEditor);

    /**
     *  Adds the menu items and the toolbar button to the editor.
     *  @param {Object} menu Holds information about particular dropdown menu
     */
    function addDropdownIntoEditor(menu) {
        editor.addMenuItems(menu.getItems());
        if (menu.getMenuLabel()) {
            CKEDITOR.addCss('.cke_button__' + menu.getMenuGroup().toLowerCase() + '_label{display: inline !important;overflow:hidden;width:' + menu.getLabelWidth() + 'px;}');
        }
        editor.ui.add(menu.getMenuGroup(), CKEDITOR.UI_MENUBUTTON, {
            label: menu.getMenuLabel(),
            icon: 'dropdown',
            name: menu.getMenuGroup(),
            onMenu: function() {
                var active = {};
                for (var p in menu.getItems()) {
                    active[p] = CKEDITOR.TRISTATE_OFF;
                }
                return active;
            }
        });
    }
}

/**
 *  Class used to hold dropdown menus and read their description from the editor's config.
 **/
function DropdownMenuManager() {
    var dropdownMenus = {};

    this.addMenuGroup = function(menuGroup, definition) {
        dropdownMenus[menuGroup] = new DropdownMenu(definition);
    };

    this.addItem = function(menuGroup, itemDesc) {
        dropdownMenus[menuGroup].addItem(itemDesc);
    };

    this.readConfiguration = function(editor) {
        var config = editor.config.dropdownmenumanager;
        for (var menuGroup in config) {
            if (config.hasOwnProperty(menuGroup)) {
                this.addMenuGroup(menuGroup, {
                    name: menuGroup,
                    label: config[menuGroup].label ? config[menuGroup].label.text : '',
                    width: config[menuGroup].label ? config[menuGroup].label.width : 0,
                });
                editor.addMenuGroup(menuGroup);
                var itemsOfMenuGroup = config[menuGroup].items;
                for (var i = 0; i < itemsOfMenuGroup.length; i++) {
                    this.addItem(menuGroup, itemsOfMenuGroup[i]);
                }
            }
        }
    };

    this.getMenus = function() {
        return dropdownMenus;
    };
}

/**
 *  Class used to hold items in particular dropdown menu.
 *  @param {String} Name of the menu group that this dropdown adds its items.
 **/
function DropdownMenu(menuGroup) {
    var items = {};

    this.getItems = function() {
        return items;
    };

    this.addItem = function(item) {
        item['group'] = menuGroup.name;
        item['role'] = 'menuitemcheckbox';
        items[item['label']] = item;
    };

    this.getLabelWidth = function() {
        return menuGroup.width;
    }

    this.getMenuGroup = function() {
        return menuGroup.name;
    };

    this.getMenuLabel = function() {
        return menuGroup.label ? menuGroup.label : '';
    }
}