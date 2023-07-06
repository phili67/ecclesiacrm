DELETE FROM `plugin` WHERE `plgn_Name`='MeetingJitsi';
DELETE FROM `plugin_menu_bar` WHERE `plgn_mb_plugin_name`='MeetingJitsi';

DROP TABLE `plugin_pref_jitsimeeting_pjmp`;
DROP TABLE `personlastjitsimeeting_plm`;
DROP TABLE `personjitsimeeting_pm`;
