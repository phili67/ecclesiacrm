INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`)
VALUES ('PersonInfosDashboard', 'Plugin person Infos', 'Dashboard', NULL, '', '1', '1.1', 'pip_', 'inside_category_menu', 'left', 'bg-gradient-red text-white',32);

INSERT INTO `plugin_dependencies` ( `plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`) VALUES
(LAST_INSERT_ID(), CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardItemPlugin'), 'synchronize'),
(LAST_INSERT_ID(), 'Plugins/PersonInfosDashboard/skin/js/synchronize/PersonDashboardItemPlugin.js', 'global_js');
