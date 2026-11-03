INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`, `plgn_UserRole_Dashboard_Availability`)
VALUES ('FamilyDashboardWidget', 'Widget Dashboard Plugin : to see families informations', 'Dashboard', NULL, '', '1', '1.1', 'fam_w_', 'inside_category_menu', 'widget', 'bg-gradient-blue text-white',1073741824,0);

INSERT INTO `plugin_dependencies` (`plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`) VALUES
(LAST_INSERT_ID(), CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'FamilyDashboardWidgetDashboardItemPlugin'), 'synchronize'),
(LAST_INSERT_ID(), 'Plugins/FamilyDashboardWidget/skin/js/synchronize/FamilyDashboardWidgetDashboardItem.js', 'global_js');
