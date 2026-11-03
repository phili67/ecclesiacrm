INSERT INTO `plugin_dependencies` (`plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`)
SELECT p.`plgn_ID`, CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardItemPlugin'), 'synchronize'
FROM `plugin` AS p
WHERE p.`plgn_Name` = 'PersonInfosDashboard'
  AND NOT EXISTS (
    SELECT 1 FROM `plugin_dependencies` AS d
    WHERE d.`plgn_dep_plugin_ID` = p.`plgn_ID`
      AND d.`plgn_dep_url` = CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardItemPlugin')
      AND d.`plgn_dep_extension` = 'synchronize'
  );

INSERT INTO `plugin_dependencies` (`plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`)
SELECT p.`plgn_ID`, 'Plugins/PersonInfosDashboard/skin/js/synchronize/PersonDashboardItemPlugin.js', 'global_js'
FROM `plugin` AS p
WHERE p.`plgn_Name` = 'PersonInfosDashboard'
  AND NOT EXISTS (
    SELECT 1 FROM `plugin_dependencies` AS d
    WHERE d.`plgn_dep_plugin_ID` = p.`plgn_ID`
      AND d.`plgn_dep_url` = 'Plugins/PersonInfosDashboard/skin/js/synchronize/PersonDashboardItemPlugin.js'
      AND d.`plgn_dep_extension` = 'global_js'
  );

DELETE d FROM `plugin_dependencies` AS d
INNER JOIN `plugin` AS p ON p.`plgn_ID` = d.`plgn_dep_plugin_ID`
WHERE p.`plgn_Name` = 'PersonDashboardWidget'
  AND (
    (d.`plgn_dep_url` = CONCAT('Plugin', CHAR(92), 'Synchronize', CHAR(92), 'PersonDashboardItemPlugin')
      AND d.`plgn_dep_extension` = 'synchronize')
    OR
    (d.`plgn_dep_url` = 'Plugins/PersonInfosDashboard/skin/js/synchronize/PersonDashboardItemPlugin.js'
      AND d.`plgn_dep_extension` = 'global_js')
  );
