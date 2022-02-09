To set up other xml **schemas**, for example :
- an example with the meetingjitsi.schema.xml
- you've to create the database like this

    ```<database package="MeetingJitsi" name="pluginstore" nameSpace="PluginStore">```

- see always : propel.php file to add pluginstore
- See always the : **src/EcclesiaCRM/Bootstrap.php** file initPropel ('main' and "pluginstore")
- Last see the code src/EcclesiaCRM/loadDataBase.php file ...

# attention :
```<database package="MeetingJitsi" name="pluginstore" nameSpace="PluginStore">```

    # package Name must feet (in src/Plugins/MeetingJitsi) to the Folder Name of the Plugin (package="MeetingJitsi")

propel/package is experimental with no warranties.

# To generate all

