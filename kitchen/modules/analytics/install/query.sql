
CREATE TABLE IF NOT EXISTS `gb_user-analytics`(
	`day` INT UNSIGNED,
	`views` MEDIUMINT(6) UNSIGNED DEFAULT 1, 
	`reviews` MEDIUMINT(6) UNSIGNED DEFAULT 1, 
	PRIMARY KEY(`day`)
)ENGINE=InnoDB COMMENT='analytics';

CREATE TABLE IF NOT EXISTS `gb_pages`(
	`PageID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`views` MEDIUMINT(6) UNSIGNED DEFAULT 0,
	`time` INT UNSIGNED DEFAULT 0,
	`rating` INT(5) UNSIGNED NOT NULL DEFAULT 4,
	`type` ENUM('material','category','article','video','story','gallery','component') NOT NULL DEFAULT 'article',
	`created` INT UNSIGNED NOT NULL DEFAULT 1,
	`modified` INT UNSIGNED NOT NULL DEFAULT 1,
	INDEX `time`(`created`),
	PRIMARY KEY(`PageID`)
)ENGINE=InnoDB COMMENT='analytics';

CREATE TABLE IF NOT EXISTS `gb_components`(
	`PageID` INT UNSIGNED NOT NULL,
	`name` VARCHAR(32) NOT NULL DEFAULT '',
	`event` ENUM('click','over','out','move','view','submit','reset','change','input','set','drop') NOT NULL DEFAULT 'click',
	UNIQUE (`PageID`,`event`),
	FOREIGN KEY (`PageID`) REFERENCES `gb_pages`(`PageID`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
)ENGINE=InnoDB CHARACTER SET utf8;