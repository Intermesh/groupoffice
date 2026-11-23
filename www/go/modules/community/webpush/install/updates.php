<?php

// Alerts were marked as sent with core_alert.isSent, which the e-mail alerts use too. Pushed alerts are now recorded here.
$updates['202611231800'][] = "CREATE TABLE IF NOT EXISTS webpush_pushed_alert (
	alertId INT NOT NULL PRIMARY KEY,
	CONSTRAINT `webpush_pushed_alert_core_alert_fk1` FOREIGN KEY (`alertId`) REFERENCES `core_alert` (`id`) ON DELETE CASCADE
);";
