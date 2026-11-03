DELETE FROM `plugin` WHERE `plgn_Name`='PersonInfosDashboard';

-- uninstall plugin dependencies for PersonInfosDashboard is done by the on delete trigger of the plugin table
