INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'miles'),
(100, 'sPhoneFormat', '9999 9999'),
(101, 'sPhoneFormatWithExt', '9999 9999'),
(111, 'sPhoneFormatCell', '9999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(2051, 'sCurrency', '£')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);