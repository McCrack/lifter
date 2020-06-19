CREATE TABLE IF NOT EXISTS gb_orders(
	OrderID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	UserID INT UNSIGNED,
	CommunityID INT UNSIGNED,
	created INT UNSIGNED NOT NULL DEFAULT 1,
	modified INT UNSIGNED NOT NULL DEFAULT 1,
	paid INT UNSIGNED NOT NULL DEFAULT 0,
	type SET('deal','delivery','reserved') NOT NULL DEFAULT 'deal',
	status SET('new','accepted','canceled') NOT NULL DEFAULT 'new',
	payment SET('cash','card','on delivery') NOT NULL DEFAULT 'on delivery',
	price DECIMAL(8,2) NOT NULL DEFAULT '0.00',
	discount TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
	delivery VARCHAR(1024) NOT NULL DEFAULT '{
		"tracking":"",
		"city":"",
		"office":""
	}',
	log VARCHAR(4096) NOT NULL DEFAULT '',
	body VARCHAR(1024) NOT NULL DEFAULT '{}',
	PRIMARY KEY(OrderID),
	FOREIGN KEY (CommunityID) REFERENCES gb_community(CommunityID)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (`UserID`) REFERENCES `gb_staff`(`UserID`)
		ON UPDATE CASCADE
		ON DELETE SET NULL
)ENGINE=InnoDB CHARACTER SET utf8;