DELETE FROM `query_qry` WHERE qry_ID=200;
INSERT INTO `query_qry` (`qry_ID`, `qry_SQL`, `qry_Name`, `qry_Description`, `qry_Count`) VALUES
(200, 'SELECT a.per_ID as AddToCart, CONCAT(''<a href=PersonView.php?PersonID='',a.per_ID,''>'',a.per_FirstName,'' '',a.per_LastName,''</a>'') AS Name FROM person_per AS a LEFT JOIN person_custom pc ON a.per_id = pc.per_ID WHERE pc.~custom~ LIKE ''%~value~%'' ORDER BY per_LastName', 'CustomSearch', 'Find people with a custom field value', 1);

CREATE TABLE IF NOT EXISTS `query_type` (
  `qry_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `qry_type_Category` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`qry_type_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `query_type` (`qry_type_id`, `qry_type_Category`) VALUES
(1, 'Person'),
(2, 'Family'),
(3, 'Events'),
(4, 'Pledge'),
(5, 'Users'),
(6, 'Volunteers'),
(7, 'Advanced Search'),
(8, 'Not assigned');


-- ALTER TABLE `query_qry` DROP COLUMN `qry_Type`;
ALTER TABLE `query_qry` ADD COLUMN `qry_Type_ID` int(11) DEFAULT 0;

UPDATE `query_qry` SET qry_Type_ID=2 WHERE qry_ID=1;
UPDATE `query_qry` SET qry_Type_ID=2 WHERE qry_ID=3;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=4;
UPDATE `query_qry` SET qry_Type_ID=8 WHERE qry_ID=6;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=7;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=9;
UPDATE `query_qry` SET qry_Type_ID=7 WHERE qry_ID=15;
UPDATE `query_qry` SET qry_Type_ID=3 WHERE qry_ID=18;
UPDATE `query_qry` SET qry_Type_ID=5 WHERE qry_ID=21;
UPDATE `query_qry` SET qry_Type_ID=3 WHERE qry_ID=22;
UPDATE `query_qry` SET qry_Type_ID=5 WHERE qry_ID=23;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=24;
UPDATE `query_qry` SET qry_Type_ID=6 WHERE qry_ID=25;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=26;
UPDATE `query_qry` SET qry_Type_ID=2 WHERE qry_ID=27;
UPDATE `query_qry` SET qry_Type_ID=4 WHERE qry_ID=28;
UPDATE `query_qry` SET qry_Type_ID=4 WHERE qry_ID=30;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=31;
UPDATE `query_qry` SET qry_Type_ID=2 WHERE qry_ID=32;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=33;
UPDATE `query_qry` SET qry_Type_ID=1 WHERE qry_ID=34;
UPDATE `query_qry` SET qry_Type_ID=6 WHERE qry_ID=100;
UPDATE `query_qry` SET qry_Type_ID=7 WHERE qry_ID=200;