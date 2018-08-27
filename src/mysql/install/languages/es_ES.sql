INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'km'),
(100, 'sPhoneFormat', '999 999 999'),
(101, 'sPhoneFormatWithExt', '999 999 999'),
(111, 'sPhoneFormatCell', '999 999 999'),
(112, 'sTimeFormat', '%H:%M'),
(2050, 'bStateUnusefull', '0'),
(2051, 'sCurrency', '€')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);

INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Diezmo.', '.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Servicio en la iglesia'),
  (2, 'Escuela dominical')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Miembros', ''),
  (3, 1, 'Visitantes', ''),
  (4, 2, 'Total', ''),
  (5, 2, 'Miembros', ''),
  (6, 2, 'Visitantes', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_OptionName`) VALUES
  (1, 1, 1, 'Responsable cellule'),
  (1, 2, 2, 'Miembro'),
  (1, 3, 3, 'Participantes habituales.'),
  (1, 4, 4, 'Invitado'),
  (1, 5, 5, 'No participante'),
  (1, 6, 6, 'No participante (staff)'),
  (1, 7, 7, 'Fallecido'),
  (2, 1, 1, 'Representante del familiar'),
  (2, 2, 2, 'Conjunto(a)'),
  (2, 3, 3, 'Cónyuge'),
  (2, 4, 4, 'Otro miembro de la familia.'),
  (2, 5, 5, 'No es miembro de la familia'),
  (3, 1, 1, 'Ministerio'),
  (3, 2, 2, 'Equipo '),
  (3, 3, 3, 'Estudiar la Biblia.'),
  (3, 4, 4, 'Clases escuelas dominicales'),
  (4, 1, 1, 'True / False'),
  (4, 2, 2, 'Date'),
  (4, 3, 3, 'Text Field (50 char)'),
  (4, 4, 4, 'Text Field (100 char)'),
  (4, 5, 5, 'Text Field (Long)'),
  (4, 6, 6, 'Year'),
  (4, 7, 7, 'Season'),
  (4, 8, 8, 'Number'),
  (4, 9, 9, 'Person from Group'),
  (4, 10, 10, 'Money'),
  (4, 11, 11, 'Phone Number'),
  (4, 12, 12, 'Custom Drop-Down List'),
  (5, 1, 1, 'bAll'),
  (5, 2, 2, 'bAdmin'),
  (5, 3, 3, 'bAddRecords'),
  (5, 4, 4, 'bEditRecords'),
  (5, 5, 5, 'bDeleteRecords'),
  (5, 6, 6, 'bMenuOptions'),
  (5, 7, 7, 'bManageGroups'),
  (5, 8, 8, 'bFinance'),
  (5, 9, 9, 'bNotes'),
  (5, 10, 10, 'bCommunication'),
  (5, 11, 11, 'bCanvasser'),
  (10, 1, 1, 'Teacher'),
  (10, 2, 2, 'Student'),
  (11, 1, 1, 'Member'),
  (12, 1, 1, 'Teacher'),
  (12, 2, 2, 'Student')
ON DUPLICATE KEY UPDATE lst_OptionName=VALUES(lst_OptionName);

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
  (1, 'p', 'Persona', 'propiedades generales de personas'),
  (2, 'f', 'Familia', 'propiedades generales de Familia'),
  (3, 'g', 'Grupo', 'propiedades generales de Grupo'),
  (4, 'm', 'Menú', 'Para personalizar el menú escuelas dominicales')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`) VALUES
  (1, 'p', 1, 'Desactivado', 'a una discapacidad', 'cuál ha sido esta naturaleza ?'),
  (2, 'f', 2, ' Familia monoparentale', '', ''),
  (3, 'g', 3, 'Joven', 'est orienté jeune.', '')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Usuario  Administrador'),
(2, 'Usuario  Mínimo')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);


--
-- last update for the new CRM 4.4.0
--
INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'Nota Clásico Pastoral', '', 1),
(2, '¿Por qué vino a la iglesia?', '', 1),
(3, '¿Por qué sigue viniendo?', '', 1),
(4, '¿Tiene alguna sugerencia para nosotros?', '', 1),
(5, '¿Cómo se enteró de la iglesia?', '', 1),
(6, 'bautismo', 'Ausbildung', 0),
(7, 'boda', 'Ausbildung', 0),
(8, 'asistencia', 'Therapie', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);