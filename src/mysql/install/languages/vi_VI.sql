INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Asia/Ho_Chi_Minh'),
(100, 'sPhoneFormat', '999 9999999'),
(101, 'sPhoneFormatWithExt', '99 999 9999999'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '99 99 99 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(113, 'sPhoneCountryCallingCode', '0084'),
(1011, 'sTaxReport1', 'Bức thư này là một lời nhắc nhở về mọi món quà'),
(1012, 'sTaxReport2', 'Cảm ơn đã ủng hộ chúng tôi năm nay. Chúng tôi rất thích sự cống hiến của anh!'),
(1013, 'sTaxReport3', 'Nếu bạn có bất kỳ câu hỏi hay thay đổi nào liên quan đến báo cáo, liên lạc với nhà thờ ở số trên cao trong giờ làm việc từ 9 giờ sáng đến 5 giờ chiều.'),
(1015, 'sReminder1', 'Bức thư này là bản tóm tắt thông tin được gửi cho năm tài chính hiện tại'),
(1019, 'sConfirm1', 'Bức thư này tóm tắt lại thông tin được ghi lại trong cơ sở dữ liệu của chúng tôi. Hãy đọc cẩn thận, sửa lại và trả lại cái mẫu này cho nhà thờ của chúng ta.'),
(1020, 'sConfirm2', 'Merici đã giúp chúng tôi hoàn thành thông tin này. Nếu anh muốn thông tin về cơ sở dữ liệu.'),
(1021, 'sConfirm3', 'Email _____________________________________ Mật khẩu ________________'),
(1022, 'sConfirm4', '[  ] Tôi không muốn bị liên hệ với nhà thờ nữa (bị té ở đây để bị xóa khỏi bản thu của các bạn).'),
(1026, 'sPledgeSummary1', 'Sự bền vững của những lời hứa và tiền tài trợ cho năm nay'),
(1027, 'sPledgeSummary2', 'cho anh ta'),
(1028, 'sDirectoryDisclaimer1', 'Chúng tôi đã làm việc để làm cho dữ liệu này càng chính xác càng tốt. Nếu bạn để ý lỗi hoặc sai sót, hãy liên lạc với chúng tôi. Thư mục này được dùng cho mọi người'),
(1029, 'sDirectoryDisclaimer2', ', và những thông tin chứa đựng sẽ không được sử dụng cho mục đích thương mại.'),
(1031, 'sZeroGivers', 'Bức thư này tóm tắt các khoản thanh toán'),
(1032, 'sZeroGivers2', 'Cảm ơn đã giúp chúng tôi tạo ra sự khác biệt. Chúng tôi đánh giá cao sự tham gia của các bạn!'),
(1033, 'sZeroGivers3', 'Nếu bạn có câu hỏi hay sửa chữa cho báo cáo này, hãy liên lạc với nhà thờ ở số trên cao trong vài giờ từ 9 giờ sáng đến 12 giờ tối. Thứ Hai đến thứ sáu.'),
(1048, 'sConfirmSincerely', 'Hẹn gặp lại'),
(1049, 'sDear', 'Gửi.'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'đồng'),
(2052, 'sUnsubscribeStart', 'Nếu bạn không muốn nhận những email này từ nữa'),
(2053, 'sUnsubscribeEnd', 'trong tương lai, liên lạc với các nhà thờ'),
(1017, 'sReminderNoPledge', 'Chúng tôi không có ghi chép về tiền quyên góp từ ông trong năm thuế này.'),
(1018, 'sReminderNoPayments', 'Tiền lương: chúng tôi không đăng ký ở phần của anh trong năm thuế này.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Title', 'tiền vào ngân sách.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Dịch vụ nhà thờ'),
  (2, 'Trường học ngày Chủ nhật')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Tất nhiên.', ''),
  (2, 1, 'Các thành viên.', ''),
  (3, 1, 'Có khách', ''),
  (4, 2, 'Tất nhiên.', ''),
  (5, 2, 'Các thành viên.', ''),
  (6, 2, 'Có khách', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Trách nhiệm.'),
  (1, 2, 2, 'normal', 'Thành viên.'),
  (1, 3, 3, 'normal', 'Tham gia thông thường'),
  (1, 4, 4, 'normal', 'Khách.'),
  (1, 5, 5, 'normal', 'Không tham gia'),
  (1, 6, 6, 'normal', 'Không tham gia.'),
  (1, 7, 7, 'normal', 'Chết rồi.'),
  (2, 1, 1, 'normal', 'Dân biểu gia đình'),
  (2, 2, 2, 'normal', 'Spouse'),
  (2, 3, 3, 'normal', 'Con cái'),
  (2, 4, 4, 'normal', 'Một người trong gia đình khác'),
  (2, 5, 5, 'normal', 'Đó không phải là một thành viên trong gia đình'),
  (3, 1, 1, 'normal', 'Bộ Giáo Hội'),
  (3, 2, 2, 'normal', 'Đội hình'),
  (3, 3, 3, 'normal', 'Nghiên cứu về Kinh Thánh'),
  (3, 4, 1, 'sunday_school', 'Nhóm 1'),
  (3, 5, 2, 'sunday_school', 'Nhóm 2'),
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
  (1, 'p', 'Không ai cả.', 'Những tính chất chung của người'),
  (2, 'f', 'Gia đình', 'Những đặc tính của gia đình'),
  (3, 'g', 'Tập đoàn', 'Các đặc tính chung của các nhóm'),
  (4, 'm', 'Menu', 'Để chỉnh sửa thực đơn trường ngày Chủ nhật.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Vô hiệu hóa', 'Vì khuyết tật.', 'Bản chất của anh ta là gì?',''),
  (2, 'f', 2, 'Phụ huynh cô lập', 'là một gia đình đơn thân.', '',''),
  (3, 'g', 3, 'Trẻ', 'được thúc đẩy để làm việc trong thời thanh niên.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Người sử dụng'),
(2, 'Người dùng tối thiểu'),
(3, 'User Max nhưng không phải Admin'),
(4, 'User Max nhưng không phải DPO và không phải là mục sư giám sát'),
(5, 'Người dùng DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Ghi chú mục sư cổ điển', '', 1, ''),
(2, 'Tại sao anh lại tới nhà thờ?', '', 1, ''),
(3, 'Sao anh cứ đến thế?', '', 1, ''),
(4, 'Anh có yêu cầu gì không?', '', 1, ''),
(5, 'Làm sao anh biết về nhà thờ?', '', 1, ''),
(6, 'Rửa tội', 'Huấn luyện', 0, ''),
(7, 'Hôn nhân', 'Huấn luyện', 0, ''),
(8, 'Giúp mối quan hệ', 'Trị liệu và tiếp tục', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
