INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Warsaw'),
(100, 'sPhoneFormat', '99 999 99 99'),
(101, 'sPhoneFormatWithExt', '99 99 999 99 99'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-mm-dd'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '99 999 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Niniejszy list jest przypomnieniem o wszystkich darowiznach na rzecz'),
(1012, 'sTaxReport2', 'Dziękujemy za wsparcie nas w tym roku. Bardzo doceniamy Twoje zaangażowanie!'),
(1013, 'sTaxReport3', 'W razie jakichkolwiek pytań lub zmian w raporcie, prosimy o kontakt z kościołem pod powyższym numerem w godzinach pracy, pomiędzy 9 a 17.'),
(1015, 'sReminder1', 'Niniejszy list stanowi podsumowanie informacji przesłanych za bieżący rok podatkowy'),
(1019, 'sConfirm1', 'Ten list podsumowuje informacje, które są zapisane w naszej bazie danych. Prosimy o dokładne przeczytanie, poprawienie i odesłanie do naszego kościoła.'),
(1020, 'sConfirm2', 'Dziękujemy za pomoc w uzupełnieniu tych informacji. Jeśli chcesz uzyskać informacje na temat bazy danych.'),
(1021, 'sConfirm3', 'E-mail _____________________________________ hasło ________________'),
(1022, 'sConfirm4', '[  ] Nie chcę być dłużej związany z kościołem (zaznacz tutaj, aby usunąć mnie z rejestru).'),
(1026, 'sPledgeSummary1', 'Podsumowanie zobowiązań i płatności za ten rok fiskalny'),
(1027, 'sPledgeSummary2', 'dla'),
(1028, 'sDirectoryDisclaimer1', 'Dołożyliśmy wszelkich starań, aby informacje te były jak najdokładniejsze. Jeśli znajdziesz jakieś błędy lub pominięcia, prosimy o kontakt. Ten katalog jest używany dla osób z'),
(1029, 'sDirectoryDisclaimer2', ', a zawarte w nim informacje nie będą wykorzystywane do celów komercyjnych.'),
(1031, 'sZeroGivers', 'Niniejsze pismo zawiera podsumowanie płatności'),
(1032, 'sZeroGivers2', 'Dziękujemy, że pomagasz nam zmieniać świat na lepsze. Bardzo doceniamy Państwa udział!'),
(1033, 'sZeroGivers3', 'Jeśli masz jakieś pytania lub potrzebujesz wprowadzić poprawki do tego raportu, prosimy o kontakt z naszym kościołem pod powyższym numerem telefonu w godzinach od 9:00 do 12:00 od poniedziałku do piątku.'),
(1048, 'sConfirmSincerely', 'Do zobaczenia wkrótce'),
(1049, 'sDear', 'Droga'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'zł'),
(2052, 'sUnsubscribeStart', 'Jeśli nie chcesz otrzymywać tych e-maili od'),
(2053, 'sUnsubscribeEnd', 'Jeśli nie chcesz otrzymywać tych e-maili od'),
(1017, 'sReminderNoPledge', 'Darowizny: W tym roku podatkowym nie odnotowaliśmy żadnych darowizn od Państwa.'),
(1018, 'sReminderNoPayments', 'Płatności: Nie mamy żadnych zapisów o jakichkolwiek płatnościach z Państwa strony w tym roku podatkowym.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tithe', 'pieniądze do budżetu.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Nabożeństwo'),
  (2, 'Szkoła Niedzielna')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Ogółem', ''),
  (2, 1, 'Członkowie', ''),
  (3, 1, 'Odwiedzający', ''),
  (4, 2, 'Ogółem', ''),
  (5, 2, 'Członkowie', ''),
  (6, 2, 'Odwiedzający', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Odpowiedzialny za'),
  (1, 2, 2, 'normal', 'Członek'),
  (1, 3, 3, 'normal', 'Stały uczestnik'),
  (1, 4, 4, 'normal', 'Gość'),
  (1, 5, 5, 'normal', 'Osoba nie będąca uczestnikiem'),
  (1, 6, 6, 'normal', 'Osoby nieuczestniczące (personel)'),
  (1, 7, 7, 'normal', 'Zmarły'),
  (2, 1, 1, 'normal', 'Przedstawiciel rodziny'),
  (2, 2, 2, 'normal', 'Współmałżonek'),
  (2, 3, 3, 'normal', 'Dziecko'),
  (2, 4, 4, 'normal', 'Inny członek rodziny'),
  (2, 5, 5, 'normal', 'Nie jest członkiem rodziny'),
  (3, 1, 1, 'normal', 'Duszpasterstwo kościelne'),
  (3, 2, 2, 'normal', 'Zespół'),
  (3, 3, 3, 'normal', 'Studium Biblii'),
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
  (1, 'p', 'Osoba', 'Ogólne właściwości osób'),
  (2, 'f', 'Rodzina', 'Ogólne właściwości rodzin'),
  (3, 'g', 'Grupa', 'Ogólne właściwości grupy'),
  (4, 'm', 'Menu', 'Aby spersonalizować menu szkółki niedzielnej.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Poza', 'Niepełnosprawność.', 'Jaka jest jego natura?',''),
  (2, 'f', 2, 'Samotny rodzic', 'jest gospodarstwem domowym prowadzonym przez jednego rodzica.', '',''),
  (3, 'g', 3, 'Młody', 'jest zmotywowany do pracy z młodzieżą.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Użytkownik Administrator'),
(2, 'Minimalny użytkownik'),
(3, 'Użytkownik Max, ale nie Admin'),
(4, 'Użytkownik Max, ale nie DPO i nie Duszpasterstwo'),
(5, 'Użytkownik DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klasyczna nuta pastoralna', '', 1, ''),
(2, 'Dlaczego przyszedłeś do kościoła?', '', 1, ''),
(3, 'Dlaczego wciąż przychodzisz?', '', 1, ''),
(4, 'Czy masz do nas jakąś prośbę?', '', 1, ''),
(5, 'Jak dowiedziałeś się o kościele?', '', 1, ''),
(6, 'Chrzest', 'Szkolenie', 0, ''),
(7, 'Ślub', 'Szkolenie', 0, ''),
(8, 'Pomaganie relacjom', 'Leczenie i dalsze postępowanie', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
