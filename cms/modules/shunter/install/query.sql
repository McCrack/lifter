CREATE TABLE IF NOT EXISTS gb_task_timing(
	TaskID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	created INT UNSIGNED,
	towork INT UNSIGNED NOT NULL DEFAULT 0,
	towaste INT UNSIGNED NOT NULL DEFAULT 0,
	CommunityID INT UNSIGNED,
	log VARCHAR(4096) NOT NULL DEFAULT '',
	PRIMARY KEY(TaskID),
	FOREIGN KEY (CommunityID) REFERENCES gb_community(CommunityID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS gb_task_shunter(
	TaskID INT UNSIGNED NOT NULL,
	UserID INT UNSIGNED NOT NULL,
	PageID INT UNSIGNED,
	SortID INT(4) NOT NULL DEFAULT 0,
	rank INT(4) NOT NULL DEFAULT 0,
	status ENUM('new', 'in work', 'done', 'waste', 'deleted') NOT NULL DEFAULT 'new',
	type VARCHAR(24) NOT NULL DEFAULT 'article',
	image VARCHAR(256) NOT NULL DEFAULT '',
	header VARCHAR(512) NOT NULL DEFAULT '',
	link VARCHAR(256),
	optionset VARCHAR(1024) NOT NULL DEFAULT '{}',
	task VARCHAR(2048) NOT NULL DEFAULT '',
	PRIMARY KEY(TaskID),
	FOREIGN KEY (TaskID) REFERENCES gb_task_timing(TaskID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (UserID) REFERENCES gb_staff(UserID)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (PageID) REFERENCES gb_pages(PageID)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;