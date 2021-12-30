ALTER TABLE `family_custom_master` MODIFY `fam_custom_comment` text NULL default NULL COMMENT 'comment for GDPR';
ALTER TABLE `person_custom_master` MODIFY `custom_comment` text NULL default NULL COMMENT 'comment for GDPR';

-- mise à jour supplémentaire
-- SET sql_mode='';
-- SET GLOBAL sql_mode='';

-- SET sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
-- SET GLOBAL sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

ALTER TABLE `user_usr` ADD `usr_TwoFaSecret` VARCHAR(255) NULL AFTER `usr_EDrive`;
ALTER TABLE `user_usr` ADD `usr_TwoFaSecretConfirm` BOOLEAN NOT NULL default 0;
ALTER TABLE `user_usr` ADD `usr_TwoFaRescuePasswords` VARCHAR(255) NULL;
ALTER TABLE `user_usr` ADD `usr_TwoFaRescueDateTime` datetime NOT NULL default '2000-01-01 00:00:00' COMMENT 'Only 60 seconds to validate the rescue password';

-- SHOW VARIABLES LIKE 'sql_mode';

-- ALTER TABLE `events_event` MODIFY `event_start` datetime NOT NULL DEFAULT '2000-01-01 00:00:00';
-- ALTER TABLE `events_event` MODIFY `event_end` datetime NOT NULL DEFAULT '2000-01-01 00:00:00';

-- ALTER TABLE `event_types` MODIFY `type_defrecurDOY` date NOT NULL DEFAULT '2000-01-01';

-- ALTER TABLE `istlookup_lu` MODIFY `lu_LookupDateTime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00';

-- ALTER TABLE `note_nte` MODIFY `nte_DateEntered` datetime NOT NULL DEFAULT '2000-01-01 00:00:00';

-- ALTER TABLE `person_per` MODIFY `per_DateEntered` datetime NOT NULL DEFAULT '2000-01-01 00:00:00';

-- ALTER TABLE `pledge_plg` MODIFY `plg_DateLastEdited` date NOT NULL DEFAULT '2000-01-01';

-- ALTER TABLE `user_usr` MODIFY `usr_LastLogin` datetime NOT NULL default '2000-01-01 00:00:00';
-- ALTER TABLE `user_usr` MODIFY `usr_showSince` date NOT NULL default '2018-01-01';
-- ALTER TABLE `user_usr` MODIFY `usr_showTo` date NOT NULL default '2019-01-01';


-- SET sql_mode='IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
-- SET GLOBAL sql_mode='IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';


ALTER TABLE `events_event` ADD `event_creator_user_id` mediumint(9) DEFAULT NULL COMMENT 'For resource slot : the owner is the creator';

ALTER TABLE `user_usr` ADD `usr_ManageCalendarResources` tinyint(1) unsigned NOT NULL default '0';


