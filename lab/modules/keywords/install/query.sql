CREATE TABLE IF NOT EXISTS `gb_tagination`(
	`tid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`0` INT UNSIGNED DEFAULT 0,
	INDEX `section`(`0`),
	PRIMARY KEY(`tid`)
)ENGINE=InnoDB;

INSERT INTO `gb_tagination` (`0`) VALUES (4294967295), (0); 
/*	Первая запись для новых поьзователей (все интересы включены)
	Вторая запись для новых постов (тематические теги не установлены) */

CREATE TABLE IF NOT EXISTS `gb_keywords`(
	`id` INT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
	`tag` VARCHAR(32) NOT NULL,
	`rating` SMALLINT(4) UNSIGNED DEFAULT 1,
	PRIMARY KEY(`id`),
	UNIQUE(`tag`)
)ENGINE=InnoDB CHARACTER SET utf8;