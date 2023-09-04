INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Helsinki'),
(100, 'sPhoneFormat', '99 999 999'),
(101, 'sPhoneFormatWithExt', '999 9 999 999'),
(102, 'sDateFormatLong', 'd.m.Y'),
(103, 'sDateFormatNoYear', 'd.m'),
(105, 'sDateTimeFormat', 'j.m.y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd.mm.yyyy'),
(110, 'sDatePickerFormat', 'd.m.Y'),
(111, 'sPhoneFormatCell', '99 999 999'),
(112, 'sTimeFormat', '%H:%M'),
(113, 'sPhoneCountryCallingCode', '0358'),
(1011, 'sTaxReport1', 'Tämä kirje on muistutus kaikista lahjoituksista.'),
(1012, 'sTaxReport2', 'Kiitos, että tuitte meitä tänä vuonna. Arvostamme suuresti omistautumistanne!'),
(1013, 'sTaxReport3', 'Jos sinulla on kysyttävää tai muutoksia raporttiin, ota yhteyttä kirkkoon edellä mainittuun numeroon työaikana klo 9-17 välisenä aikana.'),
(1015, 'sReminder1', 'Tämä kirje on yhteenveto kuluvalta varainhoitovuodelta lähetetyistä tiedoista.'),
(1019, 'sConfirm1', 'Tässä kirjeessä esitetään yhteenveto tietokantaamme tallennetuista tiedoista. Tarkista se huolellisesti, korjaa se ja palauta se kirkkoon.'),
(1020, 'sConfirm2', 'Kiitos, että autat meitä täydentämään näitä tietoja. Jos haluat tietoja tietokannasta.'),
(1021, 'sConfirm3', 'Sähköposti _____________________________________ salasana ________________'),
(1022, 'sConfirm4', '[  ] En halua enää olla yhteydessä kirkkoon (rasti tähän, jotta minut poistetaan rekisteristä).'),
(1026, 'sPledgeSummary1', 'Yhteenveto tämän tilikauden lupauksista ja maksuista'),
(1027, 'sPledgeSummary2', 'varten'),
(1028, 'sDirectoryDisclaimer1', 'Olemme pyrkineet tekemään näistä tiedoista mahdollisimman tarkkoja. Jos havaitset virheitä tai puutteita, ota meihin yhteyttä. Tätä hakemistoa käytetään henkilöille, jotka ovat'),
(1029, 'sDirectoryDisclaimer2', ', eikä sen sisältämiä tietoja käytetä kaupallisiin tarkoituksiin.'),
(1031, 'sZeroGivers', 'Tässä kirjeessä esitetään yhteenveto seuraavista maksuista'),
(1032, 'sZeroGivers2', 'Kiitos, että autat meitä vaikuttamaan. Arvostamme suuresti osallistumistanne!'),
(1033, 'sZeroGivers3', 'Jos sinulla on kysyttävää tai haluat tehdä korjauksia tähän raporttiin, ota yhteyttä kirkkomme yllä olevaan numeroon maanantaista perjantaihin kello 9:00-12:00 välisenä aikana.'),
(1048, 'sConfirmSincerely', 'Nähdään pian'),
(1049, 'sDear', 'Rakas'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Jos et halua vastaanottaa näitä sähköpostiviestejä osoitteesta'),
(2053, 'sUnsubscribeEnd', 'tulevaisuudessa, ota yhteyttä kirkon ylläpitäjiin'),
(1017, 'sReminderNoPledge', 'Lahjoitukset: Meillä ei ole tietoja lahjoituksistanne tältä tilikaudelta.'),
(1018, 'sReminderNoPayments', 'Maksut: Meillä ei ole tietoja maksuista, joita olette suorittaneet tältä tilikaudelta.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tithe', 'rahaa talousarvioon.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Jumalanpalvelus'),
  (2, 'Sunnuntaikoulu')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Yhteensä', ''),
  (2, 1, 'Jäsenet', ''),
  (3, 1, 'Vierailijat', ''),
  (4, 2, 'Yhteensä', ''),
  (5, 2, 'Jäsenet', ''),
  (6, 2, 'Vierailijat', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Vastaa seuraavista asioista'),
  (1, 2, 2, 'normal', 'Jäsen'),
  (1, 3, 3, 'normal', 'Säännöllinen osallistuja'),
  (1, 4, 4, 'normal', 'Vieras'),
  (1, 5, 5, 'normal', 'Muu kuin osallistuja'),
  (1, 6, 6, 'normal', 'Ei-osallistujat (Tiimi)'),
  (1, 7, 7, 'normal', 'Kuollut'),
  (2, 1, 1, 'normal', 'Perheen edustaja'),
  (2, 2, 2, 'normal', 'Puoliso'),
  (2, 3, 3, 'normal', 'Lapsi'),
  (2, 4, 4, 'normal', 'Muu perheenjäsen'),
  (2, 5, 5, 'normal', 'ei ole perheenjäsen'),
  (3, 1, 1, 'normal', 'palvelutyö'),
  (3, 2, 2, 'normal', 'Joukkue'),
  (3, 3, 3, 'normal', 'Raamatun tutkiminen'),
  (3, 4, 1, 'sunday_school', 'Ryhmä 1'),
  (3, 5, 2, 'sunday_school', 'Ryhmä 2'),
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
  (1, 'p', 'Henkilö', 'Henkilöiden yleiset ominaisuudet'),
  (2, 'f', 'Perhe', 'Perheiden yleiset ominaisuudet'),
  (3, 'g', 'Ryhmä', 'Ryhmän yleiset ominaisuudet'),
  (4, 'm', 'Valikko', 'Henkilökohtaisen pyhäkoulumenun laatiminen.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Vammaisuus.', 'Mikä on sen luonne?', ''),
  (2, 'f', 2, 'Yksinhuoltaja', 'on yksinhuoltajien kotitalous.', '', ''),
  (3, 'g', 3, 'Nuori', 'on motivoitunut työskentelemään nuorisotyössä.', '', '')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Käyttäjän ylläpitäjä'),
(2, 'Vähimmäiskäyttäjä'),
(3, 'UMax käyttäjä mutta ei Admin'),
(4, 'Käyttäjä Max mutta ei tietosuojapäällikkö eikä pastoraalihuolto.'),
(5, 'DPO-käyttäjä')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);
--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klassinen pastoraalinen huomautus', '', 1, ''),
(2, 'Miksi tulit kirkkoon', '', 1, ''),
(3, 'Miksi te tulette tänne?', '', 1, ''),
(4, 'Onko teillä toivomus meille?', '', 1, ''),
(5, 'Miten kuulit kirkosta?', '', 1, ''),
(6, 'Kaste', 'Koulutus', 0, ''),
(7, 'Häät', 'Koulutus', 0, ''),
(8, 'Suhteiden auttaminen', 'Hoito ja seuranta', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
