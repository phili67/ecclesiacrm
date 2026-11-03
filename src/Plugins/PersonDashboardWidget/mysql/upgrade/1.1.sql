INSERT INTO `plugin_dependencies` (`plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`)
SELECT p.`plgn_ID`, CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardWidgetDashboardItemPlugin'), 'synchronize'
FROM `plugin` AS p
WHERE p.`plgn_Name` = 'PersonDashboardWidget'
  AND NOT EXISTS (
    SELECT 1 FROM `plugin_dependencies` AS d
    WHERE d.`plgn_dep_plugin_ID` = p.`plgn_ID`
      AND d.`plgn_dep_url` = CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardWidgetDashboardItemPlugin')
      AND d.`plgn_dep_extension` = 'synchronize'
  );

INSERT INTO `plugin_dependencies` (`plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`)
SELECT p.`plgn_ID`, 'Plugins/PersonDashboardWidget/skin/js/synchronize/PersonDashboardWidgetDashboardItem.js', 'global_js'
FROM `plugin` AS p
WHERE p.`plgn_Name` = 'PersonDashboardWidget'
  AND NOT EXISTS (
    SELECT 1 FROM `plugin_dependencies` AS d
    WHERE d.`plgn_dep_plugin_ID` = p.`plgn_ID`
      AND d.`plgn_dep_url` = 'Plugins/PersonDashboardWidget/skin/js/synchronize/PersonDashboardWidgetDashboardItem.js'
      AND d.`plgn_dep_extension` = 'global_js'
  );
