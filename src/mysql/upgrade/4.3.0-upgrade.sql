--
-- A person classification can have an icon
-- 
CREATE TABLE list_icon (
    `lst_ic_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `lst_ic_lst_ID` mediumint(9) unsigned NOT NULL,
    `lst_ic_lst_Option_ID` mediumint(9) unsigned NOT NULL,
    `lst_ic_lst_url` varchar(50) default NULL,
    `lst_ic_only_person_View` BOOLEAN NOT NULL default 0,
    PRIMARY KEY(lst_ic_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;