CREATE TABLE IF NOT EXISTS `gb_community`(
	`CommunityID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`App` ENUM('facebook','google','twitter','vk','ok','self') NOT NULL DEFAULT 'self',
	`CitizenID` BIGINT UNSIGNED NOT NULL DEFAULT 1,
	`Name` TINYTEXT NOT NULL DEFAULT '',
	`Email` VARCHAR(32) NOT NULL DEFAULT 'n/a',
	`Visit` INT UNSIGNED NOT NULL DEFAULT 1,
	`tid` INT UNSIGNED DEFAULT 1,
	`reputation` INT(4) DEFAULT 1,
	`options` VARCHAR(512) NOT NULL DEFAULT '{}',
	PRIMARY KEY(`CommunityID`),
	UNIQUE(`App`, `CitizenID`),
	FOREIGN KEY (`tid`) REFERENCES `gb_tagination`(`tid`)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

INSERT INTO `gb_community` (`Name`, `CitizenID`) VALUES ('Admin', 1), ('Visitor', 2)