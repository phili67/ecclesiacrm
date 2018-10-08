-- 
-- Alter the table to have the real primary key
-- 
ALTER TABLE `calendarinstances` ADD `cal_type` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '1 = normal, 2 = room, 3 = computer, 4 = video';
ALTER TABLE `user_usr` ADD `usr_CurrentPath` varchar(1500) NOT NULL default '/';