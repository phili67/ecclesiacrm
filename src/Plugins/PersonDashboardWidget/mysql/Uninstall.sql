DELETE FROM `plugin` WHERE `plgn_Name`='PersonDashboardWidget';

-- uninstall plugin dependencies for PersonDashboardWidget is done by the on delete trigger of the plugin table