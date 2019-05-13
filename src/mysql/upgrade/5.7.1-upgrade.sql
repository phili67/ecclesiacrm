ALTER TABLE `person_per` ADD `per_SendNewsLetter` enum('FALSE','TRUE') NOT NULL default 'FALSE';

ALTER TABLE `user_usr`
ADD CONSTRAINT fk_usr_per_ID
  FOREIGN KEY (usr_per_ID) REFERENCES person_per(per_ID);