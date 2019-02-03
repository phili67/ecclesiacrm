# Dropdown menu manager

This plugin adds the feature to describe with the help of the   CKEditor's configuration various dropdowns and populate them with desirable items.

## Configuration example

```javascript
config.dropdownmenumanager = {
        'DropdownMenuA': [{
            label: 'pluginUIToolbarButtonInLowerCase',
            command: 'theCommandExecutedOnClick',
            order: 1
        }, {
            label: 'pluginUIToolbarButtonInLowerCase',
            command: 'theCommandExecutedOnClick',
            order: 2
        }],
        'DropdownMenuB': [{
            label: 'pluginUIToolbarButtonInLowerCase',
            command: 'theCommandExecutedOnClick',
            order: 1
        }],
        label: {
                text: 'This will be shown next to the icon',
                width: 30 //width of the whole button
        }
    };
```

To add the dropdowns on the toolbar use the keys in the 'config.dropdownmenumanager' object , in this case:
'DropdownMenuA' and 'DropdownMenuB'