CREATE TABLE IF NOT EXISTS `gb_staff`(
	`UserID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`CommunityID` INT UNSIGNED,
	`Login` VARCHAR(24) NOT NULL,
	`Passwd` CHAR(32) NOT NULL,
	`Group` ENUM('admin', 'author', 'client', 'courier', 'developer', 'manager', 'mediator', 'partner', 'performer', 'topmanager') DEFAULT 'client',
	`Departament` VARCHAR(24) DEFAULT '',
	PRIMARY KEY(`UserID`),
	UNIQUE(`login`),
	FOREIGN KEY (`CommunityID`) REFERENCES `gb_community`(`CommunityID`)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

INSERT INTO `gb_staff` (`CommunityID`, `Login`, `Passwd`, `Group`, `Departament` ) VALUES (1, 'Admin', MD5('goolybeep'), 'admin', 'Goolybeep');