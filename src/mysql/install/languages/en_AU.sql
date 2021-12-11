INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(100, 'sPhoneFormat', '9999 999 999'),
(101, 'sPhoneFormatWithExt', '9999 999 999'),
(111, 'sPhoneFormatCell', '9999 999 999'),
(112, 'sTimeFormat', '%H:%M'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'A$')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);
