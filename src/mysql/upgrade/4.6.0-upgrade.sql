-- 
-- Alter the table to have the real primary key
-- 
ALTER TABLE `family_custom_master` ADD `family_custom_id` mediumint(9) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT;

-- 
-- Alter the table to have the real primary key
-- 
ALTER TABLE `person_custom_master` DROP PRIMARY KEY;
ALTER TABLE `person_custom_master` ADD `custom_id` mediumint(9) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT;
