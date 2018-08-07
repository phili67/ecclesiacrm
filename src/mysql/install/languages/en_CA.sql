INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'miles'),
(100, 'sPhoneFormat', '999 999 9999'),
(101, 'sPhoneFormatWithExt', '999 999 9999'),
(111, 'sPhoneFormatCell', '999 999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '$')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);