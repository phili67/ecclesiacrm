INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Bucharest'),
(100, 'sPhoneFormat', '99 99 99 99 99'),
(101, 'sPhoneFormatWithExt', '99 99 99 99 99'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '99 99 99 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Această scrisoare este o reamintire a tuturor donațiilor pentru'),
(1012, 'sTaxReport2', 'Vă mulțumim pentru că ne-ați sprijinit în acest an. Apreciem foarte mult dedicarea dumneavoastră!'),
(1013, 'sTaxReport3', 'În cazul în care aveți întrebări sau modificări ale raportului, vă rugăm să contactați biserica dumneavoastră la numărul de telefon de mai sus, în timpul programului de lucru, între orele 9.00 și 17.00.'),
(1015, 'sReminder1', 'Această scrisoare este un rezumat al informațiilor trimise pentru anul fiscal în curs.'),
(1019, 'sConfirm1', 'Această scrisoare rezumă informațiile înregistrate în baza noastră de date. Vă rugăm să o citiți cu atenție, să o corectați și să o returnați la biserica noastră.'),
(1020, 'sConfirm2', 'Vă mulțumim că ne ajutați să completăm aceste informații. Dacă doriți informații despre baza de date.'),
(1021, 'sConfirm3', 'Email _____________________________________ parola ________________'),
(1022, 'sConfirm4', '[  ] Nu mai doresc să fiu asociat cu biserica (bifați aici pentru a fi șters din evidențele dumneavoastră).'),
(1026, 'sPledgeSummary1', 'Rezumat al promisiunilor și plăților pentru acest an fiscal'),
(1027, 'sPledgeSummary2', 'pentru'),
(1028, 'sDirectoryDisclaimer1', 'Ne-am străduit ca aceste informații să fie cât mai exacte posibil. Dacă găsiți erori sau omisiuni, vă rugăm să ne contactați. Acest director este folosit pentru persoanele din'),
(1029, 'sDirectoryDisclaimer2', ', iar informațiile conținute nu vor fi utilizate în scopuri comerciale.'),
(1031, 'sZeroGivers', 'Această scrisoare sintetizează plățile de'),
(1032, 'sZeroGivers2', 'Vă mulțumim că ne ajutați să facem diferența. Apreciem foarte mult participarea dumneavoastră!'),
(1033, 'sZeroGivers3', 'Dacă aveți întrebări sau dacă aveți nevoie să faceți corecturi la acest raport, vă rugăm să contactați biserica noastră la numărul de telefon de mai sus, în intervalul orar 9:00-12:00, de luni până vineri.'),
(1048, 'sConfirmSincerely', 'A très bientôt'),
(1049, 'sDear', 'Dragă'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'lei'),
(2052, 'sUnsubscribeStart', 'Dacă nu doriți să primiți aceste e-mailuri de la'),
(2053, 'sUnsubscribeEnd', 'în viitor, contactați administratorii bisericii'),
(1017, 'sReminderNoPledge', 'Donații: Nu avem nicio înregistrare a vreunei donații din partea dumneavoastră pentru acest an fiscal.'),
(1018, 'sReminderNoPayments', 'Plăți: Nu avem nicio înregistrare a vreunei plăți din partea dumneavoastră pentru acest an fiscal.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tithe', 'bani pentru buget.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Slujbă religioasă'),
  (2, 'Școala de duminică')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Membri', ''),
  (3, 1, 'Vizitatori', ''),
  (4, 2, 'Total', ''),
  (5, 2, 'Membri', ''),
  (6, 2, 'Vizitatori', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Responsabil pentru'),
  (1, 2, 2, 'normal', 'Membru'),
  (1, 3, 3, 'normal', 'Participant obișnuit'),
  (1, 4, 4, 'normal', 'Invitat'),
  (1, 5, 5, 'normal', 'Neparticipant'),
  (1, 6, 6, 'normal', 'Neparticipanți (personal)'),
  (1, 7, 7, 'normal', 'Decedat'),
  (2, 1, 1, 'normal', 'Reprezentantul familiei'),
  (2, 2, 2, 'normal', 'Soț/soție'),
  (2, 3, 3, 'normal', 'Copilul'),
  (2, 4, 4, 'normal', 'Alt membru al familiei'),
  (2, 5, 5, 'normal', 'Nu este un membru al familiei'),
  (3, 1, 1, 'normal', 'Slujirea bisericii'),
  (3, 2, 2, 'normal', 'Echipa'),
  (3, 3, 3, 'normal', 'Studiu biblic'),
  (3, 4, 1, 'sunday_school', 'Grupa 1'),
  (3, 5, 2, 'sunday_school', 'Grupa 2'),
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
  (1, 'p', 'Persoană', 'Proprietăți generale ale persoanelor'),
  (2, 'f', 'Familie', 'Proprietăți generale ale familiilor'),
  (3, 'g', 'Grup', 'Proprietăți generale ale grupului'),
  (4, 'm', 'Meniu', 'Pentru a personaliza meniul școlii duminicale.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Un handicap.', 'Care este natura sa?',''),
  (2, 'f', 2, 'Părinte singur', 'este o gospodărie monoparentală.', '',''),
  (3, 'g', 3, 'Tânăr', 'este motivat să lucreze în domeniul tineretului.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Utilizator Administrator'),
(2, 'Utilizator minim'),
(3, 'Utilizator Max, dar nu Admin'),
(4, 'Utilizator Max, dar nu DPO și nu Pastoral Care'),
(5, 'Utilizator DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Notă pastorală clasică', '', 1, ''),
(2, 'De ce ai venit la biserică?', '', 1, ''),
(3, 'De ce continui să vii?', '', 1, ''),
(4, 'Aveți o cerere pentru noi?', '', 1, ''),
(5, 'Cum ați auzit de biserică?', '', 1, ''),
(6, 'Botez', 'Formare', 0, ''),
(7, 'Nuntă', 'Formare', 0, ''),
(8, 'Relații de ajutorare', 'Relații de ajutorare', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
