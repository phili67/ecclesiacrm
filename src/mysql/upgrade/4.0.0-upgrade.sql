--
-- The bAddEvent is no more usefull
--

DELETE FROM `userconfig_ucfg` WHERE ucfg_name = 'bAddEvent';

-- 
-- Update : new user config flags
-- 
INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
  (0, 3, 'bExportSundaySchoolCSV', '0', 'boolean', 'User permission to export CSV files for the sunday school', 'FALSE', ''),
  (1, 3, 'bExportSundaySchoolCSV', '1', 'boolean', 'User permission to export CSV files for the sunday school', 'TRUE', ''),
  (0, 4, 'bExportSundaySchoolPDF', '0', 'boolean', 'User permission to export PDF files for the sunday school', 'FALSE', ''),
  (1, 4, 'bExportSundaySchoolPDF', '1', 'boolean', 'User permission to export PDF files for the sunday school', 'TRUE', ''),
  (0, 9, 'sCSVExportDelemiter', ',', 'text', 'To export to another For european CharSet use ;', 'TRUE', ''),
  (1, 9, 'sCSVExportDelemiter', ',', 'text', 'To export to another For european CharSet use ;', 'TRUE', ''),
  (0, 10, 'sCSVExportCharset', 'UTF-8', 'text', 'Default is UTF-8, For european CharSet use Windows-1252 for example for French language.', 'TRUE', ''),
  (1, 10, 'sCSVExportCharset', 'UTF-8', 'text', 'Default is UTF-8, For european CharSet use Windows-1252 for example for French language.', 'TRUE', ''),
  (0, 12, 'bSidebarExpandOnHover', '1', 'boolean', 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
  (1, 12, 'bSidebarExpandOnHover', '1', 'boolean', 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
  (0, 13, 'bSidebarCollapse', '1', 'boolean', 'The sidebar is collapse by default', 'TRUE', ''),
  (1, 13, 'bSidebarCollapse', '1', 'boolean', 'The sidebar is collapse by default', 'TRUE', '');

--
-- We add the new columns in events_event
--
ALTER TABLE `events_event` ADD  `event_last_occurence` datetime NOT NULL;
ALTER TABLE `events_event` ADD  `event_location` text;
ALTER TABLE `events_event` ADD  `event_calendardata` mediumblob;
ALTER TABLE `events_event` ADD  `event_uri` varbinary(200) DEFAULT NULL;
ALTER TABLE `events_event` ADD  `event_calendarid` int(10) UNSIGNED NOT NULL  DEFAULT '0';
ALTER TABLE `events_event` ADD  `event_lastmodified` int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE `events_event` ADD  `event_etag` varbinary(32) DEFAULT NULL;
ALTER TABLE `events_event` ADD  `event_size` int(11) UNSIGNED NOT NULL;
ALTER TABLE `events_event` ADD  `event_componenttype` varbinary(8) DEFAULT NULL;
ALTER TABLE `events_event` ADD  `event_uid` varbinary(200) DEFAULT NULL;

ALTER TABLE `events_event`
  ADD UNIQUE KEY `event_calendarid` (`event_calendarid`,`event_uri`),
  ADD KEY `calendarid_time` (`event_calendarid`);

--
-- we drop the event_parent_id, it's no more usefull with sabre
--
ALTER TABLE `events_event` DROP FOREIGN KEY `fk_event_parent_id`;
ALTER TABLE events_event DROP COLUMN event_parent_id;

-- last we add the new tables

CREATE TABLE calendars (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    synctoken INTEGER UNSIGNED NOT NULL DEFAULT '1',
    components VARBINARY(21)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE calendarinstances (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendarid INTEGER UNSIGNED NOT NULL  DEFAULT '0',
    principaluri VARBINARY(100),
    access TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = owner, 2 = read, 3 = readwrite',
    displayname VARCHAR(100),
    uri VARBINARY(200),
    description TEXT,
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10),
    visible BOOLEAN NOT NULL default 0,
    present BOOLEAN NOT NULL default 1,
    timezone TEXT,
    transparent TINYINT(1) NOT NULL DEFAULT '0',
    share_href VARBINARY(100),
    share_displayname VARCHAR(100),
    share_invitestatus TINYINT(1) NOT NULL DEFAULT '2' COMMENT '1 = noresponse, 2 = accepted, 3 = declined, 4 = invalid',
    grpid mediumint(9) NOT NULL DEFAULT '0',
    UNIQUE(principaluri, uri),
    UNIQUE(calendarid, principaluri),
    UNIQUE(calendarid, share_href)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE calendarchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

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

CREATE TABLE schedulingobjects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


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


CREATE TABLE principals (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    email VARBINARY(80),
    displayname VARCHAR(80),
    UNIQUE(uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE groupmembers (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principal_id INTEGER UNSIGNED NOT NULL,
    member_id INTEGER UNSIGNED NOT NULL,
    UNIQUE(principal_id, member_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


INSERT INTO principals (uri,email,displayname) VALUES
('principals/admin', 'admin@example.org','Administrator'),
('principals/admin/calendar-proxy-read', null, null),
('principals/admin/calendar-proxy-write', null, null);


CREATE TABLE propertystorage (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    path VARBINARY(1024) NOT NULL,
    name VARBINARY(100) NOT NULL,
    valuetype INT UNSIGNED,
    value MEDIUMBLOB
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE UNIQUE INDEX path_property ON propertystorage (path(600), name(100));