INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`, `plgn_UserRole_Dashboard_Availability`)
VALUES ('PersonDashboardWidget', 'Widget Dashboard Plugin : to see persons informations', 'Dashboard', NULL, '', '1', '1.1', 'person_w_', 'inside_category_menu', 'widget', 'bg-gradient-purple text-white',1073741824,0);

INSERT INTO `plugin_dependencies` ( `plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`) VALUES
(LAST_INSERT_ID(), CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardWidgetDashboardItemPlugin'), 'synchronize'),
(LAST_INSERT_ID(), 'Plugins/PersonDashboardWidget/skin/js/synchronize/PersonDashboardWidgetDashboardItem.js', 'global_js');
