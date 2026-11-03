INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`)
VALUES ('FamilyInfosDashboard', 'Dashboard Plugin for family infos', 'Dashboard', NULL, '', '1', '1.1', 'pid_', 'inside_category_menu', 'right', 'bg-gradient-blue text-white',32);

INSERT INTO `plugin_dependencies` (`plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`) VALUES
(LAST_INSERT_ID(), CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'FamilyInfosDashboardItemPlugin'), 'synchronize'),
(LAST_INSERT_ID(), 'Plugins/FamilyInfosDashboard/skin/js/synchronize/FamilyInfosDashboardItem.js', 'global_js');
