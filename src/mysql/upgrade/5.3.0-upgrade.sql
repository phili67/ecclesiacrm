--
-- a calendar could be shared to another user
--
CREATE TABLE IF NOT EXISTS addressbookshare (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    addressbooksid INTEGER UNSIGNED NOT NULL  DEFAULT '0',
    principaluri VARBINARY(255),
    displayname VARCHAR(255),
    description TEXT,
    href VARBINARY(100),
    access TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1 = owner, 2 = read, 3 = readwrite',
    UNIQUE(principaluri(100)),
    UNIQUE(addressbooksid, principaluri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;