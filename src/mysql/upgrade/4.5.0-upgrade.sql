ALTER TABLE `user_usr` ADD `usr_profile_id` mediumint(11) unsigned NULL;

ALTER TABLE `user_usr`
ADD CONSTRAINT fk_usr_profile_id
  FOREIGN KEY (usr_profile_id) REFERENCES userprofile_usrprf(usrprf_id)
  ON DELETE SET NULL;


ALTER TABLE `user_usr` ADD `usr_webDavKey` VARCHAR(255) default NULL;

ALTER TABLE user_usr ADD UNIQUE (usr_webDavKey);

