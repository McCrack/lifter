CREATE TABLE IF NOT EXISTS `gb_blogfeed`(
	`PageID` INT UNSIGNED NOT NULL,
	`ID` INT UNSIGNED NOT NULL,
	`language` ENUM('uk','ru') NOT NULL DEFAULT 'uk',
	`published` ENUM('Not published','Published') NOT NULL DEFAULT 'Not published',
	`readiness` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`tid` INT UNSIGNED,
	`header` VARCHAR(256) NOT NULL DEFAULT '', 
	`subheader` VARCHAR(1024) NOT NULL DEFAULT '', 
	`preview` VARCHAR(256) NOT NULL DEFAULT '/images/NIA.jpg',
	`portrait` VARCHAR(256) NOT NULL DEFAULT 'https://lifter.com.ua/images/portrait.jpg',
	`video` VARCHAR(256) NOT NULL DEFAULT '/images/lifter.mp4',
	`category` VARCHAR(32) NOT NULL DEFAULT 'article',
	`subtemplate` VARCHAR(32) NOT NULL DEFAULT 'default',
	`UserID` INT UNSIGNED, 
	UNIQUE(`ID`,`language`),
	PRIMARY KEY(`PageID`),
	FOREIGN KEY (`PageID`) REFERENCES `gb_pages`(`PageID`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (`tid`) REFERENCES `gb_tagination`(`tid`)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (`UserID`) REFERENCES `gb_staff`(`UserID`)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS `gb_blogcontent`(
	PageID INT UNSIGNED NOT NULL,
	Ads ENUM('YES','NO') NOT NULL DEFAULT 'YES',
	content BLOB,
	PRIMARY KEY(`PageID`),
	FOREIGN KEY (`PageID`) REFERENCES `gb_pages`(`PageID`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS gb_amp(
	PageID INT UNSIGNED NOT NULL,
	content BLOB,
	PRIMARY KEY(`PageID`),
	FOREIGN KEY (`PageID`) REFERENCES `gb_pages`(`PageID`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;