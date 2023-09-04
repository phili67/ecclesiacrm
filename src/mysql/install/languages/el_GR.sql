INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Athens'),
(100, 'sPhoneFormat', '99 9 999 9999'),
(101, 'sPhoneFormatWithExt', '99 9 999 9999'),
(102, 'sDateFormatLong', 'm/d/Y'),
(103, 'sDateFormatNoYear', 'm/d'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'mm/jj/yyyy'),
(110, 'sDatePickerFormat', 'm/d/Y'),
(111, 'sPhoneFormatCell', '99 9 999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(113, 'sPhoneCountryCallingCode', '0030'),
(1011, 'sTaxReport1', 'Αυτή η επιστολή είναι μια υπενθύμιση όλων των δωρεών για'),
(1012, 'sTaxReport2', 'Σας ευχαριστούμε που μας υποστηρίξατε φέτος. Εκτιμούμε ιδιαίτερα την αφοσίωσή σας!'),
(1013, 'sTaxReport3', 'Εάν έχετε οποιεσδήποτε ερωτήσεις ή αλλαγές στην έκθεση, παρακαλείστε να επικοινωνήσετε με την εκκλησία σας στον παραπάνω αριθμό κατά τις εργάσιμες ώρες, μεταξύ 9 π.μ. και 5 μ.μ.'),
(1015, 'sReminder1', 'Η παρούσα επιστολή αποτελεί περίληψη των πληροφοριών που εστάλησαν για το τρέχον οικονομικό έτος'),
(1019, 'sConfirm1', 'Αυτή η επιστολή συνοψίζει τις πληροφορίες που έχουν καταγραφεί στη βάση δεδομένων μας. Παρακαλούμε διαβάστε το προσεκτικά, διορθώστε το και επιστρέψτε το στην εκκλησία μας.'),
(1020, 'sConfirm2', 'Σας ευχαριστούμε που μας βοηθήσατε να συμπληρώσουμε αυτές τις πληροφορίες. Εάν θέλετε πληροφορίες σχετικά με τη βάση δεδομένων.'),
(1021, 'sConfirm3', 'Ηλεκτρονικό ταχυδρομείο _____________________________________ κωδικός πρόσβασης ________________'),
(1022, 'sConfirm4', '[  ] Δεν θέλω πλέον να σχετίζομαι με την εκκλησία (τσεκάρετε εδώ για να διαγραφείτε από τα αρχεία σας).'),
(1026, 'sPledgeSummary1', 'Σύνοψη των δεσμεύσεων και πληρωμών για το τρέχον οικονομικό έτος'),
(1027, 'sPledgeSummary2', 'για το'),
(1028, 'sDirectoryDisclaimer1', 'Προσπαθήσαμε να κάνουμε αυτές τις πληροφορίες όσο το δυνατόν πιο ακριβείς. Εάν διαπιστώσετε σφάλματα ή παραλείψεις, παρακαλούμε επικοινωνήστε μαζί μας. Αυτός ο κατάλογος χρησιμοποιείται για άτομα από'),
(1029, 'sDirectoryDisclaimer2', ', και οι πληροφορίες που περιέχονται δεν θα χρησιμοποιηθούν για εμπορικούς σκοπούς.'),
(1031, 'sZeroGivers', 'Η παρούσα επιστολή συνοψίζει τις πληρωμές'),
(1032, 'sZeroGivers2', 'Σας ευχαριστούμε που μας βοηθάτε να κάνουμε τη διαφορά. Εκτιμούμε ιδιαίτερα τη συμμετοχή σας!'),
(1033, 'sZeroGivers3', 'Εάν έχετε οποιεσδήποτε ερωτήσεις ή χρειαστεί να κάνετε διορθώσεις στην παρούσα έκθεση, παρακαλούμε επικοινωνήστε με την εκκλησία μας στον παραπάνω αριθμό κατά τις ώρες 9:00 π.μ. έως 12:00 μ.μ. από Δευτέρα έως Παρασκευή.'),
(1048, 'sConfirmSincerely', 'Τα λέμε σύντομα'),
(1049, 'sDear', 'Αγαπητή'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Εάν δεν θέλετε να λαμβάνετε αυτά τα μηνύματα ηλεκτρονικού ταχυδρομείου από'),
(2053, 'sUnsubscribeEnd', 'στο μέλλον, επικοινωνήστε με τους διαχειριστές της εκκλησίας'),
(1017, 'sReminderNoPledge', 'Δωρεές: Δεν έχουμε καταγεγραμμένες δωρεές από εσάς για το τρέχον οικονομικό έτος.'),
(1018, 'sReminderNoPayments', 'Πληρωμές: Δεν έχουμε καταγεγραμμένες πληρωμές από εσάς για το τρέχον οικονομικό έτος.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Δέκατη', 'Αυτός είναι ο μόνος τρόπος για να μπουν χρήματα στον προϋπολογισμό.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Λειτουργία της εκκλησίας'),
  (2, 'Κυριακάτικο Σχολείο')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Σύνολο', ''),
  (2, 1, 'Μέλη', ''),
  (3, 1, 'Επισκέπτες', ''),
  (4, 2, 'Σύνολο', ''),
  (5, 2, 'Μέλη', ''),
  (6, 2, 'Επισκέπτες', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Υπεύθυνος για'),
  (1, 2, 2, 'normal', 'Μέλος'),
  (1, 3, 3, 'normal', 'Τακτικός συμμετέχων'),
  (1, 4, 4, 'normal', 'Επισκέπτης'),
  (1, 5, 5, 'normal', 'Μη συμμετέχων'),
  (1, 6, 6, 'normal', 'Μη συμμετέχοντες (προσωπικό)'),
  (1, 7, 7, 'normal', 'Αποθανών'),
  (2, 1, 1, 'normal', 'Εκπρόσωπος της οικογένειας'),
  (2, 2, 2, 'normal', 'σύζυγος/σύζυγος'),
  (2, 3, 3, 'normal', 'Παιδί'),
  (2, 4, 4, 'normal', 'Άλλο μέλος της οικογένειας'),
  (2, 5, 5, 'normal', 'Δεν είναι μέλος της οικογένειας'),
  (3, 1, 1, 'normal', 'Υπουργείο'),
  (3, 2, 2, 'normal', 'Ομάδα'),
  (3, 3, 3, 'normal', 'Μελέτη της Βίβλου'),
  (3, 4, 1, 'sunday_school', 'Ομάδα 1'),
  (3, 5, 2, 'sunday_school', 'Ομάδα 2'),
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
  (1, 'p', 'Πρόσωπο', 'Γενικές ιδιότητες των προσώπων'),
  (2, 'f', 'Οικογένεια', 'Γενικές ιδιότητες των οικογενειών'),
  (3, 'g', 'Ομάδα', 'Propriétés générales de groupes'),
  (4, 'm', 'Μενού', 'Για να εξατομικεύσετε το μενού του κυριακάτικου σχολείου.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Μια αναπηρία.', 'Ποια είναι η φύση του;',''),
  (2, 'f', 2, 'Μονογονέας', 'είναι μονογονεϊκό νοικοκυριό.', '',''),
  (3, 'g', 3, 'Νέοι', 'έχει κίνητρο να εργαστεί στο χώρο της νεολαίας.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Διαχειριστής χρήστη'),
(2, 'Ελάχιστος χρήστης'),
(3, 'Χρήστης Max αλλά όχι διαχειριστής'),
(4, 'Χρήστης Max αλλά όχι DPO και όχι Ποιμαντική Φροντίδα'),
(5, 'Χρήστης DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Κλασική ποιμενική νότα', '', 1, ''),
(2, 'Γιατί ήρθατε στην εκκλησία', '', 1, ''),
(3, 'Γιατί συνεχίζεις να έρχεσαι;', '', 1, ''),
(4, 'Έχετε κάποιο αίτημα για εμάς;', '', 1, ''),
(5, 'Πώς μάθατε για την εκκλησία;', '', 1, ''),
(6, 'Βάπτιση', 'Εκπαίδευση', 0, ''),
(7, 'Γάμος', 'Εκπαίδευση', 0, ''),
(8, 'Βοηθώντας τις σχέσεις', 'Θεραπεία και παρακολούθηση', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
