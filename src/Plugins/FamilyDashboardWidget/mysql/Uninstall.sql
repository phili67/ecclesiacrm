DELETE FROM `plugin` WHERE `plgn_Name`='FamilyDashboardWidget';

-- uninstall plugin dependencies for FamilyDashboardWidget is done by the on delete trigger of the plugin table
