INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'km'),
(100, 'sPhoneFormat', '999 999 999'),
(101, 'sPhoneFormatWithExt', '999 999 999'),
(111, 'sPhoneFormatCell', '999 999 999'),
(112, 'sTimeFormat', '%H:%M'),
(2051, 'sCurrency', '€')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'Nota Clásico Pastoral', '', 1),
(2, '¿Por qué vino a la iglesia?', '', 1),
(3, '¿Por qué sigue viniendo?', '', 1),
(4, '¿Tiene alguna sugerencia para nosotros?, '', 1),
(5, '¿Cómo se enteró de la iglesia?', '', 1),
(6, 'bautismo', 'Ausbildung', 0),
(7, 'boda', 'Ausbildung', 0),
(8, 'asistencia', 'Therapie', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);