delete from `autopayment_aut` where aut_FamID=0;

ALTER TABLE `autopayment_aut`
ADD CONSTRAINT fk_aut_FamID
    FOREIGN KEY (aut_FamID) REFERENCES family_fam(fam_ID)
    ON DELETE CASCADE;

ALTER TABLE `autopayment_aut` MODIFY aut_Fund tinyint(3) Default NULL;

ALTER TABLE `autopayment_aut`
ADD CONSTRAINT fk_aut_Fund
    FOREIGN KEY (aut_Fund) REFERENCES donationfund_fun(fun_ID)
    ON DELETE  SET NULL;


ALTER TABLE `autopayment_aut` MODIFY  aut_EditedBy mediumint(9) unsigned NULL;

UPDATE `autopayment_aut` SET aut_EditedBy = NULL WHERE aut_EditedBy = 0;

ALTER TABLE `autopayment_aut`
ADD CONSTRAINT fk_aut_EditedBy
    FOREIGN KEY (aut_EditedBy) REFERENCES person_per(per_ID)
    ON DELETE SET NULL;