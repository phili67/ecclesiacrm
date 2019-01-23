UPDATE `gdpr_infos`
SET gdpr_info_About = 'Family'
WHERE gdpr_info_About='Person' and gdpr_info_Name='Role';


DELETE FROM `gdpr_infos` WHERE gdpr_info_About='Family' and gdpr_info_Name='Latitude';
DELETE FROM `gdpr_infos` WHERE gdpr_info_About='Family' and gdpr_info_Name='Longitude';
DELETE FROM `gdpr_infos` WHERE gdpr_info_About='Family' and gdpr_info_Name='Ok To Canvass';