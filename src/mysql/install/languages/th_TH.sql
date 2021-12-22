INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'wah'),
(65, 'sTimeZone', 'Asia/Bangkok'),
(100, 'sPhoneFormat', '99 9999 9999'),
(101, 'sPhoneFormatWithExt', '999 9999 9999'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '99 9999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'จดหมายนี้เป็นการเตือนสำเนียงของขวัญทั้งหมด'),
(1012, 'sTaxReport2', 'ขอบคุณที่สนับสนุนเราในปีนี้ เราชอบที่คุณต้องทำมาก'),
(1013, 'sTaxReport3', 'ถ้าคุณมีคำถามหรือเปลี่ยนแปลง ที่จะทำข้อมูลรายงาน ติดต่อโบสถ์ของคุณ ที่ด้านเบื้องบน ระหว่าง 3 ทุ่ม ถึงห้าทุ่ม'),
(1015, 'sReminder1', 'จดหมายนี้เป็นข้อสรุปข้อมูลที่ถูกส่งมาให้ปีภาษีในปัจจุบัน'),
(1019, 'sConfirm1', 'จดหมายนี้สรุปข้อมูลข่าวสาร ที่บันทึกไว้ในฐานข้อมูล อ่านอย่างระมัดระวัง แก้ไขมัน และกลับมาที่โบสถ์ของเรา'),
(1020, 'sConfirm2', 'ขอบคุณที่ช่วยให้เราทำข้อมูลนี้เสร็จ ถ้าคุณต้องการข้อมูลเกี่ยวกับฐานข้อมูล'),
(1021, 'sConfirm3', 'อีเมล _____________________________________ รหัสผ่าน ________________'),
(1022, 'sConfirm4', '[  ] ฉันไม่อยากมีส่วนร่วมกับโบสถ์อีกต่อไป (ติดอยู่ที่นี่เพื่อลบทิ้ง)'),
(1026, 'sPledgeSummary1', 'Rซัมเมอร์ข้อตกลงของขวัญและจ่ายเงินสำหรับปีภาษีนี้'),
(1027, 'sPledgeSummary2', 'สำหรับเขา'),
(1028, 'sDirectoryDisclaimer1', 'เราได้ทำงานเพื่อให้ข้อมูลนี้ถูกต้อง ที่สุดเท่าที่จะทำได้ ถ้าคุณสังเกตเห็นความผิดพลาดหรือการล้มเหลว ติดต่อเรา นี่เป็นคำสั่งของผู้คน'),
(1029, 'sDirectoryDisclaimer2', ', และข้อมูลที่มีอยู่จะไม่ถูกใช้เพื่อจุดประสงค์เชิงพาณิชย์'),
(1031, 'sZeroGivers', 'จดหมายนี้สรุปการจ่ายเงินของ'),
(1032, 'sZeroGivers2', 'ขอบคุณที่ช่วยให้เราสร้างความแตกต่าง เราซาบซึ้งในการมีส่วนร่วมของคุณ!'),
(1033, 'sZeroGivers3', 'ถ้าคุณมีคำถามหรือแก้ไขรายงานนี้ ติดต่อโบสถ์ของเราที่ด้านบน ในช่วงสามชั่วโมงถึง 12 โมงเย็น วันจันทร์ถึงวันศุกร์'),
(1048, 'sConfirmSincerely', 'แล้วพบกันครับ'),
(1049, 'sDear', 'ที่รัก (ถึง)'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'Baht'),
(2052, 'sUnsubscribeStart', 'ถ้าคุณไม่ต้องการรับอีเมล์พวกนี้อีก'),
(2053, 'sUnsubscribeEnd', 'ในอนาคต ติดต่อผู้บริหารโบสถ์'),
(1017, 'sReminderNoPledge', 'บริจาค: เราไม่มีการบันทึก การบริจาคจากคุณ สำหรับปีนี้'),
(1018, 'sReminderNoPayments', 'เงิน : เรา ไม่ มี การ จดทะเบียน ใน ส่วน ของ คุณ สำหรับ ปี นี้ ครับ')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Title', 'เงินสำหรับบรรทุน')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'บริการคริสตจักร'),
  (2, 'โรงเรียนวันอาทิตย์')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'ทั้งหมด', ''),
  (2, 1, 'สมาชิก', ''),
  (3, 1, 'ผู้เข้าชม', ''),
  (4, 2, 'ทั้งหมด', ''),
  (5, 2, 'สมาชิก', ''),
  (6, 2, 'ผู้เข้าชม', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'รับผิดชอบได้'),
  (1, 2, 2, 'normal', 'สมาชิก'),
  (1, 3, 3, 'normal', 'ผู้เข้าร่วมทั่วไป'),
  (1, 4, 4, 'normal', 'แขก'),
  (1, 5, 5, 'normal', 'ไม่มีผู้เข้าร่วม'),
  (1, 6, 6, 'normal', 'ไม่มีผู้เข้าร่วม'),
  (1, 7, 7, 'normal', 'ตาย'),
  (2, 1, 1, 'normal', 'ตัวแทนครอบครัว'),
  (2, 2, 2, 'normal', 'คู่ครอง'),
  (2, 3, 3, 'normal', 'เด็ก'),
  (2, 4, 4, 'normal', 'สมาชิกครอบครัวอีกคน'),
  (2, 5, 5, 'normal', 'ไม่ใช่สมาชิกครอบครัว'),
  (3, 1, 1, 'normal', 'หน่วยงาน'),
  (3, 2, 2, 'normal', 'ทีม'),
  (3, 3, 3, 'normal', 'ศึกษาไบเบิ้ล'),
  (3, 4, 1, 'sunday_school', 'กลุ่ม 1'),
  (3, 5, 2, 'sunday_school', 'กลุ่ม 2'),
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
  (1, 'p', 'ไม่มีใคร', 'คุณสมบัติทั่วไปของคน'),
  (2, 'f', 'Family', 'สมบัติทั่วไปของครอบครัว'),
  (3, 'g', 'กลุ่ม', 'สมบัติทั่วไปของกลุ่ม'),
  (4, 'm', 'เมนู', 'เพื่อรักษาเมนูโรงเรียนวันอาทิตย์')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'ปลดเคลื่อนไหว', 'เพื่อความพิการ', 'เขาเป็นคนยังไง',''),
  (2, 'f', 2, 'พ่อแม่แยกตัวเอง', 'เป็นครอบครัวเดียวของพ่อแม่', '',''),
  (3, 'g', 3, 'ยัง', 'มีแรงจูงใจที่จะทำงานตอนเยาวชน', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'ผู้นำ ผู้ใช้'),
(2, 'ผู้ใช้ขั้นต่ำ'),
(3, 'ใช้แม็กซ์ แต่ไม่ใช่แอดมิน'),
(4, 'ผู้ใช้แม็กซ์ แต่ไม่ใช่ดีพีโอ และไม่ใช่การตรวจสอบพระบาทหลวง'),
(5, 'ผู้ใช้ดีพีโอ')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'โน้ตพระหลวงคลาสสิค', '', 1, ''),
(2, 'โน้ตพระหลวงคลาสสิค', '', 1, ''),
(3, 'ทำไมคุณถึงมาเรื่อย', '', 1, ''),
(4, 'คุณมีอะไรจะขอพวกเราไหม', '', 1, ''),
(5, 'คุณรู้เรื่องโบสถ์ได้ยังไง', '', 1, ''),
(6, 'บาปทิซึม', 'ฝึก', 0, ''),
(7, 'แต่งงาน', 'ฝึก', 0, ''),
(8, 'ช่วยให้เกิดความสัมพันธ์', 'การบำบัดและตามข้อมูล', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
