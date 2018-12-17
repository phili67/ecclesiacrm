ALTER TABLE `addressbooks` ADD `groupId` mediumint(8) NOT NULL default -1  COMMENT '-1 personal addressbook, >1 for a group in the CRM';

ALTER TABLE `cards` ADD `personId` mediumint(9) NOT NULL default -1 COMMENT '-1 personal cards, >1 for a real person in the CRM';

--
-- a calendar could be shared to another user
--

DROP TABLE IF EXISTS addressbookshare;

CREATE TABLE IF NOT EXISTS addressbookshare (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    addressbooksid INT(11) UNSIGNED NOT NULL,
    principaluri VARBINARY(255),
    displayname VARCHAR(255),
    description TEXT,
    href VARBINARY(100),
    access TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = owner, 2 = read, 3 = readwrite',
    UNIQUE(principaluri(100)),
    UNIQUE(addressbooksid, principaluri),
    CONSTRAINT fk_addressbooksid FOREIGN KEY (addressbooksid) REFERENCES addressbooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;