CREATE TABLE IF NOT EXISTS gb_stream(
	CardID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	SortedID INT(2) NOT NULL DEFAULT 0,
	tid INT UNSIGNED,
	UserID INT UNSIGNED,
	CommunityID INT UNSIGNED,
	value FLOAT NOT NULL DEFAULT 0.0,
	created INT UNSIGNED NOT NULL DEFAULT 1,
	modified TIMESTAMP,
	task VARCHAR(2048) NOT NULL DEFAULT '',
	type ENUM('article','story','video', 'images', 'repost') NOT NULL DEFAULT 'article',
	image VARCHAR(256) NOT NULL DEFAULT '',
	header VARCHAR(1024) NOT NULL DEFAULT '',
	source VARCHAR(32) NOT NULL DEFAULT '',
	link VARCHAR(256) NOT NULL DEFAULT '',
	status ENUM('new', 'in work', 'waste', 'done') NOT NULL DEFAULT 'new',
	PRIMARY KEY(CardID),
	FOREIGN KEY (UserID) REFERENCES gb_staff(UserID)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (CommunityID) REFERENCES gb_community(CommunityID)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (tid) REFERENCES gb_tagination(tid)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;