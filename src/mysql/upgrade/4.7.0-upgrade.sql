--
-- We add a primary key to egive_egv
--
ALTER TABLE `egive_egv` ADD `egv_ID` mediumint(9) unsigned NOT NULL PRIMARY KEY   auto_increment;

--
-- We add the constraint
--
ALTER TABLE `egive_egv` MODIFY egv_famID mediumint(9) unsigned NOT NULL;

ALTER TABLE `egive_egv`
ADD   CONSTRAINT fk_egv_famID
    FOREIGN KEY (egv_famID) REFERENCES family_fam(fam_ID)
    ON DELETE CASCADE;

