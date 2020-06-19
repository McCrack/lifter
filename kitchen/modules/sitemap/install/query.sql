CREATE TABLE IF NOT EXISTS `gb_sitemap`(
	`PageID` INT UNSIGNED NOT NULL,
	`published` ENUM('Not published','Published') NOT NULL DEFAULT 'Not published',
	`parent` VARCHAR(128) NOT NULL DEFAULT 'root',
	`soundex` CHAR(4) NOT NULL DEFAULT '',
	`language` ENUM('uk','ru') NOT NULL DEFAULT 'uk',
	`name` VARCHAR(128) NOT NULL,
	`header` VARCHAR(256) NOT NULL DEFAULT '',
	`subheader` VARCHAR(256) NOT NULL DEFAULT '',
	`preview` VARCHAR(256) NOT NULL DEFAULT '/images/NIA.jpg',
	PRIMARY KEY(`PageID`),
	FOREIGN KEY (`PageID`) REFERENCES `gb_pages`(`PageID`)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	UNIQUE(`language`,`name`),
	INDEX `sounding`(`soundex`)
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS `gb_static`(
	`PageID` INT UNSIGNED NOT NULL,
	`module` VARCHAR(24) NOT NULL DEFAULT '',
	`template` VARCHAR(24) NOT NULL DEFAULT '',
	`content` BLOB,
	`context` VARCHAR(256) NOT NULL DEFAULT '',
	`optionset` VARCHAR(1024) NOT NULL DEFAULT '{}',
	`description` VARCHAR(2048) NOT NULL DEFAULT '',
	PRIMARY KEY(`PageID`),
	FOREIGN KEY (`PageID`) REFERENCES `gb_sitemap`(`PageID`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;