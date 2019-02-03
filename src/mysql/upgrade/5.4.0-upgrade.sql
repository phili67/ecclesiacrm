UPDATE `user_usr`SET usr_showSince = '2018-01-01';
ALTER TABLE `user_usr` ADD `usr_showTo` date NOT NULL default '2019-01-01';

--
-- gdpr update : gdpr_infos list
--

UPDATE `gdpr_infos`
SET gdpr_info_About = 'Family'
WHERE gdpr_info_About='Person' and gdpr_info_Name='Role';


DELETE FROM `gdpr_infos` WHERE gdpr_info_About='Family' and gdpr_info_Name='Latitude';
DELETE FROM `gdpr_infos` WHERE gdpr_info_About='Family' and gdpr_info_Name='Longitude';
DELETE FROM `gdpr_infos` WHERE gdpr_info_About='Family' and gdpr_info_Name='Ok To Canvass';


--
-- gdpr update : query list
--

UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart,CONCAT(\'<a href=PersonView.php?PersonID=\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, CONCAT(per_BirthMonth,\'/\',per_BirthDay,\'/\',per_BirthYear) AS \'Birth Date\', DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(CONCAT(per_BirthYear,\'-\',per_BirthMonth,\'-\',per_BirthDay))),\'%Y\')+0 AS \'Age\', per_DateDeactivated as "GDPR" FROM person_per WHERE DATE_ADD(CONCAT(per_BirthYear,\'-\',per_BirthMonth,\'-\',per_BirthDay),INTERVAL ~min~ YEAR) <= CURDATE() AND DATE_ADD(CONCAT(per_BirthYear,\'-\',per_BirthMonth,\'-\',per_BirthDay),INTERVAL (~max~ + 1) YEAR) >= CURDATE() ORDER by Age , Name DESC' WHERE `query_qry`.`qry_ID` = 4;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE per_fmr_ID = ~role~ AND per_Gender = ~gender~' WHERE `query_qry`.`qry_ID` = 7;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, CONCAT(r2p_Value,\' \') AS Value, per_DateDeactivated as \'GDPR\' FROM person_per,record2property_r2p WHERE per_ID = r2p_record_ID AND r2p_pro_ID = ~PropertyID~ ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 9;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',per_ID,\'>\',COALESCE(`per_FirstName`,\'\'),\' \',COALESCE(`per_MiddleName`,\'\'),\' \',COALESCE(`per_LastName`,\'\'),\'</a>\') AS Name, fam_City as City, fam_State as State, fam_Zip as ZIP, per_HomePhone as HomePhone, per_Email as Email, per_WorkEmail as WorkEmail,per_DateDeactivated as \'GDPR\' FROM person_per RIGHT JOIN family_fam ON family_fam.fam_id = person_per.per_fam_id WHERE ~searchwhat~ LIKE \'%~searchstring~%\'' WHERE `query_qry`.`qry_ID` = 15;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, per_BirthDay as Day, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\'  FROM person_per WHERE per_cls_ID=~percls~ AND per_BirthMonth=~birthmonth~ ORDER BY per_BirthDay' WHERE `query_qry`.`qry_ID` = 18;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name,per_DateDeactivated as \'GDPR\' FROM person_per LEFT JOIN person2group2role_p2g2r ON per_id = p2g2r_per_ID WHERE p2g2r_grp_ID=~group~ ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 21;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, DAYOFMONTH(per_MembershipDate) as Day, per_MembershipDate AS DATE, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE per_cls_ID=1 AND MONTH(per_MembershipDate)=~membermonth~ ORDER BY per_MembershipDate' WHERE `query_qry`.`qry_ID` = 22;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT usr_per_ID as AddToCart, CONCAT(a.per_FirstName,\' \',a.per_LastName) AS Name, a.per_DateDeactivated as \'GDPR\' FROM user_usr LEFT JOIN person_per a ON per_ID=usr_per_ID ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 23;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE per_cls_id =1' WHERE `query_qry`.`qry_ID` = 24;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, per_DateDeactivated as \'GDPR\' FROM FROM person_per LEFT JOIN person2volunteeropp_p2vo ON per_id = p2vo_per_ID WHERE p2vo_vol_ID = ~volopp~ ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 25;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\' FROM person_per WHERE DATE_SUB(NOW(),INTERVAL ~friendmonths~ MONTH)<per_FriendDate ORDER BY per_MembershipDate' WHERE `query_qry`.`qry_ID` = 26;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, per_DateDeactivated as \'GDPR\'  FROM person_per inner join family_fam on per_fam_ID=fam_ID where per_fmr_ID<>3 AND fam_OkToCanvass="TRUE" ORDER BY fam_Zip' WHERE `query_qry`.`qry_ID` = 27;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,\' \',per_LastName) AS Name, fam_address1, fam_city, fam_state, fam_zip, per_DateDeactivated as \'GDPR\' FROM person_per join family_fam on per_fam_id=fam_id where per_fmr_id<>3 and per_fam_id in (select fam_id from family_fam inner join pledge_plg a on a.plg_famID=fam_ID and a.plg_FYID=~fyid1~ and a.plg_amount>0) and per_fam_id not in (select fam_id from family_fam inner join pledge_plg b on b.plg_famID=fam_ID and b.plg_FYID=~fyid2~ and b.plg_amount>0)' WHERE `query_qry`.`qry_ID` = 30;
UPDATE `query_qry` SET `qry_SQL` = 'select per_ID as AddToCart, per_FirstName, per_LastName, per_email, per_DateDeactivated as \'GDPR\'  FROM person_per, autopayment_aut where aut_famID=per_fam_ID and aut_CreditCard!="" and per_email!="" and (per_fmr_ID=1 or per_fmr_ID=2 or per_cls_ID=1)' WHERE `query_qry`.`qry_ID` = 31;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, per_LastName, per_FirstName, per_DateDeactivated as \'GDPR\'  FROM `person_per` where per_BirthYear<~the_year~ AND per_cls_ID IN (1,2) AND per_fam_ID<>3 AND `per_ID` NOT IN (SELECT p2g2r_per_ID FROM `person2group2role_p2g2r`) order by per_LastName ASC, per_FirstName ASC' WHERE `query_qry`.`qry_ID` = 33;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart,per_FirstName, per_LastName, grp_Name, per_DateDeactivated as \'GDPR\'  FROM `person2group2role_p2g2r`, `person_per`, group_grp WHERE per_cls_ID IN (1,2) AND per_fam_ID<>3 AND p2g2r_per_ID=per_ID and grp_ID=p2g2r_grp_ID order by per_FirstName ASC, per_LastName ASC, grp_Name ASC' WHERE `query_qry`.`qry_ID` = 34;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT a.per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\'  FROM person_per AS a LEFT JOIN person2volunteeropp_p2vo p2v1 ON (a.per_id = p2v1.p2vo_per_ID AND p2v1.p2vo_vol_ID = ~volopp1~) LEFT JOIN person2volunteeropp_p2vo p2v2 ON (a.per_id = p2v2.p2vo_per_ID AND p2v2.p2vo_vol_ID = ~volopp2~) WHERE p2v1.p2vo_per_ID=p2v2.p2vo_per_ID ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 100;
UPDATE `query_qry` SET `qry_SQL` = 'SELECT a.per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\'  FROM person_per AS a LEFT JOIN person_custom pc ON a.per_id = pc.per_ID WHERE pc.~custom~ LIKE \'%~value~%\' ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 200;