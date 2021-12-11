INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Asia/Jakarta'),
(100, 'sPhoneFormat', '99 99 999 9999'),
(101, 'sPhoneFormatWithExt', '999 99 99 999 9999'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-mm-dd'),
(110, 'sDatePickerFormat', 'Y-m-d'),
(111, 'sPhoneFormatCell', '99 99 999 999999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Surat ini adalah pengingat dari semua hadiah untuk'),
(1012, 'sTaxReport2', 'Terima kasih telah mendukung kami tahun ini. Kami sangat menikmati dedikasimu!'),
(1013, 'sTaxReport3', 'Jika kau punya pertanyaan atau perubahan untuk membuat mengenai laporan, hubungi gereja Anda di atas nomor pada jam kerja antara jam 9 pagi dan jam 5:00 sore.'),
(1015, 'sReminder1', 'Surat ini adalah ringkasan informasi yang dikirim untuk tahun fiskal saat ini'),
(1019, 'sConfirm1', 'Surat ini meringkas informasi yang tercatat di database kami. Baca dengan hati-hati, memperbaiki mereka dan mengembalikan formulir ini ke gereja kita.'),
(1020, 'sConfirm2', 'Terima kasih telah membantu kami menyelesaikan informasi ini. Jika kau ingin informasi tentang database.'),
(1021, 'sConfirm3', 'Email _____________________________________ Password ________________'),
(1022, 'sConfirm4', '[  ] Aku tidak ingin berhubungan dengan gereja lagi (berdetik di sini untuk dihapus dari rekaman Anda.'),
(1026, 'sPledgeSummary1', 'Summary janji-janji sumbangan dan pembayaran untuk tahun pajak ini'),
(1027, 'sPledgeSummary2', 'untuk dia'),
(1028, 'sDirectoryDisclaimer1', 'Kami telah bekerja untuk membuat data ini akurat sebisa mungkin. Jika kau melihat kesalahan atau kelalaian, hubungi kami. Direktori ini digunakan untuk orang-orang dari'),
(1029, 'sDirectoryDisclaimer2', ', dan informasi yang terkandung tidak akan digunakan untuk tujuan komersial.'),
(1031, 'sZeroGivers', 'Surat ini meringkas pembayaran'),
(1032, 'sZeroGivers2', 'Terima kasih telah membantu kami membuat perbedaan. Kami menghargai partisipasimu!'),
(1033, 'sZeroGivers3', 'Jika kau punya pertanyaan atau koreksi untuk laporan ini, hubungi gereja kami di atas nomor pada jam 9:00 sampai jam 12:00 sore. Senin sampai Jumat.'),
(1048, 'sConfirmSincerely', 'Sampai jumpa'),
(1049, 'sDear', 'Sayang (sayang)'),
(1051, 'bTimeEnglish', '1'),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'Rp'),
(2052, 'sUnsubscribeStart', 'Jika kau tidak ingin menerima email ini dari lagi'),
(2053, 'sUnsubscribeEnd', 'di masa depan, hubungi administrator gereja'),
(1017, 'sReminderNoPledge', 'Donasi: Kami tidak memiliki rekaman sumbangan darimu selama tahun ini.'),
(1018, 'sReminderNoPayments', 'Pembayaran: kami tidak memiliki pendaftaran di bagian Anda untuk tahun pajak ini.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Judul', 'uang masuk untuk anggaran.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Layanan Gereja'),
  (2, 'Sekolah Minggu')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Anggota', ''),
  (3, 1, 'Pengunjung', ''),
  (4, 2, 'Total', ''),
  (5, 2, 'Anggota', ''),
  (6, 2, 'Pengunjung', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Tanggung jawab'),
  (1, 2, 2, 'normal', 'Anggota'),
  (1, 3, 3, 'normal', 'Peserta biasa'),
  (1, 4, 4, 'normal', 'Tamu'),
  (1, 5, 5, 'normal', 'Tidak ada peserta'),
  (1, 6, 6, 'normal', 'Bukan peserta (staf)'),
  (1, 7, 7, 'normal', 'Mati'),
  (2, 1, 1, 'normal', 'Perwakilan keluarga'),
  (2, 2, 2, 'normal', 'Spouse'),
  (2, 3, 3, 'normal', 'Nak'),
  (2, 4, 4, 'normal', 'Anggota keluarga lainnya'),
  (2, 5, 5, 'normal', 'Ini bukan anggota keluarga'),
  (3, 1, 1, 'normal', 'Kementerian gereja'),
  (3, 2, 2, 'normal', 'Tim'),
  (3, 3, 3, 'normal', 'Belajar Alkitab'),
  (3, 4, 1, 'sunday_school', 'Grup 1'),
  (3, 5, 2, 'sunday_school', 'Grup 2'),
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
  (1, 'p', 'Tidak ada', 'Jenderal properti orang'),
  (2, 'f', 'Keluarga', 'Jenderal properti keluarga'),
  (3, 'g', 'Kelompok', 'Jenderal properti kelompok'),
  (4, 'm', 'Menu', 'Untuk menyesuaikan menu sekolah Minggu.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Ditolak', 'Untuk cacat.', 'Apa sifat alaminya?',''),
  (2, 'f', 2, 'Orang tua terisolasi', 'adalah satu keluarga orang tua tunggal.', '',''),
  (3, 'g', 3, 'Young', 'dimotivasi untuk bekerja di masa muda.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'User Administrator'),
(2, 'Utilisateur Minimum'),
(3, 'Gunakan Max tapi tidak Admin'),
(4, 'Gunakan Max tapi tidak DPO dan bukan pengawasan pastor'),
(5, 'Pengguna DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Catatan Pastor klasik', '', 1, ''),
(2, 'Kenapa kau datang ke gereja?', '', 1, ''),
(3, 'Kenapa kau terus datang?', '', 1, ''),
(4, 'Apakah Anda memiliki permintaan untuk membuat kita?', '', 1, ''),
(5, 'Bagaimana kau mendengar tentang gereja?', '', 1, ''),
(6, 'Pembaptisan', 'Latihan', 0, ''),
(7, 'Pernikahan', 'Latihan', 0, ''),
(8, 'Bantuan hubungan', 'Terapi dan tindak lanjut', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
