CREATE TABLE if not exists core_push_subscription (
	 id INT AUTO_INCREMENT PRIMARY KEY,
	 deviceClientId VARCHAR(40) NOT NULL,
	 userId INT NOT NULL,
	 url TEXT NOT NULL,
	 p256dh VARCHAR(255) NOT NULL,
	 auth VARCHAR(255) NOT NULL,
	 expires DATETIME NOT NULL,
	 verificationCode VARCHAR(20) NULL,
	 CONSTRAINT `core_push_subscriptions_core_user_fk1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
);

-- The alerts that were pushed, so they are pushed once. Apart from core_alert.isSent, which is for the e-mail alerts.
CREATE TABLE IF NOT EXISTS webpush_pushed_alert (
	alertId INT NOT NULL PRIMARY KEY,
	CONSTRAINT `webpush_pushed_alert_core_alert_fk1` FOREIGN KEY (`alertId`) REFERENCES `core_alert` (`id`) ON DELETE CASCADE
);
