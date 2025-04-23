--
-- Table structure for table `addressbooks`
--
CREATE TABLE addressbooks (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    displayname VARCHAR(255),
    uri VARBINARY(200),
    description TEXT,
    synctoken INT(11) UNSIGNED NOT NULL DEFAULT '1',
    groupId mediumint(8) NOT NULL default -1 COMMENT '-1 personal addressbook, >1 for a group in the CRM',
    UNIQUE(principaluri(100), uri(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `addressbooks`
--

CREATE TABLE addressbookchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    addressbookid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX addressbookid_synctoken (addressbookid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `calendars`
--


CREATE TABLE calendars (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    synctoken INTEGER UNSIGNED NOT NULL DEFAULT '1',
    components VARBINARY(21)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `calendarinstances`
--

CREATE TABLE calendarinstances (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendarid INTEGER UNSIGNED NOT NULL  DEFAULT '0',
    principaluri VARBINARY(100),
    access TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = owner, 2 = read, 3 = readwrite',
    displayname VARCHAR(100),
    uri VARBINARY(200),
    description TEXT,
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10) DEFAULT '#000000',
    visible BOOLEAN NOT NULL default 0,
    present BOOLEAN NOT NULL default 1,
    timezone TEXT,
    transparent TINYINT(1) NOT NULL DEFAULT '0',
    share_href VARBINARY(100),
    share_displayname VARCHAR(100),
    share_invitestatus TINYINT(1) NOT NULL DEFAULT '2' COMMENT '1 = noresponse, 2 = accepted, 3 = declined, 4 = invalid',
    grpid mediumint(9) NOT NULL DEFAULT '0',
    cal_type TINYINT(2) NOT NULL DEFAULT '1' COMMENT '1 = personal, 2 = room, 3 = computer, 4 = video',
    UNIQUE(principaluri, uri),
    UNIQUE(calendarid, principaluri),
    UNIQUE(calendarid, share_href)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `calendarchanges`
--

CREATE TABLE calendarchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `calendarsubscriptions`
--

CREATE TABLE calendarsubscriptions (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    principaluri VARBINARY(100) NOT NULL,
    source TEXT,
    displayname VARCHAR(100),
    refreshrate VARCHAR(10),
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10),
    striptodos TINYINT(1) NULL,
    stripalarms TINYINT(1) NULL,
    stripattachments TINYINT(1) NULL,
    lastmodified INT(11) UNSIGNED,
    UNIQUE(principaluri, uri)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `schedulingobjects`
--


CREATE TABLE schedulingobjects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `locks`
--

CREATE TABLE locks (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner VARCHAR(100),
    timeout INTEGER UNSIGNED,
    created INTEGER,
    token VARBINARY(100),
    scope TINYINT,
    depth TINYINT,
    uri VARBINARY(1000),
    INDEX(token),
    INDEX(uri(100))
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `principals`
--

CREATE TABLE principals (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    email VARBINARY(80),
    displayname VARCHAR(80),
    UNIQUE(uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO principals (uri,email,displayname) VALUES
('principals/admin', 'admin@example.org','Administrator'),
('principals/admin/calendar-proxy-read', null, null),
('principals/admin/calendar-proxy-write', null, null);

--
-- Table structure for table `groupmembers`
--


CREATE TABLE groupmembers (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principal_id INTEGER UNSIGNED NOT NULL,
    member_id INTEGER UNSIGNED NOT NULL,
    UNIQUE(principal_id, member_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;



--
-- Table structure for table `propertystorage`
--

CREATE TABLE propertystorage (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    path VARBINARY(1024) NOT NULL,
    name VARBINARY(100) NOT NULL,
    valuetype INT UNSIGNED,
    value MEDIUMBLOB
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE UNIQUE INDEX path_property ON propertystorage (path(600), name(100));


--
-- Table structure for table `version_ver`
--

CREATE TABLE `version_ver` (
  `ver_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `ver_version` varchar(50) NOT NULL default '',
  `ver_update_start` datetime default NULL,
  `ver_update_end` datetime default NULL,
  PRIMARY KEY  (`ver_ID`),
  UNIQUE KEY `ver_version` (`ver_version`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `canvassdata_can`
--

CREATE TABLE `canvassdata_can` (
  `can_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `can_famID` mediumint(9) NOT NULL default '0',
  `can_Canvasser` mediumint(9) NOT NULL default '0',
  `can_FYID` mediumint(9) default NULL,
  `can_date` date default NULL,
  `can_Positive` text,
  `can_Critical` text,
  `can_Insightful` text,
  `can_Financial` text,
  `can_Suggestion` text,
  `can_NotInterested` tinyint(1) NOT NULL default '0',
  `can_WhyNotInterested` text,
  PRIMARY KEY  (`can_ID`),
  UNIQUE KEY `can_ID` (`can_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `canvassdata_can`
--


-- --------------------------------------------------------

--
-- Table structure for table `config_cfg`
--



CREATE TABLE `config_cfg` (
  `cfg_id` int(11) NOT NULL default '0',
  `cfg_name` varchar(50) NOT NULL default '',
  `cfg_value` text,
  PRIMARY KEY  (`cfg_id`),
  UNIQUE KEY `cfg_name` (`cfg_name`),
  KEY `cfg_id` (`cfg_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `deposit_dep`
--

CREATE TABLE `deposit_dep` (
  `dep_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `dep_Date` date default NULL,
  `dep_Comment` text,
  `dep_EnteredBy` mediumint(9) unsigned default NULL,
  `dep_Closed` tinyint(1) NOT NULL default '0',
  `dep_Type` enum('Bank','CreditCard','BankDraft','eGive') NOT NULL default 'Bank',
  `dep_Fund` mediumint(6) NOT NULL default '0',
  PRIMARY KEY  (`dep_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `deposit_dep`
--


-- --------------------------------------------------------

--
-- Table structure for table `donationfund_fun`
--

CREATE TABLE `donationfund_fun` (
  `fun_ID` tinyint(3) NOT NULL auto_increment,
  `fun_Active` enum('true','false') NOT NULL default 'true',
  `fun_Name` varchar(30) default NULL,
  `fun_Description` varchar(100) default NULL,
  PRIMARY KEY  (`fun_ID`),
  UNIQUE KEY `fun_ID` (`fun_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=2 ;

--
-- Dumping data for table `donationfund_fun`
--

INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Pledges', 'Pledge income for the operating budget');

-- --------------------------------------------------------

--
-- Table structure for table `email_message_pending_emp`
--

CREATE TABLE `email_message_pending_emp` (
  `emp_usr_id` mediumint(9) unsigned NOT NULL default '0',
  `emp_to_send` smallint(5) unsigned NOT NULL default '0',
  `emp_subject` varchar(128) NOT NULL,
  `emp_message` text NOT NULL,
  `emp_attach_name` text NULL,
  `emp_attach` tinyint(1)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `email_message_pending_emp`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_recipient_pending_erp`
--

CREATE TABLE `email_recipient_pending_erp` (
  `erp_id` smallint(5) unsigned NOT NULL default '0',
  `erp_usr_id` mediumint(9) unsigned NOT NULL default '0',
  `erp_num_attempt` smallint(5) unsigned NOT NULL default '0',
  `erp_failed_time` datetime default NULL,
  `erp_email_address` varchar(50) NOT NULL default ''
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


--
-- Dumping data for table `event_attend`
--


-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE `event_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(255) NOT NULL default '',
  `type_defstarttime` time NOT NULL default '00:00:00',
  `type_defrecurtype` enum('none','weekly','monthly','yearly') NOT NULL default 'none',
  `type_color` VARBINARY(10),
  `type_defrecurDOW` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL default 'Sunday',
  `type_defrecurDOM` char(2) NOT NULL default '0',
  `type_defrecurDOY` date NOT NULL default '2000-01-01',
  `type_active` int(1) NOT NULL default '1',
  `type_grpid` mediumint(9),
  PRIMARY KEY  (`type_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=3 ;

--
-- Dumping data for table `event_types`
--

INSERT INTO `event_types` (`type_id`, `type_name`, `type_defstarttime`, `type_defrecurtype`, `type_defrecurDOW`, `type_defrecurDOM`, `type_defrecurDOY`, `type_active`) VALUES
  (1, 'Church Service', '10:30:00', 'weekly', 'Sunday', '', '2016-01-01', 1),
  (2, 'Sunday School', '09:30:00', 'weekly', 'Sunday', '', '2016-01-01', 1);

--
-- Dumping data for table `email_recipient_pending_erp`
--


-- --------------------------------------------------------

--
-- Table structure for table `eventcountnames_evctnm`
--

CREATE TABLE `eventcountnames_evctnm` (
  `evctnm_countid` int(5) NOT NULL auto_increment,
  `evctnm_eventtypeid` int(11) NOT NULL default '0',
  `evctnm_countname` varchar(20) NOT NULL default '',
  `evctnm_notes` varchar(20) NOT NULL default '',
  UNIQUE KEY `evctnm_countid` (`evctnm_countid`),
  UNIQUE KEY `evctnm_eventtypeid` (`evctnm_eventtypeid`,`evctnm_countname`),
  CONSTRAINT fk_evctnm_eventtypeid FOREIGN KEY (evctnm_eventtypeid) REFERENCES event_types(type_id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=7 ;

--
-- Dumping data for table `eventcountnames_evctnm`
--

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Members', ''),
  (3, 1, 'Visitors', ''),
  (4, 2, 'Total', ''),
  (5, 2, 'Members', ''),
  (6, 2, 'Visitors', '');


--
-- Dumping data for table `eventcounts_evtcnt`
--


-- --------------------------------------------------------

--
-- Table structure for table `events_event`
--

CREATE TABLE `events_event` (
  `event_id` int(11) NOT NULL auto_increment,
  `event_type` int(11) NOT NULL default '0',
  `event_title` varchar(255) NOT NULL default '',
  `event_desc` varchar(255) default NULL,
  `event_text` text,
  `event_start` datetime NOT NULL,
  `event_end` datetime NOT NULL,
  `event_allday` BOOLEAN NOT NULL default 0,
  `event_last_occurence` datetime NOT NULL,
  `inactive` int(1) NOT NULL default '0',
  `event_typename` varchar(40) NOT NULL default '',
  `event_grpid` mediumint(9),
  `event_location` text,
  `event_coordinates` varchar(255) NOT NULL default '',
  `event_calendardata` mediumblob,
  `event_uri` varbinary(200) DEFAULT NULL,
  `event_calendarid` INTEGER UNSIGNED NOT NULL  DEFAULT '0',
  `event_lastmodified` int(11) UNSIGNED DEFAULT NULL,
  `event_etag` varbinary(32) DEFAULT NULL,
  `event_size` int(11) UNSIGNED NOT NULL,
  `event_componenttype` varbinary(8) DEFAULT NULL,
  `event_uid` varbinary(200) DEFAULT NULL,
  `event_creator_user_id` mediumint(9) DEFAULT NULL COMMENT 'For resource slot : the owner is the creator',
  `event_link` varchar(255) default NULL,
  PRIMARY KEY  (`event_id`),
    UNIQUE(event_calendarid, event_uri),
    INDEX calendarid_time (event_calendarid)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


--
-- Dumping data for table `events_event`
--

-- --------------------------------------------------------

--
-- Table structure for table `eventcounts_evtcnt`
--

CREATE TABLE `eventcounts_evtcnt` (
  `evtcnt_eventid` int(11) NOT NULL default '0',
  `evtcnt_countid` int(5) NOT NULL default '0',
  `evtcnt_countname` varchar(20) default NULL,
  `evtcnt_countcount` int(6) default NULL,
  `evtcnt_notes` varchar(255) default NULL,
  PRIMARY KEY  (`evtcnt_eventid`,`evtcnt_countid`),
  CONSTRAINT fk_evtcnt_event_ID FOREIGN KEY (evtcnt_eventid) REFERENCES events_event(event_id) ON DELETE CASCADE,
  CONSTRAINT fk_evtcnt_countid FOREIGN KEY (evtcnt_countid) REFERENCES eventcountnames_evctnm(evctnm_countid) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `event_attend`
--

CREATE TABLE `event_attend` (
  `attend_id` int(11) NOT NULL auto_increment,
  `event_id` int(11) NOT NULL default '0',
  `person_id` int(11) NOT NULL default '0',
  `checkin_date` datetime default NULL,
  `checkin_id` int(11) default NULL,
  `checkout_date` datetime default NULL,
  `checkout_id` int(11) default NULL,
  PRIMARY KEY  (`attend_id`),
  UNIQUE KEY `event_id` (`event_id`,`person_id`),
  CONSTRAINT fk_attend_event_ID FOREIGN KEY (event_id) REFERENCES events_event(event_id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `family_custom`
--

CREATE TABLE `family_custom` (
  `fam_ID` mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (`fam_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `family_custom`
--


-- --------------------------------------------------------

--
-- Table structure for table `family_custom_master`
--

CREATE TABLE `family_custom_master` (
  `family_custom_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `fam_custom_Order` smallint(6) NOT NULL default '0',
  `fam_custom_Field` varchar(5) NOT NULL default '',
  `fam_custom_Name` varchar(40) NOT NULL default '',
  `fam_custom_Special` mediumint(8) unsigned default NULL,
  `fam_custom_Side` enum('left','right') NOT NULL default 'left',
  `fam_custom_FieldSec` tinyint(4) NOT NULL default '1',
  `fam_custom_comment` text NULL default NULL COMMENT 'comment for GDPR',
  `fam_custom_confirmation_datas` BOOLEAN NOT NULL default 1 COMMENT 'confirmations default datas',
  `type_ID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`family_custom_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `family_custom_master`
--


-- --------------------------------------------------------

--
-- Table structure for table `family_fam`
--

CREATE TABLE `family_fam` (
  `fam_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `fam_Name` varchar(50) default NULL,
  `fam_Address1` varchar(255) default NULL,
  `fam_Address2` varchar(255) default NULL,
  `fam_City` varchar(50) default NULL,
  `fam_State` varchar(50) default NULL,
  `fam_Zip` varchar(50) default NULL,
  `fam_Country` varchar(50) default NULL,
  `fam_HomePhone` varchar(30) default NULL,
  `fam_WorkPhone` varchar(30) default NULL,
  `fam_CellPhone` varchar(30) default NULL,
  `fam_Email` varchar(100) default NULL,
  `fam_WeddingDate` date default NULL,
  `fam_DateEntered` datetime NOT NULL,
  `fam_DateLastEdited` datetime default NULL,
  `fam_EnteredBy` smallint(5) NOT NULL default '0',
  `fam_EditedBy` smallint(5) unsigned default '0',
  `fam_scanCheck` text,
  `fam_scanCredit` text,
  `fam_SendNewsLetter` enum('FALSE','TRUE') NOT NULL default 'FALSE',
  `fam_DateDeactivated` date default NULL,
  `fam_OkToCanvass` enum('FALSE','TRUE') NOT NULL default 'FALSE',
  `fam_Canvasser` smallint(5) unsigned NOT NULL default '0',
  `fam_Latitude` double default NULL,
  `fam_Longitude` double default NULL,
  `fam_Envelope` mediumint(9) NOT NULL default '0',
  `fam_confirm_report` enum('No', 'Pending','Done') default 'No' COMMENT 'To confirm report of all families',
  PRIMARY KEY  (`fam_ID`),
  KEY `fam_ID` (`fam_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `family_fam`
--


-- --------------------------------------------------------

--
-- Table structure for table `groupprop_master`
--

CREATE TABLE `groupprop_master` (
  `grp_mster_id` mediumint(9) unsigned NOT NULL auto_increment,
  `grp_ID` mediumint(9) unsigned NOT NULL default '0',
  `prop_ID` tinyint(3) unsigned NOT NULL default '0',
  `prop_Field` varchar(5) NOT NULL default '0',
  `prop_Name` varchar(40) default NULL,
  `prop_Description` varchar(60) default NULL,
  `type_ID` smallint(5) unsigned NOT NULL default '0',
  `prop_Special` mediumint(9) unsigned default NULL,
  `prop_PersonDisplay` enum('false','true') NOT NULL default 'false',
  PRIMARY KEY  (`grp_mster_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT='Group-specific properties order, name, description, type';

--
-- Dumping data for table `groupprop_master`
--


-- --------------------------------------------------------

--
-- Table structure for table `group_grp`
--

CREATE TABLE `group_grp` (
  `grp_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `grp_Type` tinyint(4) NOT NULL default '0',
  `grp_RoleListID` mediumint(8) unsigned NOT NULL default '0',
  `grp_DefaultRole` mediumint(9) NOT NULL default '0',
  `grp_Name` varchar(50) NOT NULL default '',
  `grp_Description` text,
  `grp_hasSpecialProps` BOOLEAN NOT NULL default 0,
  `grp_active` BOOLEAN NOT NULL default 1,
  `grp_include_email_export` BOOLEAN NOT NULL default 1,
  PRIMARY KEY  (`grp_ID`),
  UNIQUE KEY `grp_ID` (`grp_ID`),
  KEY `grp_ID_2` (`grp_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `group_grp`
--


-- --------------------------------------------------------

--
-- Table structure for table `group_type`
--

CREATE TABLE `group_type` (
    `grptp_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `grptp_grp_ID` mediumint(8) unsigned NOT NULL,
    `grptp_lst_OptionID` mediumint(8) unsigned NOT NULL default '0',
    CONSTRAINT fk_grptp_grp_ID FOREIGN KEY (grptp_grp_ID) REFERENCES group_grp(grp_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `group_type`
--

-- --------------------------------------------------------

--
-- Table structure for table `istlookup_lu`
--

CREATE TABLE `istlookup_lu` (
  `lu_fam_ID` mediumint(9) NOT NULL default '0',
  `lu_LookupDateTime` datetime NOT NULL default '2000-01-01 00:00:00',
  `lu_DeliveryLine1` varchar(255) default NULL,
  `lu_DeliveryLine2` varchar(255) default NULL,
  `lu_City` varchar(50) default NULL,
  `lu_State` varchar(50) default NULL,
  `lu_ZipAddon` varchar(50) default NULL,
  `lu_Zip` varchar(10) default NULL,
  `lu_Addon` varchar(10) default NULL,
  `lu_LOTNumber` varchar(10) default NULL,
  `lu_DPCCheckdigit` varchar(10) default NULL,
  `lu_RecordType` varchar(10) default NULL,
  `lu_LastLine` varchar(255) default NULL,
  `lu_CarrierRoute` varchar(10) default NULL,
  `lu_ReturnCodes` varchar(10) default NULL,
  `lu_ErrorCodes` varchar(10) default NULL,
  `lu_ErrorDesc` varchar(255) default NULL,
  PRIMARY KEY  (`lu_fam_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT='US Address Verification Lookups From Intelligent Search Tech';

--
-- Dumping data for table `istlookup_lu`
--


-- --------------------------------------------------------

--
-- Table structure for table `list_lst`
--

CREATE TABLE `list_lst` (
  `lst_ID` mediumint(8) unsigned NOT NULL default '0',
  `lst_OptionID` mediumint(8) unsigned NOT NULL default '0',
  `lst_OptionSequence` tinyint(3) unsigned NOT NULL default '0',
  `lst_Type` enum('normal','sunday_school') NOT NULL default 'normal',
  `lst_OptionName` varchar(50) NOT NULL default ''
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `list_lst`
--

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Manager'),
  (1, 2, 2, 'normal', 'Member'),
  (1, 3, 3, 'normal', 'Regular Attender'),
  (1, 4, 4, 'normal', 'Guest'),
  (1, 5, 5, 'normal', 'Non-Attender'),
  (1, 6, 6, 'normal', 'Non-Attender (staff)'),
  (1, 7, 7, 'normal', 'Deceased'),
  (2, 1, 1, 'normal', 'Head of Household'),
  (2, 2, 2, 'normal', 'Spouse'),
  (2, 3, 3, 'normal', 'Child'),
  (2, 4, 4, 'normal', 'Other Relative'),
  (2, 5, 5, 'normal', 'Non Relative'),
  (3, 1, 1, 'normal', 'Ministry'),
  (3, 2, 2, 'normal', 'Team'),
  (3, 3, 3, 'normal', 'Bible Study'),
  (3, 4, 1, 'sunday_school', 'Group 1'),
  (3, 5, 2, 'sunday_school', 'Group 2'),
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
  (12, 2, 2, 'normal', 'Student');

  --
-- A person classification can have an icon
--
CREATE TABLE list_icon (
    `lst_ic_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `lst_ic_lst_ID` mediumint(9) unsigned NOT NULL,
    `lst_ic_lst_Option_ID` mediumint(9) unsigned NOT NULL,
    `lst_ic_lst_url` varchar(50) default NULL,
    `lst_ic_only_person_View` BOOLEAN NOT NULL default 0,
    PRIMARY KEY(lst_ic_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


INSERT INTO `list_icon` (`lst_ic_id`, `lst_ic_lst_ID`, `lst_ic_lst_Option_ID`, `lst_ic_lst_url`, `lst_ic_only_person_View`) VALUES
(6, 1, 1, 'm-chapel-2.png',0),
(1, 1, 2, 'gm-green-dot.png',0),
(2, 1, 3, 'gm-orange-dot.png',0),
(3, 1, 4, 'gm-grn-pushpin.png',0),
(4, 1, 5, 'gm-blue-pushpin.png',0),
(5, 1, 6, 'gm-purple-pushpin.png',0),
(7, 1, 7, 'm-cross.png',1);



--
-- Dumping data for table `note_nte`
--


-- --------------------------------------------------------

--
-- Table structure for table `person2group2role_p2g2r`
--

CREATE TABLE `person2group2role_p2g2r` (
  `p2g2r_per_ID` mediumint(8) unsigned NOT NULL default '0',
  `p2g2r_grp_ID` mediumint(8) unsigned NOT NULL default '0',
  `p2g2r_rle_ID` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`p2g2r_per_ID`,`p2g2r_grp_ID`),
  KEY `p2g2r_per_ID` (`p2g2r_per_ID`,`p2g2r_grp_ID`,`p2g2r_rle_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `person_custom`
--

CREATE TABLE `person_custom` (
  `per_ID` mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (`per_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `person_custom`
--


-- --------------------------------------------------------

--
-- Table structure for table `person_custom_master`
--

CREATE TABLE `person_custom_master` (
  `custom_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `custom_Order` smallint(6) NOT NULL default '0',
  `custom_Field` varchar(5) NOT NULL default '',
  `custom_Name` varchar(40) NOT NULL default '',
  `custom_Special` mediumint(8) unsigned default NULL,
  `custom_Side` enum('left','right') NOT NULL default 'left',
  `custom_FieldSec` tinyint(4) NOT NULL,
  `custom_comment` text NULL default NULL COMMENT 'comment for GDPR',
  `custom_confirmation_datas` BOOLEAN NOT NULL default 1 COMMENT 'confirmations default datas',
  `type_ID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`custom_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `person_custom_master`
--


-- --------------------------------------------------------

--
-- Table structure for table `person_per`
--

CREATE TABLE `person_per` (
  `per_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `per_Title` varchar(50) default NULL,
  `per_FirstName` varchar(50) default NULL,
  `per_MiddleName` varchar(50) default NULL,
  `per_LastName` varchar(50) default NULL,
  `per_Suffix` varchar(50) default NULL,
  `per_Address1` varchar(50) default NULL,
  `per_Address2` varchar(50) default NULL,
  `per_City` varchar(50) default NULL,
  `per_State` varchar(50) default NULL,
  `per_Zip` varchar(50) default NULL,
  `per_Country` varchar(50) default NULL,
  `per_HomePhone` varchar(30) default NULL,
  `per_WorkPhone` varchar(30) default NULL,
  `per_CellPhone` varchar(30) default NULL,
  `per_Email` varchar(50) default NULL,
  `per_WorkEmail` varchar(50) default NULL,
  `per_BirthMonth` tinyint(3) unsigned NOT NULL default '0',
  `per_BirthDay` tinyint(3) unsigned NOT NULL default '0',
  `per_BirthYear` year(4) default NULL,
  `per_MembershipDate` date default NULL,
  `per_Gender` tinyint(1) unsigned NOT NULL default '0',
  `per_fmr_ID` tinyint(3) unsigned NOT NULL default '0',
  `per_cls_ID` tinyint(3) unsigned NOT NULL default '0',
  `per_fam_ID` smallint(5) unsigned NOT NULL default '0',
  `per_Envelope` smallint(5) unsigned default NULL,
  `per_DateLastEdited` datetime default NULL,
  `per_DateEntered` datetime NOT NULL,
  `per_EnteredBy` smallint(5)  NOT NULL default '0',
  `per_EditedBy` smallint(5) unsigned default '0',
  `per_FriendDate` date default NULL,
  `per_Flags` mediumint(9) NOT NULL default '0',
  `per_FacebookID` bigint(20) unsigned default NULL,
  `per_Twitter` varchar(50) default NULL,
  `per_LinkedIn` varchar(50) default NULL,
  `per_DateDeactivated` datetime default NULL,
  `per_SendNewsLetter` enum('FALSE','TRUE') NOT NULL default 'FALSE',
  `per_confirm_report` enum('No', 'Pending','Done') default 'No' COMMENT 'To confirm report of all users',
  PRIMARY KEY  (`per_ID`),
  KEY `per_ID` (`per_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=2 ;

--
-- Dumping data for table `person_per`
--

INSERT INTO `person_per` (`per_ID`, `per_Title`, `per_FirstName`, `per_MiddleName`, `per_LastName`, `per_Suffix`, `per_Address1`, `per_Address2`, `per_City`, `per_State`, `per_Zip`, `per_Country`, `per_HomePhone`, `per_WorkPhone`, `per_CellPhone`, `per_Email`, `per_WorkEmail`, `per_BirthMonth`, `per_BirthDay`, `per_BirthYear`, `per_MembershipDate`, `per_Gender`, `per_fmr_ID`, `per_cls_ID`, `per_fam_ID`, `per_Envelope`, `per_DateLastEdited`, `per_DateEntered`, `per_EnteredBy`, `per_EditedBy`, `per_FriendDate`, `per_Flags`) VALUES
  (1, NULL, 'EcclesiaCRM', NULL, 'Admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0000, NULL, 0, 0, 0, 0, NULL, NULL, '2004-08-25 18:00:00', 1, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pledge_plg`
--

CREATE TABLE `pledge_plg` (
  `plg_plgID` mediumint(9) NOT NULL auto_increment,
  `plg_FamID` mediumint(9) default NULL,
  `plg_FYID` mediumint(9) default NULL,
  `plg_date` date default NULL,
  `plg_amount` decimal(8,2) default NULL,
  `plg_schedule` enum('Weekly', 'Monthly','Quarterly','Once','Other') default NULL,
  `plg_method` enum('CREDITCARD','CHECK','CASH','BANKDRAFT','EGIVE') default NULL,
  `plg_comment` text,
  `plg_DateLastEdited` date NOT NULL default '2016-01-01',
  `plg_EditedBy` mediumint(9) NOT NULL default '0',
  `plg_PledgeOrPayment` enum('Pledge','Payment') NOT NULL default 'Pledge',
  `plg_fundID` tinyint(3) unsigned default NULL,
  `plg_depID` mediumint(9) unsigned default NULL,
  `plg_CheckNo` bigint(16) unsigned default NULL,
  `plg_Problem` tinyint(1) default NULL,
  `plg_scanString` text,
  `plg_aut_ID` mediumint(9) NOT NULL default '0',
  `plg_aut_Cleared` tinyint(1) NOT NULL default '0',
  `plg_aut_ResultID` mediumint(9) NOT NULL default '0',
  `plg_NonDeductible` decimal(8,2) NOT NULL,
  `plg_GroupKey` VARCHAR( 64 ) NOT NULL,
  `plg_MoveDonations_Comment` text NOT NULL default 'None',
  PRIMARY KEY  (`plg_plgID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `pledge_plg`
--


-- --------------------------------------------------------

--
-- Table structure for table `propertytype_prt`
--

CREATE TABLE `propertytype_prt` (
  `prt_ID` mediumint(9) NOT NULL auto_increment,
  `prt_Class` varchar(10) NOT NULL default '',
  `prt_Name` varchar(50) NOT NULL default '',
  `prt_Description` text NOT NULL,
  PRIMARY KEY  (`prt_ID`),
  UNIQUE KEY `prt_ID` (`prt_ID`),
  KEY `prt_ID_2` (`prt_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=4 ;

--
-- Dumping data for table `propertytype_prt`
--

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
  (1, 'p', 'Person', 'General Person Properties'),
  (2, 'f', 'Family', 'General Family Properties'),
  (3, 'g', 'Group', 'General Group Properties');

-- --------------------------------------------------------

--
-- Table structure for table `property_pro`
--

CREATE TABLE `property_pro` (
  `pro_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `pro_Class` varchar(10) NOT NULL default '',
  `pro_prt_ID` mediumint(8) unsigned NOT NULL default '0',
  `pro_Name` varchar(200) NOT NULL default '0',
  `pro_Description` text NOT NULL,
  `pro_Prompt` varchar(255) default NULL,
  `pro_Comment` text NOT NULL DEFAULT '' COMMENT 'comment for GDPR',
  PRIMARY KEY  (`pro_ID`),
  UNIQUE KEY `pro_ID` (`pro_ID`),
  KEY `pro_ID_2` (`pro_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=4 ;

--
-- Dumping data for table `property_pro`
--

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Disabled', 'has a disability.', 'What is the nature of the disability?',''),
  (2, 'f', 2, 'Single Parent', 'is a single-parent household.', '',''),
  (3, 'g', 3, 'Youth', 'is youth-oriented.', '','');

-- --------------------------------------------------------

--
-- Table structure for table `queryparameteroptions_qpo`
--

CREATE TABLE `queryparameteroptions_qpo` (
  `qpo_ID` smallint(5) unsigned NOT NULL auto_increment,
  `qpo_qrp_ID` mediumint(8) unsigned NOT NULL default '0',
  `qpo_Display` varchar(50) NOT NULL default '',
  `qpo_Value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`qpo_ID`),
  UNIQUE KEY `qpo_ID` (`qpo_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=28 ;

--
-- Dumping data for table `queryparameteroptions_qpo`
--

INSERT INTO `queryparameteroptions_qpo` (`qpo_ID`, `qpo_qrp_ID`, `qpo_Display`, `qpo_Value`) VALUES
  (1, 4, 'Male', '1'),
  (2, 4, 'Female', '2'),
  (3, 6, 'Male', '1'),
  (4, 6, 'Female', '2'),
  (5, 15, 'Name', 'CONCAT(COALESCE(`per_FirstName`,''''),COALESCE(`per_MiddleName`,''''),COALESCE(`per_LastName`,''''))'),
  (6, 15, 'Zip Code', 'fam_Zip'),
  (7, 15, 'State', 'fam_State'),
  (8, 15, 'City', 'fam_City'),
  (9, 15, 'Home Phone', 'per_HomePhone'),
  (10, 27, '2012/2013', '17'),
  (11, 27, '2013/2014', '18'),
  (12, 27, '2014/2015', '19'),
  (13, 27, '2015/2016', '20'),
  (14, 28, '2012/2013', '17'),
  (15, 28, '2013/2014', '18'),
  (16, 28, '2014/2015', '19'),
  (17, 28, '2015/2016', '20'),
  (18, 30, '2012/2013', '17'),
  (19, 30, '2013/2014', '18'),
  (20, 30, '2014/2015', '19'),
  (21, 30, '2015/2016', '20'),
  (22, 31, '2012/2013', '17'),
  (23, 31, '2013/2014', '18'),
  (24, 31, '2014/2015', '19'),
  (25, 31, '2015/2016', '20'),
  (26, 15, 'Email', 'per_Email'),
  (27, 15, 'WorkEmail', 'per_WorkEmail'),
  (28, 32, '2012/2013', '17'),
  (29, 32, '2013/2014', '18'),
  (30, 32, '2014/2015', '19'),
  (31, 32, '2015/2016', '20'),
  (32, 33, 'Member', '1'),
  (33, 33, 'Regular Attender', '2'),
  (34, 33, 'Guest', '3'),
  (35, 33, 'Non-Attender', '4'),
  (36, 33, 'Non-Attender (staff)', '5');

-- --------------------------------------------------------

--
-- Table structure for table `queryparameters_qrp`
--

CREATE TABLE `queryparameters_qrp` (
  `qrp_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `qrp_qry_ID` mediumint(8) unsigned NOT NULL default '0',
  `qrp_Type` tinyint(3) unsigned NOT NULL default '0',
  `qrp_OptionSQL` text,
  `qrp_Name` varchar(25) default NULL,
  `qrp_Description` text,
  `qrp_Alias` varchar(25) default NULL,
  `qrp_Default` varchar(25) default NULL,
  `qrp_Required` tinyint(3) unsigned NOT NULL default '0',
  `qrp_InputBoxSize` tinyint(3) unsigned NOT NULL default '0',
  `qrp_Validation` varchar(5) NOT NULL default '',
  `qrp_NumericMax` int(11) default NULL,
  `qrp_NumericMin` int(11) default NULL,
  `qrp_AlphaMinLength` int(11) default NULL,
  `qrp_AlphaMaxLength` int(11) default NULL,
  PRIMARY KEY  (`qrp_ID`),
  UNIQUE KEY `qrp_ID` (`qrp_ID`),
  KEY `qrp_ID_2` (`qrp_ID`),
  KEY `qrp_qry_ID` (`qrp_qry_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=102 ;

--
-- Dumping data for table `queryparameters_qrp`
--

INSERT INTO `queryparameters_qrp` (`qrp_ID`, `qrp_qry_ID`, `qrp_Type`, `qrp_OptionSQL`, `qrp_Name`, `qrp_Description`, `qrp_Alias`, `qrp_Default`, `qrp_Required`, `qrp_InputBoxSize`, `qrp_Validation`, `qrp_NumericMax`, `qrp_NumericMin`, `qrp_AlphaMinLength`, `qrp_AlphaMaxLength`) VALUES
  (1, 4, 0, NULL, 'Minimum Age', 'The minimum age for which you want records returned.', 'min', '0', 0, 5, 'n', 120, 0, NULL, NULL),
  (2, 4, 0, NULL, 'Maximum Age', 'The maximum age for which you want records returned.', 'max', '120', 1, 5, 'n', 120, 0, NULL, NULL),
  (4, 6, 1, '', 'Gender', 'The desired gender to search the database for.', 'gender', '1', 1, 0, '', 0, 0, 0, 0),
  (5, 7, 2, 'SELECT lst_OptionID as Value, lst_OptionName as Display FROM list_lst WHERE lst_ID=2 ORDER BY lst_OptionSequence', 'Family Role', 'Select the desired family role.', 'role', '1', 0, 0, '', 0, 0, 0, 0),
  (6, 7, 1, '', 'Gender', 'The gender for which you would like records returned.', 'gender', '1', 1, 0, '', 0, 0, 0, 0),
  (8, 9, 2, 'SELECT pro_ID AS Value, pro_Name as Display \r\nFROM property_pro\r\nWHERE pro_Class= ''p'' \r\nORDER BY pro_Name ', 'Property', 'The property for which you would like person records returned.', 'PropertyID', '0', 1, 0, '', 0, 0, 0, 0),
  (9, 10, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don ORDER BY don_date ASC', 'Beginning Date', 'Please select the beginning date to calculate total contributions for each member (i.e. YYYY-MM-DD). NOTE: You can only choose dates that conatain donations.', 'startdate', '1', 1, 0, '0', 0, 0, 0, 0),
  (10, 10, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don\r\nORDER BY don_date DESC', 'Ending Date', 'Please enter the last date to calculate total contributions for each member (i.e. YYYY-MM-DD).', 'enddate', '1', 1, 0, '', 0, 0, 0, 0),
  (14, 15, 0, '', 'Search', 'Enter any part of the following: Name, City, State, Zip, Home Phone, Email, or Work Email.', 'searchstring', '', 1, 0, '', 0, 0, 0, 0),
  (15, 15, 1, '', 'Field', 'Select field to search for.', 'searchwhat', '1', 1, 0, '', 0, 0, 0, 0),
  (16, 11, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don ORDER BY don_date ASC', 'Beginning Date', 'Please select the beginning date to calculate total contributions for each member (i.e. YYYY-MM-DD). NOTE: You can only choose dates that conatain donations.', 'startdate', '1', 1, 0, '0', 0, 0, 0, 0),
  (17, 11, 2, 'SELECT distinct don_date as Value, don_date as Display FROM donations_don\r\nORDER BY don_date DESC', 'Ending Date', 'Please enter the last date to calculate total contributions for each member (i.e. YYYY-MM-DD).', 'enddate', '1', 1, 0, '', 0, 0, 0, 0),
  (18, 18, 0, '', 'Month', 'The birthday month for which you would like records returned.', 'birthmonth', '1', 1, 0, '', 12, 1, 1, 2),
  (19, 19, 2, 'SELECT grp_ID AS Value, grp_Name AS Display FROM group_grp ORDER BY grp_Type', 'Class', 'The sunday school class for which you would like records returned.', 'group', '1', 1, 0, '', 12, 1, 1, 2),
  (20, 20, 2, 'SELECT grp_ID AS Value, grp_Name AS Display FROM group_grp ORDER BY grp_Type', 'Class', 'The sunday school class for which you would like records returned.', 'group', '1', 1, 0, '', 12, 1, 1, 2),
  (21, 21, 2, 'SELECT grp_ID AS Value, grp_Name AS Display FROM group_grp ORDER BY grp_Type', 'Registered students', 'Group of registered students', 'group', '1', 1, 0, '', 12, 1, 1, 2),
  (22, 22, 0, '', 'Month', 'The membership anniversary month for which you would like records returned.', 'membermonth', '1', 1, 0, '', 12, 1, 1, 2),
  (25, 25, 2, 'SELECT vol_ID AS Value, vol_Name AS Display FROM volunteeropportunity_vol ORDER BY vol_Name', 'Volunteer opportunities', 'Choose a volunteer opportunity', 'volopp', '1', 1, 0, '', 12, 1, 1, 2),
  (26, 26, 0, '', 'Months', 'Number of months since becoming a friend', 'friendmonths', '1', 1, 0, '', 24, 1, 1, 2),
  (27, 28, 1, '', 'First Fiscal Year', 'First fiscal year for comparison', 'fyid1', '9', 1, 0, '', 12, 9, 0, 0),
  (28, 28, 1, '', 'Second Fiscal Year', 'Second fiscal year for comparison', 'fyid2', '9', 1, 0, '', 12, 9, 0, 0),
  (30, 30, 1, '', 'First Fiscal Year', 'Pledged this year', 'fyid1', '9', 1, 0, '', 12, 9, 0, 0),
  (31, 30, 1, '', 'Second Fiscal Year', 'but not this year', 'fyid2', '9', 1, 0, '', 12, 9, 0, 0),
  (32, 32, 1, '', 'Fiscal Year', 'Fiscal Year.', 'fyid', '9', 1, 0, '', 12, 9, 0, 0),
  (33, 18, 1, '', 'Classification', 'Member, Regular Attender, etc.', 'percls', '1', 1, 0, '', 12, 1, 1, 2),
  (34, 33, 0, NULL, 'Year', 'Get all persons who were born before the Year you mentioned.', 'the_year', '2100', 0, 5, 'n', 2100, 0, NULL, NULL),
  (100, 100, 2, 'SELECT vol_ID AS Value, vol_Name AS Display FROM volunteeropportunity_vol ORDER BY vol_Name', 'Volunteer opportunities', 'First volunteer opportunity choice', 'volopp1', '1', 1, 0, '', 12, 1, 1, 2),
  (101, 100, 2, 'SELECT vol_ID AS Value, vol_Name AS Display FROM volunteeropportunity_vol ORDER BY vol_Name', 'Volunteer opportunities', 'Second volunteer opportunity choice', 'volopp2', '1', 1, 0, '', 12, 1, 1, 2),
  (200, 200, 2, 'SELECT custom_field as Value, custom_Name as Display FROM person_custom_master', 'Custom field', 'Choose customer person field', 'custom', '1', 0, 0, '', 0, 0, 0, 0),
  (201, 200, 0, '', 'Field value', 'Match custom field to this value', 'value', '1', 0, 0, '', 0, 0, 0, 0),
  (202, 201, 2, 'SELECT custom_field as Value, custom_Name as Display FROM person_custom_master', 'Custom field', 'Choose customer person field', 'custom', '1', 0, 0, '', 0, 0, 0, 0),
  (203, 201, 0, '', 'Field value', 'Match custom field to this value', 'value', '1', 0, 0, '', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `query_type`
--

CREATE TABLE `query_type` (
  `qry_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `qry_type_Category` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`qry_type_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `query_type` (`qry_type_id`, `qry_type_Category`) VALUES
(1, 'Person'),
(2, 'Family'),
(3, 'Events'),
(4, 'Pledges and payments'),
(5, 'Users'),
(6, 'Volunteers'),
(7, 'Advanced Search'),
(8, 'Confirm Datas'),
(100, 'Not assigned');

-- --------------------------------------------------------

--
-- Table structure for table `query_qry`
--

CREATE TABLE `query_qry` (
  `qry_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `qry_SQL` text NOT NULL,
  `qry_Name` varchar(255) NOT NULL default '',
  `qry_Description` text NOT NULL,
  `qry_Count` tinyint(1) unsigned NOT NULL default '0',
  `qry_Type_ID` int(11) DEFAULT 0,
  PRIMARY KEY  (`qry_ID`),
  UNIQUE KEY `qry_ID` (`qry_ID`),
  KEY `qry_ID_2` (`qry_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci  AUTO_INCREMENT=101 ;

--
-- Dumping data for table `query_qry`
--

INSERT INTO `query_qry` (`qry_ID`, `qry_SQL`, `qry_Name`, `qry_Description`, `qry_Count`, `qry_Type_ID`) VALUES
  (1, 'SELECT CONCAT(''<a href=v2/people/family/view/'',fam_ID,''>'',fam_Name,''</a>'') AS ''Family Name''   FROM family_fam Where fam_WorkPhone != ""', 'Family With Work Phone', 'Returns each family with a work phone', 0, 2),
  (3, 'SELECT CONCAT(''<a href=v2/people/family/view/'',fam_ID,''>'',fam_Name,''</a>'') AS ''Family Name'', COUNT(*) AS ''No.''\nFROM person_per\nINNER JOIN family_fam\nON fam_ID = per_fam_ID\nGROUP BY per_fam_ID\nORDER BY ''No.'' DESC', 'Family Member Count', 'Returns each family and the total number of people assigned to them.', 0, 2),
  (4, 'SELECT per_ID as AddToCart,CONCAT(''<a href=v2/people/person/view/'',per_ID,''>'',per_FirstName,'' '',per_LastName,''</a>'') AS Name, CONCAT(per_BirthMonth,''/'',per_BirthDay,''/'',per_BirthYear) AS ''Birth Date'', DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(CONCAT(per_BirthYear,''-'',per_BirthMonth,''-'',per_BirthDay))),''%Y'')+0 AS ''Age'', per_DateDeactivated as "GDPR" FROM person_per WHERE DATE_ADD(CONCAT(per_BirthYear,''-'',per_BirthMonth,''-'',per_BirthDay),INTERVAL ~min~ YEAR) <= CURDATE() AND DATE_ADD(CONCAT(per_BirthYear,''-'',per_BirthMonth,''-'',per_BirthDay),INTERVAL (~max~ + 1) YEAR) >= CURDATE() ORDER by Age , Name DESC', 'Person by Age', 'Returns any person records with ages between two given ages.', 1, 1),
  (6, 'SELECT COUNT(per_ID) AS Total FROM person_per WHERE per_Gender = ~gender~', 'Total By Gender', 'Total of records matching a given gender.', 0, 8),
  (7, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,'' '',per_LastName) AS Name, per_DateDeactivated as ''GDPR'' FROM person_per WHERE per_fmr_ID = ~role~ AND per_Gender = ~gender~', 'Person by Role and Gender', 'Selects person records with the family role and gender specified.', 1, 1),
  (9, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,'' '',per_LastName) AS Name, CONCAT(r2p_Value,'' '') AS Value, per_DateDeactivated as ''GDPR'' FROM person_per,record2property_r2p WHERE per_ID = r2p_record_ID AND r2p_pro_ID = ~PropertyID~ ORDER BY per_LastName', 'Person by Property', 'Returns person records which are assigned the given property.', 1, 1),
  (15, 'SELECT per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',per_ID,''>'',COALESCE(`per_FirstName`,''''),'' '',COALESCE(`per_MiddleName`,''''),'' '',COALESCE(`per_LastName`,''''),''</a>'') AS Name, fam_City as City, fam_State as State, fam_Zip as ZIP, per_HomePhone as HomePhone, per_Email as Email, per_WorkEmail as WorkEmail,per_DateDeactivated as ''GDPR'' FROM person_per RIGHT JOIN family_fam ON family_fam.fam_id = person_per.per_fam_id WHERE ~searchwhat~ LIKE ''%~searchstring~%''', 'Advanced Search', 'Search by any part of Name, City, State, Zip, Home Phone, Email, or Work Email.', 1, 7),
  (18, 'SELECT per_ID as AddToCart, per_BirthDay as Day, CONCAT(per_FirstName,'' '',per_LastName) AS Name, per_DateDeactivated as ''GDPR''  FROM person_per WHERE per_cls_ID=~percls~ AND per_BirthMonth=~birthmonth~ ORDER BY per_BirthDay', 'Birthdays', 'People with birthdays in a particular month', 0, 3),
  (21, 'SELECT per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',per_ID,''>'',per_FirstName,'' '',per_LastName,''</a>'') AS Name,per_DateDeactivated as ''GDPR'' FROM person_per LEFT JOIN person2group2role_p2g2r ON per_id = p2g2r_per_ID WHERE p2g2r_grp_ID=~group~ ORDER BY per_LastName', 'Registered students', 'Find Registered students', 1, 5),
  (22, 'SELECT per_ID as AddToCart, DAYOFMONTH(per_MembershipDate) as Day, per_MembershipDate AS DATE, CONCAT(per_FirstName,'' '',per_LastName) AS Name, per_DateDeactivated as ''GDPR'' FROM person_per WHERE per_cls_ID=1 AND MONTH(per_MembershipDate)=~membermonth~ ORDER BY per_MembershipDate', 'Membership anniversaries', 'Members who joined in a particular month', 0, 3),
  (23, 'SELECT usr_per_ID as AddToCart, CONCAT(a.per_FirstName,'' '',a.per_LastName) AS Name, a.per_DateDeactivated as ''GDPR'' FROM user_usr LEFT JOIN person_per a ON per_ID=usr_per_ID ORDER BY per_LastName', 'Select database users', 'People who are registered as database users', 0, 5),
  (24, 'SELECT per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',per_ID,''>'',per_FirstName,'' '',per_LastName,''</a>'') AS Name, per_DateDeactivated as ''GDPR'' FROM person_per WHERE per_cls_id =1', 'Select all members', 'People who are members', 0, 1),
  (25, 'SELECT per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',per_ID,''>'',per_FirstName,'' '',per_LastName,''</a>'') AS Name, per_DateDeactivated as ''GDPR'' FROM person_per LEFT JOIN person2volunteeropp_p2vo ON per_id = p2vo_per_ID WHERE p2vo_vol_ID = ~volopp~ ORDER BY per_LastName', 'Volunteers', 'Find volunteers for a particular opportunity', 1, 6),
  (26, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,'' '',per_LastName) AS Name, per_DateDeactivated as ''GDPR'' FROM person_per WHERE DATE_SUB(NOW(),INTERVAL ~friendmonths~ MONTH)<per_FriendDate ORDER BY per_MembershipDate', 'Recent friends', 'Friends who signed up in previous months', 0, 1),
  (27, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,'' '',per_LastName) AS Name, per_DateDeactivated as ''GDPR''  FROM person_per inner join family_fam on per_fam_ID=fam_ID where per_fmr_ID<>3 AND fam_OkToCanvass="TRUE" ORDER BY fam_Zip', 'Families to Canvass', 'People in families that are ok to canvass.', 0, 2),
  (28, 'SELECT fam_Name, a.plg_amount as PlgFY1, b.plg_amount as PlgFY2 from family_fam left join pledge_plg a on a.plg_famID = fam_ID and a.plg_FYID=~fyid1~ and a.plg_PledgeOrPayment=''Pledge'' left join pledge_plg b on b.plg_famID = fam_ID and b.plg_FYID=~fyid2~ and b.plg_PledgeOrPayment=''Pledge'' order by fam_Name', 'Pledge comparison', 'Compare pledges between two fiscal years', 1, 4),
  (30, 'SELECT per_ID as AddToCart, CONCAT(per_FirstName,'' '',per_LastName) AS Name, fam_address1, fam_city, fam_state, fam_zip, per_DateDeactivated as ''GDPR'' FROM person_per join family_fam on per_fam_id=fam_id where per_fmr_id<>3 and per_fam_id in (select fam_id from family_fam inner join pledge_plg a on a.plg_famID=fam_ID and a.plg_FYID=~fyid1~ and a.plg_amount>0) and per_fam_id not in (select fam_id from family_fam inner join pledge_plg b on b.plg_famID=fam_ID and b.plg_FYID=~fyid2~ and b.plg_amount>0)', 'Missing pledges', 'Find people who pledged one year but not another', 1, 4),
  (31, 'select per_ID as AddToCart, per_FirstName, per_LastName, per_email, per_DateDeactivated as ''GDPR''  FROM person_per, autopayment_aut where aut_famID=per_fam_ID and aut_CreditCard!="" and per_email!="" and (per_fmr_ID=1 or per_fmr_ID=2 or per_cls_ID=1)', 'Credit Card People', 'People who are configured to pay by credit card.', 0, 4),
  (32, 'SELECT fam_Name, fam_Envelope, b.fun_Name as Fund_Name, a.plg_amount as Pledge from family_fam left join pledge_plg a on a.plg_famID = fam_ID and a.plg_FYID=~fyid~ and a.plg_PledgeOrPayment=''Pledge'' and a.plg_amount>0 join donationfund_fun b on b.fun_ID = a.plg_fundID order by fam_Name, a.plg_fundID', 'Family Pledge by Fiscal Year', 'Pledge summary by family name for each fund for the selected fiscal year', 1, 4),
  (33, 'SELECT per_ID as AddToCart, per_LastName, per_FirstName, per_DateDeactivated as ''GDPR''  FROM `person_per` where per_BirthYear<~the_year~ AND per_cls_ID IN (1,2) AND per_fam_ID<>3 AND `per_ID` NOT IN (SELECT p2g2r_per_ID FROM `person2group2role_p2g2r`) order by per_LastName ASC, per_FirstName ASC', 'Persons not assigned to a group', 'Returns all the persons not assigned to a group.', 1, 1),
  (34, 'SELECT per_ID as AddToCart,per_FirstName, per_LastName, grp_Name, per_DateDeactivated as ''GDPR''  FROM `person2group2role_p2g2r`, `person_per`, group_grp WHERE per_cls_ID IN (1,2) AND per_fam_ID<>3 AND p2g2r_per_ID=per_ID and grp_ID=p2g2r_grp_ID order by per_FirstName ASC, per_LastName ASC, grp_Name ASC', 'Person assigned to a group', 'Returns all persons assigned to a group.', 1, 1),
  (35,'SELECT a.per_ID as AddToCart, per_Email as Email, CONCAT(\'<a href=v2/people/person/view/\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\' FROM `person_per` AS a WHERE per_confirm_report=\'pending\';','Awaiting confirmation of persons','Find person who are in pending confirmation process',1,8),
  (36,'SELECT a.per_ID as AddToCart, per_Email as Email, CONCAT(\'<a href=v2/people/person/view/\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\' FROM person_per AS a WHERE `per_fam_ID` IN (SELECT F.fam_ID  FROM `family_fam` AS F WHERE F.fam_confirm_report=\'pending\');','Awaiting confirmation of families','Find families who are in pending confirmation process',1,8),
  (37,'SELECT a.per_ID as AddToCart, per_Email as Email, CONCAT(\'<a href=v2/people/person/view/\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\' FROM `person_per` AS a WHERE per_confirm_report=\'Done\';','Done confirmation of persons','Look for person who have confirmed',1,8),
  (38,'SELECT a.per_ID as AddToCart, per_Email as Email, CONCAT(\'<a href=v2/people/person/view/\',a.per_ID,\'>\',a.per_FirstName,\' \',a.per_LastName,\'</a>\') AS Name, a.per_DateDeactivated as \'GDPR\' FROM person_per AS a WHERE `per_fam_ID` IN (SELECT F.fam_ID  FROM `family_fam` AS F WHERE F.fam_confirm_report=\'Done\');','Done confirmation of families','Look for families who have confirmed',1,8),
  (100, 'SELECT a.per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',a.per_ID,''>'',a.per_FirstName,'' '',a.per_LastName,''</a>'') AS Name, a.per_DateDeactivated as ''GDPR''  FROM person_per AS a LEFT JOIN person2volunteeropp_p2vo p2v1 ON (a.per_id = p2v1.p2vo_per_ID AND p2v1.p2vo_vol_ID = ~volopp1~) LEFT JOIN person2volunteeropp_p2vo p2v2 ON (a.per_id = p2v2.p2vo_per_ID AND p2v2.p2vo_vol_ID = ~volopp2~) WHERE p2v1.p2vo_per_ID=p2v2.p2vo_per_ID ORDER BY per_LastName', 'Volunteers', 'Find volunteers for who match two specific opportunity codes', 1, 6),
  (200, 'SELECT a.per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',a.per_ID,''>'',a.per_FirstName,'' '',a.per_LastName,''</a>'') AS Name, a.per_DateDeactivated as ''GDPR''  FROM person_per AS a LEFT JOIN person_custom pc ON a.per_id = pc.per_ID WHERE pc.~custom~ LIKE ''%~value~%'' ORDER BY per_LastName', 'CustomSearch', 'Find people with a custom field value', 1, 7),
  (201, 'SELECT a.per_ID as AddToCart, CONCAT(''<a href=v2/people/person/view/'',a.per_ID,''>'',a.per_FirstName,'' '',a.per_LastName,''</a>'') AS Name, a.per_DateDeactivated as ''GDPR''  FROM person_per AS a LEFT JOIN person_custom pc ON a.per_id = pc.per_ID WHERE pc.~custom~ IS ~value~ ORDER BY per_LastName', 'CustomSearch is NULL', 'Find people with a custom field value is NULL', 1, 7);


-- --------------------------------------------------------

--
-- Table structure for table `record2property_r2p`
--

CREATE TABLE `record2property_r2p` (
  `r2p_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `r2p_pro_ID` mediumint(8) unsigned NOT NULL default '0',
  `r2p_record_ID` mediumint(8) unsigned NOT NULL default '0',
  `r2p_Value` text NOT NULL,
   PRIMARY KEY  (`r2p_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `record2property_r2p`
--


-- --------------------------------------------------------

--
-- Table structure for table `result_res`
--

CREATE TABLE `result_res` (
  `res_ID` mediumint(9) NOT NULL auto_increment,
  `res_echotype1` text NOT NULL,
  `res_echotype2` text NOT NULL,
  `res_echotype3` text NOT NULL,
  `res_authorization` text NOT NULL,
  `res_order_number` text NOT NULL,
  `res_reference` text NOT NULL,
  `res_status` text NOT NULL,
  `res_avs_result` text NOT NULL,
  `res_security_result` text NOT NULL,
  `res_mac` text NOT NULL,
  `res_decline_code` text NOT NULL,
  `res_tran_date` text NOT NULL,
  `res_merchant_name` text NOT NULL,
  `res_version` text NOT NULL,
  `res_EchoServer` text NOT NULL,
  PRIMARY KEY  (`res_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `result_res`
--

-- --------------------------------------------------------

--
-- Table structure for table `userconfig_choices_ucfg_ch`
--

CREATE TABLE `userconfig_choices_ucfg_ch` (
  `ucfg_ch_id` mediumint(9) unsigned NOT NULL,
  `ucfg_name` text,
  `ucfg_choices` varchar(200) NOT NULL,
  PRIMARY KEY  (`ucfg_ch_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


INSERT INTO `userconfig_choices_ucfg_ch` (`ucfg_ch_id`,`ucfg_name`,`ucfg_choices`) VALUES
(0,'Maps','GoogleMaps,AppleMaps,BingMaps'),
(1,'StyleFontSize', 'Small,Large' ),
(2,'StyleSideBarType', 'dark,light'),
(3,'StyleSideBarColor','blue,secondary,green,cyan,yellow,red,fuchsia,blue,yellow,indigo,navy,purple,pink,maroon,orange,lime,teal,olive,black,gray-dark,gray,light' ),
(4,'StyleNavBarColor', 'blue,secondary,green,cyan,yellow,red,fuchsia,blue,yellow,indigo,navy,purple,pink,maroon,orange,lime,teal,olive,black,gray-dark,gray,light' ),
(5,'StyleBrandLinkColor', 'blue,secondary,green,cyan,yellow,red,fuchsia,blue,yellow,indigo,navy,purple,pink,maroon,orange,lime,teal,olive,black,gray-dark,gray,light' ),
(6,'DarkMode', 'light,dark,automatic' );


-- --------------------------------------------------------

--
-- Table structure for table `userconfig_ucfg`
--

CREATE TABLE `userconfig_ucfg` (
  `ucfg_per_id` mediumint(9) unsigned NOT NULL,
  `ucfg_id` int(11) NOT NULL default '0',
  `ucfg_name` varchar(50) NOT NULL default '',
  `ucfg_value` text,
  `ucfg_type` enum('text','number','date','boolean','textarea','choice') NOT NULL default 'text',
  `ucfg_choices_id` mediumint(9) unsigned NULL,
  `ucfg_tooltip` text NOT NULL,
  `ucfg_permission` enum('FALSE','TRUE') NOT NULL default 'FALSE',
  `ucfg_cat` varchar(20) NOT NULL,
  PRIMARY KEY  (`ucfg_per_id`,`ucfg_id`),
  CONSTRAINT fk_ucfg_choices_id FOREIGN KEY (ucfg_choices_id) REFERENCES userconfig_choices_ucfg_ch(ucfg_ch_id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `userconfig_ucfg`
--

INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
  (0, 0, 'bEmailMailto', '1', 'boolean', 'User permission to send email via mailto: links', 'TRUE', ''),
  (0, 1, 'sMailtoDelimiter', ',', 'text', 'Delimiter to separate emails in mailto: links', 'TRUE', ''),
  (0, 8, 'bShowTooltip',1,'boolean','Allow to see ballon Help','TRUE',''),
  (0, 9, 'sCSVExportDelemiter', ',', 'text', 'To export to another For european CharSet use ;', 'TRUE', ''),
  (0, 10, 'sCSVExportCharset', 'UTF-8', 'text', 'Default is UTF-8, For european CharSet use Windows-1252 for example for French language.', 'TRUE', ''),
  (1, 0, 'bEmailMailto', '1', 'boolean', 'User permission to send email via mailto: links', 'TRUE', ''),
  (1, 1, 'sMailtoDelimiter', ',', 'text', 'User permission to send email via mailto: links', 'TRUE', ''),
  (1, 8, 'bShowTooltip',1,'boolean','Allow to see ballon Help','TRUE',''),
  (1, 9, 'sCSVExportDelemiter', ',', 'text', 'To export to another For european CharSet use ;', 'TRUE', ''),
  (1, 10, 'sCSVExportCharset', 'UTF-8', 'text', 'Default is UTF-8, For european CharSet use Windows-1252 for example for French language.', 'TRUE', '');

-- the map choices
INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_choices_id`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
  (0, 12, 'sMapExternalProvider', 'GoogleMaps', 'choice', '0', 'Map providers for external view', 'TRUE', ''),
  (1, 12, 'sMapExternalProvider', 'GoogleMaps', 'choice', '0', 'Map providers for external view', 'TRUE', '');


--  The navbar styles
INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_choices_id`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
  (0, 13, 'bSidebarExpandOnHover', '1', 'boolean', NULL, 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
  (1, 13, 'bSidebarExpandOnHover', '1', 'boolean', NULL, 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
  (0, 14, 'bSidebarCollapse', '1', 'boolean', NULL, 'The sidebar is collapse by default', 'TRUE', ''),
  (1, 14, 'bSidebarCollapse', '1', 'boolean', NULL, 'The sidebar is collapse by default', 'TRUE', ''),
  (0, 15, 'sStyleFontSize', 'Small', 'choice', '1', 'AdminLTE 3.0 font style', 'TRUE', ''),
  (1, 15, 'sStyleFontSize', 'Small', 'choice', '1','AdminLTE 3.0 font style', 'TRUE', ''),
  (0, 16, 'sStyleSideBar', 'dark', 'choice', '2', 'AdminLTE 3.0 Theme sideBar style', 'TRUE', ''),
  (1, 16, 'sStyleSideBar', 'dark', 'choice', '2','AdminLTE 3.0 Theme sideBar style', 'TRUE', ''),
  (0, 17, 'sStyleSideBarColor', 'blue', 'choice', '3', 'AdminLTE 3.0 sideBar color style', 'TRUE', ''),
  (1, 17, 'sStyleSideBarColor', 'blue', 'choice', '3','AdminLTE 3.0 sideBar color style', 'TRUE', ''),
  (0, 18, 'sStyleNavBarColor', 'gray', 'choice', '4', 'AdminLTE 3.0 navbar color style', 'TRUE', ''),
  (1, 18, 'sStyleNavBarColor', 'gray', 'choice', '4','AdminLTE 3.0 navbar color style', 'TRUE', ''),
  (0, 19, 'sStyleBrandLinkColor', 'gray', 'choice', '5', 'AdminLTE 3.0 brand link color style', 'TRUE', ''),
  (1, 19, 'sStyleBrandLinkColor', 'gray', 'choice', '5','AdminLTE 3.0 brand link color style', 'TRUE', ''),
  (0, 20, 'sDarkMode', 'automatic', 'choice', '6', 'AdminLTE 3.1 Dark Mode', 'TRUE', ''),
  (1, 20, 'sDarkMode', 'automatic', 'choice', '6','AdminLTE 3.1 Dark Mode color style', 'TRUE', '');



-- --------------------------------------------------------

--
-- Table structure for table `userrole_usrrol`
--

CREATE TABLE userrole_usrrol (
    `usrrol_id` mediumint(11) unsigned  NOT NULL AUTO_INCREMENT,
    `usrrol_name` VARCHAR(256) NOT NULL,
    `usrrol_global` TEXT COLLATE utf8_unicode_ci,
    `usrrol_permissions` TEXT COLLATE utf8_unicode_ci,
    `usrrol_value` TEXT COLLATE utf8_unicode_ci,
    PRIMARY KEY(usrrol_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


-- the new settings
INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;QueryMenu:1;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(2, 'User Min',  'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;EDrive:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;CanSendEmail:0;ExportCSV:0;CreateDirectory:0;ExportSundaySchoolPDF:0;ExportSundaySchoolCSV:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(3, 'User Max but not Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(4, 'User Max but not DPO and not Pastoral Care',  'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(5, 'User DPO', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray');


-- --------------------------------------------------------

--
-- Table structure for table `user_usr`
--

CREATE TABLE `user_usr` (
  `usr_per_ID` mediumint(9) unsigned NOT NULL default '0',
  `usr_role_id` mediumint(11) unsigned NULL,
  `usr_Password` varchar(500) NOT NULL default '',
  `usr_CurrentPath` varchar(1500) NOT NULL default '/',
  `usr_NeedPasswordChange` tinyint(1) unsigned NOT NULL default '1',
  `usr_HomeDir` varchar(500) default NULL,
  `usr_LastLogin` datetime NOT NULL default '2000-01-01 00:00:00',
  `usr_LoginCount` smallint(5) unsigned NOT NULL default '0',
  `usr_is_logged_in` tinyint(1) unsigned NOT NULL default '0',
  `usr_tLastOperation_date` datetime NOT NULL default '2000-01-01 00:00:00',
  `usr_FailedLogins` tinyint(3) unsigned NOT NULL default '0',
  `usr_AddRecords` tinyint(1) unsigned NOT NULL default '0',
  `usr_EditRecords` tinyint(1) unsigned NOT NULL default '0',
  `usr_DeleteRecords` tinyint(1) unsigned NOT NULL default '0',
  `usr_ManageCalendarResources` tinyint(1) unsigned NOT NULL default '0',
  `usr_HtmlSourceEditor` tinyint(1) unsigned NOT NULL default '0',
  `usr_MenuOptions` tinyint(1) unsigned NOT NULL default '0',
  `usr_ManageGroups` tinyint(1) unsigned NOT NULL default '0',
  `usr_Finance` tinyint(1) unsigned NOT NULL default '0',
  `usr_Notes` tinyint(1) unsigned NOT NULL default '0',
  `usr_Admin` tinyint(1) unsigned NOT NULL default '0',
  `usr_Meeting` tinyint(1) unsigned NOT NULL default '0',
  `usr_PastoralCare` tinyint(1) DEFAULT '0',
  `usr_GDRP_DPO` tinyint(1) DEFAULT '0',
  `usr_MailChimp` tinyint(1) DEFAULT '0',
  `usr_MainDashboard` tinyint(1) DEFAULT '0',
  `usr_SeePrivacyData` tinyint(1) DEFAULT '0',
  `usr_SearchLimit` tinyint(4) default '10',
  `usr_showPledges` tinyint(1) NOT NULL default '0',
  `usr_showPayments` tinyint(1) NOT NULL default '0',
  `usr_showMenuQuery` tinyint(1) NOT NULL default '0',
  `usr_CanSendEmail` tinyint(1) NOT NULL default '0',
  `usr_ExportSundaySchoolCSV` tinyint(1) NOT NULL default '0',
  `usr_ExportSundaySchoolPDF` tinyint(1) NOT NULL default '0',
  `usr_CreateDirectory` tinyint(1) NOT NULL default '0',
  `usr_ExportCSV` tinyint(1) NOT NULL default '0',
  `usr_showSince` date NOT NULL default '2018-01-01',
  `usr_showTo` date NOT NULL default '2019-01-01',
  `usr_defaultFY` mediumint(9) NOT NULL default '10',
  `usr_currentDeposit` mediumint(9) NOT NULL default '0',
  `usr_UserName` varchar(50) default NULL,
  `usr_webDavKey` VARCHAR(255) default NULL,
  `usr_webDavPublicKey` VARCHAR(255) default NULL,
  `usr_IsDeactivated` tinyint(1) DEFAULT '0',
  `usr_EditSelf` tinyint(1) unsigned NOT NULL default '0',
  `usr_CalStart` date default NULL,
  `usr_CalEnd` date default NULL,
  `usr_CalNoSchool1` date default NULL,
  `usr_CalNoSchool2` date default NULL,
  `usr_CalNoSchool3` date default NULL,
  `usr_CalNoSchool4` date default NULL,
  `usr_CalNoSchool5` date default NULL,
  `usr_CalNoSchool6` date default NULL,
  `usr_CalNoSchool7` date default NULL,
  `usr_CalNoSchool8` date default NULL,
  `usr_SearchFamily` tinyint(3) default NULL,
  `usr_Canvasser` tinyint(1) NOT NULL default '0',
  `usr_ShowCart` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `usr_ShowMap` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `usr_EDrive` tinyint(1) NOT NULL default '0',
  `usr_TwoFaSecret` VARCHAR(255) NULL,
  `usr_TwoFaSecretConfirm` BOOLEAN NOT NULL default 0,
  `usr_TwoFaRescuePasswords` VARCHAR(255) NULL,
  `usr_TwoFaRescueDateTime` datetime NOT NULL default '2000-01-01 00:00:00' COMMENT 'Only 60 seconds to validate the rescue password',
  `usr_jwt_secret` VARCHAR(255) default NULL,
  `usr_jwt_token` VARCHAR(2000) default NULL,
  PRIMARY KEY  (`usr_per_ID`),
  UNIQUE KEY `usr_UserName` (`usr_UserName`),
  UNIQUE KEY `usr_apiKey` (`usr_webDavKey`),
  KEY `usr_per_ID` (`usr_per_ID`),
  CONSTRAINT fk_usr_per_ID
    FOREIGN KEY (usr_per_ID) REFERENCES person_per(per_ID),
  CONSTRAINT fk_usr_role_id
    FOREIGN KEY (usr_role_id) REFERENCES userrole_usrrol(usrrol_id)
    ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


--
-- Dumping data for table `user_usr`
--

INSERT INTO `user_usr` (`usr_per_ID`, `usr_Password`, `usr_NeedPasswordChange`, `usr_LastLogin`,
                        `usr_LoginCount`, `usr_FailedLogins`, `usr_AddRecords`, `usr_EditRecords`, `usr_DeleteRecords`,
                        `usr_MenuOptions`, `usr_ManageGroups`, `usr_Finance`, `usr_Notes`, `usr_Admin`,
                        `usr_PastoralCare`, `usr_GDRP_DPO`, `usr_MailChimp`, `usr_MainDashboard`, `usr_SeePrivacyData`,
                        `usr_showMenuQuery`, `usr_CanSendEmail`, `usr_ExportSundaySchoolCSV`, `usr_ExportSundaySchoolPDF`, `usr_CreateDirectory`, `usr_ExportCSV`,
                        `usr_ShowCart`, `usr_ShowMap`, `usr_EDrive`,
                        `usr_SearchLimit`, `usr_showPledges`,
                        `usr_showPayments`, `usr_showSince`, `usr_defaultFY`, `usr_currentDeposit`, `usr_UserName`, `usr_EditSelf`,
                        `usr_CalStart`, `usr_CalEnd`, `usr_CalNoSchool1`, `usr_CalNoSchool2`, `usr_CalNoSchool3`, `usr_CalNoSchool4`,
                        `usr_CalNoSchool5`, `usr_CalNoSchool6`, `usr_CalNoSchool7`, `usr_CalNoSchool8`, `usr_SearchFamily`,
                        `usr_Canvasser`)
VALUES
  (1, '4bdf3fba58c956fc3991a1fde84929223f968e2853de596e49ae80a91499609b', 1, '2016-01-01 00:00:00', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 10, 0, 0, '2019-01-01', 10, 0, 'Admin', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0);


-- --------------------------------------------------------

--
-- Table structure for table `volunteeropportunity_vol`
--

CREATE TABLE `volunteeropportunity_vol` (
  `vol_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `vol_Active` enum('true','false') NOT NULL default 'true',
  `vol_Name` varchar(30) default NULL,
  `vol_Description` varchar(100) default NULL,
  `vol_parent_ID` mediumint(8) unsigned DEFAULT NULL COMMENT 'parent volunteeropportunity_vol id',
  `vol_color` enum('bg-blue text-white', 'bg-indigo text-white', 'bg-navy text-white', 'bg-maroon text-white', 'bg-purple text-white', 'bg-pink text-white', 'bg-red text-white', 'bg-orange text-black', 'bg-yellow text-white', 'bg-lime text-white', 'bg-green text-white', 'bg-teal text-white', 'bg-cyan text-white', 'bg-gray text-black') NOT NULL default 'bg-blue text-white' COMMENT 'Color for a volunteer opportunity',
  `vol_icon` enum('fas fa-layer-group','fas fa-users','fas fa-desktop','fas fa-file','fas fa-comment','fas fa-music','fas fa-photo-video','fas fa-envelope','fas fa-headset', 'fas fa-book-reader' ) NOT NULL default 'fas fa-file' COMMENT 'icon of the volunteer opportunity',
  PRIMARY KEY  (`vol_ID`),
  UNIQUE KEY `vol_ID` (`vol_ID`),
  CONSTRAINT fk_vol_parent_ID
      FOREIGN KEY (vol_parent_ID)
          REFERENCES volunteeropportunity_vol(vol_ID)
          ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `volunteeropportunity_vol`
--

--
-- Dumping data for table `person2group2role_p2g2r`
--


-- --------------------------------------------------------

--
-- Table structure for table `person2volunteeropp_p2vo`
--

CREATE TABLE `person2volunteeropp_p2vo` (
  `p2vo_ID` mediumint(9) NOT NULL auto_increment,
  `p2vo_per_ID` mediumint(9) unsigned NOT NULL,
  `p2vo_vol_ID` mediumint(9) unsigned NOT NULL,
  PRIMARY KEY  (`p2vo_ID`),
  UNIQUE KEY `p2vo_ID` (`p2vo_ID`),
  CONSTRAINT fk_p2vo_per_ID
    FOREIGN KEY (p2vo_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE,
  CONSTRAINT fk_p2vo_vol_ID
    FOREIGN KEY (p2vo_vol_ID) REFERENCES volunteeropportunity_vol(vol_ID)
    ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `person2volunteeropp_p2vo`
--

--
-- Fundraiser support added 4/11/2009 Michael Wilt
--
CREATE TABLE `fundraiser_fr` (
  `fr_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `fr_date` date default NULL,
  `fr_title` varchar(128) NOT NULL,
  `fr_description` text,
  `fr_EnteredBy` smallint(5) unsigned NOT NULL default '0',
  `fr_EnteredDate` date NOT NULL,
  PRIMARY KEY  (`fr_ID`),
  UNIQUE KEY `fr_ID` (`fr_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `donateditem_di` (
  `di_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `di_item` varchar(32) NOT NULL,
  `di_FR_ID` mediumint(9) unsigned NOT NULL,
  `di_donor_ID` mediumint(9) unsigned NULL,
  `di_buyer_ID` mediumint(9) unsigned NULL,
  `di_multibuy` smallint(1) NOT NULL default '0',
  `di_title` varchar(128) NOT NULL,
  `di_description` text,
  `di_sellprice` decimal(8,2) default NULL,
  `di_estprice` decimal(8,2) default NULL,
  `di_minimum` decimal(8,2) default NULL,
  `di_materialvalue` decimal(8,2) default NULL,
  `di_EnteredBy` smallint(5) unsigned NOT NULL default '0',
  `di_EnteredDate` date NOT NULL,
  `di_picture` text,
  PRIMARY KEY  (`di_ID`),
  UNIQUE KEY `di_ID` (`di_ID`),
  CONSTRAINT fk_donateditem_di_fundraiser_id
    FOREIGN KEY (di_FR_ID) REFERENCES fundraiser_fr(fr_ID)
    ON DELETE CASCADE,
  CONSTRAINT fk_donor_person_id
    FOREIGN KEY (di_donor_ID)
    REFERENCES person_per(per_ID)
    ON DELETE SET NULL,
  CONSTRAINT fk_buyer_person_id
    FOREIGN KEY (di_buyer_ID)
    REFERENCES person_per(per_ID)
    ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `paddlenum_pn` (
  `pn_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `pn_fr_ID` mediumint(9) unsigned,
  `pn_Num` mediumint(9) unsigned,
  `pn_per_ID` mediumint(9) unsigned NOT NULL,
  PRIMARY KEY  (`pn_ID`),
  UNIQUE KEY `pn_ID` (`pn_ID`),
  CONSTRAINT fk_paddlenum_person_id
    FOREIGN KEY (pn_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE,
  CONSTRAINT fk_paddlenum_pn_fundraiser_id
    FOREIGN KEY (pn_fr_ID) REFERENCES fundraiser_fr(fr_ID)
    ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `multibuy_mb` (
  `mb_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `mb_per_ID` mediumint(9) unsigned NOT NULL,
  `mb_item_ID` mediumint(9) unsigned NOT NULL,
  `mb_count` decimal(8,0) default NULL,
  PRIMARY KEY  (`mb_ID`),
  UNIQUE KEY `mb_ID` (`mb_ID`),
  CONSTRAINT fk_multibuy_mb_person_id
    FOREIGN KEY (mb_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE,
  CONSTRAINT fk_multibuy_mb_donateditem_di_id
    FOREIGN KEY (mb_item_ID) REFERENCES donateditem_di(di_ID)
    ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `egive_egv` (
  `egv_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `egv_egiveID` varchar(16) character set utf8 NOT NULL,
  `egv_famID` mediumint(9) unsigned NOT NULL,
  `egv_DateEntered` datetime NOT NULL,
  `egv_DateLastEdited` datetime NOT NULL,
  `egv_EnteredBy` smallint(6) NOT NULL default '0',
  `egv_EditedBy` smallint(6) NOT NULL default '0',
  `egv_MoveDonations_Comment` text NOT NULL default 'None',
  PRIMARY KEY  (`egv_ID`),
  CONSTRAINT fk_egv_famID
    FOREIGN KEY (egv_famID) REFERENCES family_fam(fam_ID)
    ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `kioskdevice_kdev` (
  `kdev_ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `kdev_GUIDHash` char(64) DEFAULT NULL,
  `kdev_Name` varchar(50) DEFAULT NULL,
  `kdev_deviceType` mediumint(9) NOT NULL DEFAULT 0,
  `kdev_lastHeartbeat` TIMESTAMP,
  `kdev_Accepted` BOOLEAN,
  `kdev_PendingCommands` varchar(50),

  PRIMARY KEY  (`kdev_ID`),
  UNIQUE KEY `kdev_ID` (`kdev_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `kioskassginment_kasm` (
  `kasm_ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `kasm_kdevId` mediumint(9) DEFAULT NULL,
  `kasm_AssignmentType` mediumint(9) DEFAULT NULL,
  `kasm_EventId` mediumint(9) DEFAULT 0,
  PRIMARY KEY  (`kasm_ID`),
  UNIQUE KEY `kasm_ID` (`kasm_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `tokens` (
  `token` VARCHAR(99) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `reference_id` INT(9) NOT NULL,
  `valid_until_date` datetime NULL,
  `remainingUses` INT(2) NULL,
  `Comment` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `tokens_password` (
   `tok_pwd_ID` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
   `tok_pwd_token_ID` VARCHAR(99) NOT NULL,
   `tok_pwd_must_change_PWD` BOOLEAN NOT NULL default 1,
   `tok_pwd_password` varchar(255) NOT NULL default '',
   `tok_pwd_ip` varchar(255) NOT NULL default '',
   PRIMARY KEY (`tok_pwd_ID`),
   CONSTRAINT fk_tok_pwd_token_ID
       FOREIGN KEY (tok_pwd_token_ID) REFERENCES tokens(token)
           ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `church_location` (
  `location_id` INT NOT NULL,
  `location_typeId` INT NOT NULL,
  `location_name` VARCHAR(256) NOT NULL,
  `location_address` VARCHAR(45) NOT NULL,
  `location_city` VARCHAR(45) NOT NULL,
  `location_state` VARCHAR(45) NOT NULL,
  `location_zip` VARCHAR(45) NOT NULL,
  `location_country` VARCHAR(45) NOT NULL,
  `location_phone` VARCHAR(45) NULL,
  `location_email` VARCHAR(45) NULL,
  `location_timzezone` VARCHAR(45) NULL,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE `church_location_person` (
  `location_id` INT NOT NULL,
  `person_id` INT NOT NULL,
  `order` INT NOT NULL,
  `person_location_role_id` INT NOT NULL,  #This will be referenced to user-defined roles such as clergey, pastor, member, etc for non-denominational use
  PRIMARY KEY (`location_id`, `person_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `church_location_role` (
  `location_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  `role_order` INT NOT NULL,
  `role_title` INT NOT NULL,  #Thi
  PRIMARY KEY (`location_id`, `role_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

update version_ver set ver_update_end = now();


-- --------------------------------------------------------

--
-- Table structure for table `note_nte`
--

CREATE TABLE `note_nte` (
  `nte_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `nte_per_ID` mediumint(9) unsigned NULL,
  `nte_fam_ID` mediumint(9) unsigned NULL,
  `nte_Private` mediumint(8) unsigned NOT NULL default '0',
  `nte_Title` varchar(1000) DEFAULT '',
  `nte_Text` text,
  `nte_DateEntered` datetime NOT NULL,
  `nte_DateLastEdited` datetime default NULL,
  `nte_EnteredBy` mediumint(8) NOT NULL default '0',
  `nte_EditedBy` mediumint(8) unsigned NOT NULL default '0',
  `nte_isEditedBy` mediumint(8) unsigned NOT NULL default '0',
  `nte_isEditedByDate` datetime default NULL,
  `nte_Type` varchar(50) DEFAULT NULL,
  `nte_Info` varchar(500) DEFAULT NULL,
  PRIMARY KEY  (`nte_ID`),
  CONSTRAINT fk_nte_per_ID
    FOREIGN KEY (nte_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE,
  CONSTRAINT fk_nte_fam_ID
    FOREIGN KEY (nte_fam_ID) REFERENCES family_fam(fam_ID)
    ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `note_nte_share`
--


CREATE TABLE note_nte_share (
    `nte_sh_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `nte_sh_note_ID` mediumint(9) unsigned NULL,
    `nte_sh_share_to_person_ID` mediumint(9) unsigned NULL,
    `nte_sh_share_to_family_ID` mediumint(9) unsigned NULL,
    `nte_sh_share_rights` smallint(2) NOT NULL default '1',
    PRIMARY KEY(nte_sh_id),
    CONSTRAINT fk_nte_note_ID
      FOREIGN KEY (nte_sh_note_ID)
      REFERENCES note_nte(nte_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_nte_share_from_person_ID
      FOREIGN KEY (nte_sh_share_to_person_ID)
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_nte_share_from_family_ID
      FOREIGN KEY (nte_sh_share_to_family_ID)
      REFERENCES family_fam(fam_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_manager_person`
--


CREATE TABLE group_manager_person (
    `grp_mgr_per_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `grp_mgr_per_person_ID` mediumint(9) unsigned NOT NULL,
    `grp_mgr_per_group_ID` mediumint(9) unsigned NOT NULL,
    PRIMARY KEY(grp_mgr_per_id),
    CONSTRAINT fk_grp_mgr_per_person_ID
      FOREIGN KEY (grp_mgr_per_person_ID)
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_grp_mgr_per_group_ID
      FOREIGN KEY (grp_mgr_per_group_ID)
      REFERENCES group_grp(grp_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- We create ckeditor_templates table
--
CREATE TABLE ckeditor_templates (
    `cke_tmp_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `cke_tmp_per_ID` mediumint(9) unsigned NOT NULL,
    `cke_tmp_title` varchar(255) NOT NULL default '',
    `cke_tmp_desc` varchar(255) default NULL,
    `cke_tmp_text` text,
    `cke_tmp_image` varchar(255) default NULL,
    PRIMARY KEY(cke_tmp_id),
    CONSTRAINT fk_cke_tmp_per_ID
      FOREIGN KEY (cke_tmp_per_ID)
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Pastoral care type for a person
--
CREATE TABLE pastoral_care_type (
    `pst_cr_tp_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `pst_cr_tp_title` varchar(255) NOT NULL default '',
    `pst_cr_tp_desc` varchar(255) NOT NULL default '',
    `pst_cr_tp_visible` BOOLEAN NOT NULL default 0,
    `pst_cr_tp_comment` text NOT NULL COMMENT 'comment for GDPR',
    PRIMARY KEY(pst_cr_tp_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO `pastoral_care_type` (`pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
  ('Classical Pastoral note','', true, ''),
  ('Why did you come to the church?','', true, ''),
  ('Why do you keep coming?','', true, ''),
  ('Do you have any suggestions for us?','', true, ''),
  ('How did you learn of the church?','', true, ''),
  ('Baptism', 'Baptism formation', false, ''),
  ('Mariage', 'Mariage formation', false, ''),
  ('Psychology', 'Psychology therapy', false, '');

--
-- Pastoral care for a person
--
CREATE TABLE pastoral_care (
    `pst_cr_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `pst_cr_person_id` mediumint(9) unsigned NULL,
    `pst_cr_family_id` mediumint(9) unsigned NULL,
    `pst_cr_pastor_id` mediumint(9) unsigned NULL,
    `pst_cr_pastor_Name` varchar(255) NOT NULL default '',
    `pst_cr_Type_id` mediumint(9) unsigned NOT NULL,
    `pst_cr_date` datetime default NULL,
    `pst_cr_visible` BOOLEAN NOT NULL default 0,
    `pst_cr_Text` text,
    PRIMARY KEY(pst_cr_id),
    CONSTRAINT fk_pst_cr_person_id
      FOREIGN KEY (pst_cr_person_id)
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_pst_cr_family_id
      FOREIGN KEY (pst_cr_family_id)
      REFERENCES family_fam(fam_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_pst_cr_pastor_id
      FOREIGN KEY (pst_cr_pastor_id)
      REFERENCES person_per(per_ID)
      ON DELETE SET NULL,
    CONSTRAINT fk_pst_cr_Type_id
      FOREIGN KEY (pst_cr_Type_id)
      REFERENCES pastoral_care_type(pst_cr_tp_id)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- Table structure for table `menu_links`
--

CREATE TABLE `menu_links` (
  `linkId` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `linkPersonId` mediumint(9) unsigned default NULL,
  `linkName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linkUri` text COLLATE utf8_unicode_ci NOT NULL,
  `linkOrder` INT NOT NULL,
  PRIMARY KEY (`linkId`),
  CONSTRAINT fk_linkPersonId
    FOREIGN KEY (linkPersonId)
    REFERENCES person_per(per_ID)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `autopayment_aut`
--

CREATE TABLE `autopayment_aut` (
  `aut_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `aut_FamID` mediumint(9) unsigned NOT NULL default '0',
  `aut_EnableBankDraft` tinyint(1) unsigned NOT NULL default '0',
  `aut_EnableCreditCard` tinyint(1) unsigned NOT NULL default '0',
  `aut_NextPayDate` date default NULL,
  `aut_FYID` mediumint(9) NOT NULL default '9',
  `aut_Amount` decimal(6,2) NOT NULL default '0.00',
  `aut_Interval` tinyint(3) NOT NULL default '1',
  `aut_Fund` tinyint(3) default NULL,
  `aut_FirstName` varchar(50) default NULL,
  `aut_LastName` varchar(50) default NULL,
  `aut_Address1` varchar(255) default NULL,
  `aut_Address2` varchar(255) default NULL,
  `aut_City` varchar(50) default NULL,
  `aut_State` varchar(50) default NULL,
  `aut_Zip` varchar(50) default NULL,
  `aut_Country` varchar(50) default NULL,
  `aut_Phone` varchar(30) default NULL,
  `aut_Email` varchar(100) default NULL,
  `aut_CreditCard` varchar(50) default NULL,
  `aut_ExpMonth` varchar(2) default NULL,
  `aut_ExpYear` varchar(4) default NULL,
  `aut_BankName` varchar(50) default NULL,
  `aut_Route` varchar(30) default NULL,
  `aut_Account` varchar(30) default NULL,
  `aut_DateLastEdited` datetime default NULL,
  `aut_EditedBy` mediumint(9) unsigned NULL,
  `aut_Serial` mediumint(9) NOT NULL default '1',
  `aut_CreditCardVanco` varchar(50) default NULL,
  `aut_AccountVanco` varchar(50) default NULL,
  PRIMARY KEY  (`aut_ID`),
  UNIQUE KEY `aut_ID` (`aut_ID`),
  CONSTRAINT fk_aut_FamID
    FOREIGN KEY (aut_FamID) REFERENCES family_fam(fam_ID)
    ON DELETE CASCADE,
  CONSTRAINT fk_aut_Fund
    FOREIGN KEY (aut_Fund) REFERENCES donationfund_fun(fun_ID)
    ON DELETE SET NULL,
  CONSTRAINT fk_aut_EditedBy
    FOREIGN KEY (aut_EditedBy) REFERENCES person_per(per_ID)
    ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `autopayment_aut`
--


--
-- Table structure for table `gdpr_infos`
--

CREATE TABLE `gdpr_infos` (
  `gdpr_info_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `gdpr_info_About` enum('Person','Family') NOT NULL default 'Person',
  `gdpr_info_Name` varchar(40) NOT NULL default '',
  `gdpr_info_Type` tinyint(4) NOT NULL default '0',
  `gdpr_info_comment` text NOT NULL COMMENT 'comment for GDPR',
  PRIMARY KEY  (`gdpr_info_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- the default value for the CRM : family and person DN Tables
INSERT INTO `gdpr_infos` (`gdpr_info_About`, `gdpr_info_Name`, `gdpr_info_Type`, `gdpr_info_comment`) VALUES
('Person', 'Gender', '3', ''),
('Person', 'Title', '3', ''),
('Person', 'First Name', '3', ''),
('Person', 'Middle Name', '3', ''),
('Person', 'Last Name', '3', ''),
('Person', 'Suffix', '3', ''),
('Person', 'Birth Month', '12', ''),
('Person', 'Birth Day', '12', ''),
('Person', 'Birth Year', '6', ''),
('Person', 'Hide Age', '3', ''),
('Person', 'Home Phone', '3', ''),
('Person', 'Work Phone', '3', ''),
('Person', 'Mobile Phone', '3', ''),
('Person', 'Email', '3', ''),
('Person', 'Work / Other Email', '3', ''),
('Person', 'Facebook ID', '3', ''),
('Person', 'Twitter', '3', ''),
('Person', 'LinkedIn', '3', ''),
('Person', 'Classification', '12', ''),
('Person', 'Membership Date','2',''),
('Person', 'Friend Date','2',''),
('Family', 'Family Name', '3', ''),
('Family', 'Address 1', '4', ''),
('Family', 'Address 2', '4', ''),
('Family', 'City', '3', ''),
('Family', 'Country', '12', ''),
('Family', 'State', '3', ''),
('Family', 'Home Phone', '3', ''),
('Family', 'Work Phone', '3', ''),
('Family', 'Mobile Phone', '3', ''),
('Family', 'Email', '3', ''),
('Family', 'Send Newsletter', '1', ''),
('Family', 'Wedding Date', '2', ''),
('Family', 'Role', '12', '');
--
-- Dumping data for table `gdpr_infos`
--


--
-- Table structure for table `plugin`
--

CREATE TABLE `plugin` (
  `plgn_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `plgn_Name` varchar(255) DEFAULT '',
  `plgn_Description` text,
  `plgn_Category` enum('Dashboard', 'Personal', 'GDPR', 'Events','PEOPLE','GROUP', 'SundaySchool', 'Meeting', 'PastoralCare', 'Mail', 'Deposit', 'Funds', 'FreeMenu') NOT NULL default 'Personal' COMMENT 'For the left side menu bar',
  `plgn_UserRole_Dashboard_Availability` BOOLEAN NOT NULL default 0 COMMENT 'role choice (none/user/admin) available for dashboard plugins only ',
  `plgn_position` enum('inside_category_menu', 'after_category_menu') NOT NULL default 'after_category_menu' COMMENT 'Inside category menu or after',
  `plgn_securities` INT(40) DEFAULT 0 COMMENT 'See for this point EcclesiaCRM/User.php model class in : SecurityOptions 0 mean not dashboard',
  `plgn_default_orientation` enum('top', 'left', 'center', 'right') NOT NULL default 'left' COMMENT 'only for dashboard plugins',
  `plgn_default_color` enum('bg-gradient-blue text-white', 'bg-gradient-indigo text-white', 'bg-gradient-navy text-white', 'bg-gradient-maroon text-white', 'bg-gradient-purple text-white', 'bg-gradient-pink text-white', 'bg-gradient-red text-white', 'bg-gradient-orange text-black', 'bg-gradient-yellow text-black', 'bg-gradient-lime text-black', 'bg-gradient-green text-black', 'bg-gradient-teal text-black', 'bg-gradient-cyan text-black', 'bg-gradient-gray text-white') NOT NULL default 'bg-gradient-blue text-white' COMMENT 'Default Background dashboard color',
  `plgn_image` varchar(255) default NULL COMMENT 'Presentation image',
  `plgn_installation_path` varchar(5000) DEFAULT '' COMMENT 'path of the plugin',
  `plgn_activ` BOOLEAN NOT NULL default 0 COMMENT 'activation status',
  `plgn_version` varchar(50) NOT NULL default '',
  `plgn_prefix` varchar(50) NOT NULL default '' COMMENT 'prefix of the database tables, to avoid conflicts',
  `plgn_mailer` BOOLEAN NOT NULL default 0,
  PRIMARY KEY  (`plgn_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugin`
--


--
-- Table structure for table `plugin_menu_bar`
--

CREATE TABLE `plugin_menu_bar` (
  `plgn_mb_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `plgn_mb_plugin_name` varchar(255) DEFAULT '',
  `plgn_mb_plugin_Display_name` varchar(255) DEFAULT '',
  `plgn_mb_url` varchar(255) DEFAULT '' COMMENT 'URL Menubar',
  `plgn_bm_icon` varchar(255) DEFAULT '' COMMENT 'Icon MenuBar',
  `plgn_bm_grp_sec` varchar(255) DEFAULT '' COMMENT 'In lower case : usr_AddRecords, usr_EditRecords, usr_DeleteRecords, usr_ShowCart, usr_ShowMap, usr_EDrive, usr_MenuOptions, usr_ManageGroups, usr_ManageCalendarResources, usr_HtmlSourceEditor, usr_Finance, usr_Notes, usr_EditSelf, usr_Canvasser, usr_Admin, usr_showMenuQuery, usr_CanSendEmail, usr_ExportCSV, usr_CreateDirectory, usr_ExportSundaySchoolPDF, usr_ExportSundaySchoolCSV, usr_MainDashboard, usr_SeePrivacyData, usr_MailChimp, usr_GDRP_DPO, usr_PastoralCare',
  `plgn_mb_parent_ID` mediumint(8) unsigned DEFAULT NULL COMMENT 'in the case of a link : the parent is plgn_mb_ID',
  PRIMARY KEY  (`plgn_mb_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugin_menu_bar`
--

--
-- Table structure for table `plugin_user_role`
--

CREATE TABLE `plugin_user_role` (
    `plgn_usr_rl_ID` mediumint(8) unsigned NOT NULL auto_increment,
    `plgn_usr_rl_user_id` mediumint(9) unsigned NOT NULL default '0',
    `plgn_usr_rl_plugin_id` mediumint(8) unsigned NOT NULL default '0',
    `plgn_usr_rl_role` enum('none', 'user', 'admin') NOT NULL default 'none' COMMENT 'user role : can be the thee enum parts',
    `plgn_usr_rl_visible` BOOLEAN NOT NULL default 1 COMMENT 'visible on dashboard (only for dashboard plugins)',
    `plgn_usr_rl_orientation` enum('top', 'left', 'center', 'right') NOT NULL default 'left' COMMENT 'only for dashboard plugins',
    `plgn_usr_rl_place` mediumint(9) unsigned NOT NULL default '0' COMMENT 'position on the dashboard',
    `plgn_usr_rl_color` enum('bg-gradient-blue text-white', 'bg-gradient-indigo text-white', 'bg-gradient-navy text-white', 'bg-gradient-maroon text-white', 'bg-gradient-purple text-white', 'bg-gradient-pink text-white', 'bg-gradient-red text-white', 'bg-gradient-orange text-black', 'bg-gradient-yellow text-black', 'bg-gradient-lime text-black', 'bg-gradient-green text-black', 'bg-gradient-teal text-black', 'bg-gradient-cyan text-black', 'bg-gradient-gray text-white') NOT NULL default 'bg-gradient-blue text-white' COMMENT 'Background dashboard color',
    `plgn_collapsed` BOOLEAN NOT NULL default 0 COMMENT 'the plugin is collapse on the dashboard by the default no',
    PRIMARY KEY  (`plgn_usr_rl_ID`),
    CONSTRAINT fk_plgn_usr_rl_user_id FOREIGN KEY (plgn_usr_rl_user_id) REFERENCES user_usr(usr_per_ID) ON DELETE CASCADE,
    CONSTRAINT fk_plgn_usr_rl_plugin_id FOREIGN KEY (plgn_usr_rl_plugin_id) REFERENCES plugin(plgn_ID) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Table structure for table `addressbooks`
--
CREATE TABLE cards (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    addressbookid INT(11) UNSIGNED NOT NULL,
    carddata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    personId mediumint(9) unsigned NOT NULL COMMENT '-1 personal cards, >1 for a real person in the CRM',
    size INT(11) UNSIGNED NOT NULL,
    UNIQUE KEY (id),
    PRIMARY KEY  (`id`),
    CONSTRAINT fk_cards_personId
      FOREIGN KEY (personId) REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_card_addressbookid
      FOREIGN KEY (addressbookid) REFERENCES addressbooks(id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `addressbookshare`
--
CREATE TABLE addressbookshare (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    addressbookid INT(11) UNSIGNED NOT NULL,
    principaluri VARBINARY(255),
    displayname VARCHAR(255),
    description TEXT,
    href VARBINARY(100),
    user_id mediumint(9) unsigned NOT NULL default '0',
    access TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = owner, 2 = read, 3 = readwrite',
    UNIQUE KEY (id),
    CONSTRAINT fk_addressbookid FOREIGN KEY (addressbookid) REFERENCES addressbooks(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES user_usr(usr_per_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



--
-- Table structure for table `send_news_letter_user_update`
--

CREATE TABLE `send_news_letter_user_update` (
  `snl_ID` mediumint(9) unsigned NOT NULL auto_increment,
  `snl_person_ID` mediumint(9) unsigned NOT NULL,
  `snl_state` enum('Add','Delete') NOT NULL default 'Add',
  PRIMARY KEY  (`snl_ID`),
  CONSTRAINT fk_snl_person_ID FOREIGN KEY (snl_person_ID) REFERENCES person_per(per_id) ON DELETE CASCADE  
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugin_user_role`
--

-- method for dup emails

CREATE VIEW email_list AS
    SELECT fam_Email AS email, 'family' AS type, fam_id AS id FROM family_fam WHERE fam_email IS NOT NULL AND fam_email != ''
    UNION
    SELECT per_email AS email, 'person_home' AS type, per_id AS id FROM person_per WHERE per_email IS NOT NULL AND per_email != ''
    UNION
    SELECT per_WorkEmail AS email, 'person_work' AS type, per_id AS id FROM person_per WHERE per_WorkEmail IS NOT NULL AND per_WorkEmail != '';

CREATE VIEW email_count AS
    SELECT email, COUNT(*) AS total FROM email_list group by email;
