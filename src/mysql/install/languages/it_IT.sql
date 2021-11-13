INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Rome'),
(100, 'sPhoneFormat', '999 9999999'),
(101, 'sPhoneFormatWithExt', '999 999 9999999'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '999 9999999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Questa lettera è un promemoria di tutte le donazioni per'),
(1012, 'sTaxReport2', 'Grazie per averci sostenuto quest''anno. Apprezziamo molto la vostra dedizione!'),
(1013, 'sTaxReport3', 'In caso di domande o modifiche al rapporto, si prega di contattare la chiesa al numero sopra indicato durante l''orario di lavoro, tra le 9 e le 17.'),
(1015, 'sReminder1', 'Questa lettera è un riassunto delle informazioni inviate per l''anno fiscale in corso'),
(1019, 'sConfirm1', 'Questa lettera riassume le informazioni che sono registrate nel nostro database. Per favore, leggilo attentamente, correggilo e restituiscilo alla nostra chiesa.'),
(1020, 'sConfirm2', 'Grazie per averci aiutato a completare queste informazioni. Se volete informazioni sul database.'),
(1021, 'sConfirm3', 'Email _____________________________________ password ________________'),
(1022, 'sConfirm4', '[  ] Non voglio più essere associato alla chiesa (spuntare qui per essere cancellato dai vostri archivi).'),
(1026, 'sPledgeSummary1', 'Riassunto delle promesse e dei pagamenti per questo anno fiscale'),
(1027, 'sPledgeSummary2', 'per il'),
(1028, 'sDirectoryDisclaimer1', 'Abbiamo lavorato per rendere queste informazioni il più accurate possibile. Se trovate degli errori o delle omissioni, contattateci. Questo elenco è utilizzato per le persone di'),
(1029, 'sDirectoryDisclaimer2', ', e le informazioni contenute non saranno utilizzate per scopi commerciali.'),
(1031, 'sZeroGivers', 'Questa lettera riassume i pagamenti di'),
(1032, 'sZeroGivers2', 'Grazie per aiutarci a fare la differenza. Apprezziamo molto la vostra partecipazione!'),
(1033, 'sZeroGivers3', 'Se avete domande o bisogno di fare correzioni a questo rapporto, si prega di contattare la nostra chiesa al numero di cui sopra durante le ore 9:00-12:00 dal lunedì al venerdì.'),
(1048, 'sConfirmSincerely', 'A presto'),
(1049, 'sDear', 'Caro'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Se non vuoi ricevere queste e-mail da'),
(2053, 'sUnsubscribeEnd', 'in futuro, contattare gli amministratori della chiesa'),
(1017, 'sReminderNoPledge', 'Donazioni: non abbiamo nessuna registrazione di donazioni da parte vostra per questo anno fiscale.'),
(1018, 'sReminderNoPayments', 'Pagamenti: Non abbiamo nessuna registrazione di pagamenti da parte vostra per questo anno fiscale.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Decima', 'soldi per il bilancio.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Servizio in chiesa'),
  (2, 'Scuola domenicale')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Totale', ''),
  (2, 1, 'Membri', ''),
  (3, 1, 'Visitatori', ''),
  (4, 2, 'Totale', ''),
  (5, 2, 'Membri', ''),
  (6, 2, 'Visitatori', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Responsabile per'),
  (1, 2, 2, 'normal', 'Membro'),
  (1, 3, 3, 'normal', 'Participant régulier'),
  (1, 4, 4, 'normal', 'Ospite'),
  (1, 5, 5, 'normal', 'Non partecipante'),
  (1, 6, 6, 'normal', 'Non partecipanti (Staff)'),
  (1, 7, 7, 'normal', 'Deceduto'),
  (2, 1, 1, 'normal', 'Représentant famille'),
  (2, 2, 2, 'normal', 'Coniuge'),
  (2, 3, 3, 'normal', 'Bambino'),
  (2, 4, 4, 'normal', 'Altro membro della famiglia'),
  (2, 5, 5, 'normal', 'Non è un membro della famiglia'),
  (3, 1, 1, 'normal', 'Ministero'),
  (3, 2, 2, 'normal', 'Squadra'),
  (3, 3, 3, 'normal', 'Studio della Bibbia'),
  (3, 4, 1, 'sunday_school', 'Gruppo 1'),
  (3, 5, 2, 'sunday_school', 'Gruppo 2'),
  (4, 1, 1, 'normal', 'True / False'),
  (4, 2, 2, 'normal', 'Date'),
  (4, 3, 3, 'normal', 'Text Field (50 char)'),
  (4, 4, 4, 'normal', 'Text Field (100 char)'),
  (4, 5, 5, 'normal', 'Text Field (Long)'),
  (4, 6, 6, 'normal', 'Year'),
  (4, 7, 7, 'normal', 'Season'),
  (4, 8, 8, 'normal', 'Number'),
  (4, 9, 9, 'normal', 'Person from Group'),
  (4, 10, 10, 'normal', 'Money'),
  (4, 11, 11, 'normal', 'Phone Number'),
  (4, 12, 12, 'normal', 'Custom Drop-Down List'),
  (5, 1, 1, 'normal', 'bAll'),
  (5, 2, 2, 'normal', 'bAdmin'),
  (5, 3, 3, 'normal', 'bAddRecords'),
  (5, 4, 4, 'normal', 'bEditRecords'),
  (5, 5, 5, 'normal', 'bDeleteRecords'),
  (5, 6, 6, 'normal', 'bMenuOptions'),
  (5, 7, 7, 'normal', 'bManageGroups'),
  (5, 8, 8, 'normal', 'bFinance'),
  (5, 9, 9, 'normal', 'bNotes'),
  (5, 10, 10, 'normal', 'bCommunication'),
  (5, 11, 11, 'normal', 'bCanvasser'),
  (10, 1, 1, 'normal', 'Teacher'),
  (10, 2, 2, 'normal', 'Student'),
  (11, 1, 1, 'normal', 'Member'),
  (12, 1, 1, 'normal', 'Teacher'),
  (12, 2, 2, 'normal', 'Student')
ON DUPLICATE KEY UPDATE lst_OptionName=VALUES(lst_OptionName);

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
  (1, 'p', 'Persona', 'Proprietà generali delle persone'),
  (2, 'f', 'Famiglia', 'Proprietà generali delle famiglie'),
  (3, 'g', 'Groupe', 'Proprietà generali del gruppo'),
  (4, 'm', 'Menu', 'Per personalizzare il menu della scuola domenicale.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Una disabilità.', 'Qual è la sua natura?',''),
  (2, 'f', 2, 'Genitore solo', 'è una famiglia monoparentale.', '',''),
  (3, 'g', 3, 'Giovane', 'è motivato a lavorare nel settore giovanile.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Amministratore utente'),
(2, 'Utente minimo'),
(3, 'Utente Max ma non Admin'),
(4, 'Utente Max ma non DPO e non Pastoral Care'),
(5, 'Utente DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Classica nota pastorale', '', 1, ''),
(2, 'PPerché sei venuto in chiesa?', '', 1, ''),
(3, 'Perché continui a venire?', '', 1, ''),
(4, 'Ha una richiesta da farci?', '', 1, ''),
(5, 'Come hai saputo della chiesa?', '', 1, ''),
(6, 'Battesimo', 'Formazione', 0, ''),
(7, 'Matrimonio', 'Formazione', 0, ''),
(8, 'Relazioni d''aiuto', 'Terapia e follow-up', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
