CREATE TABLE core_push_subscription (
	 id INT AUTO_INCREMENT PRIMARY KEY,
	 deviceClientId VARCHAR(40) NOT NULL,
	 createdBy INT NOT NULL,
	 url TEXT NOT NULL,
	 p256dh VARCHAR(255) NOT NULL,
	 auth VARCHAR(255) NOT NULL,
	 expires DATETIME NOT NULL,
	 types VARCHAR(512) NOT NULL,
	 verificationCode VARCHAR(20) NULL,
	 CONSTRAINT `core_push_subscriptions_core_user_fk1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
);