DELETE FROM `plugin` WHERE `plgn_Name`='FamilyInfosDashboard';

-- uninstall plugin dependencies for FamilyInfosDashboard is done by the on delete trigger of the plugin table
