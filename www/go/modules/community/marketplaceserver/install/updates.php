<?php

// The marketplace server's schema is defined in install.sql.
$updates = [];

// RateLimiter counts attempts per bucket inside a sliding window
// (`WHERE ip = ? AND createdAt >= ?`, same for email), which the single-column
// keys could not serve: the email lookup had no index at all and scanned the
// table on every registration/login attempt. Composite keys cover both; the
// lone createdAt key stays for prune()'s range delete.
$updates["202609211200"][] = "ALTER TABLE `marketplaceserver_reg_attempt` "
    . "DROP KEY `ip`, "
    . "ADD KEY `ip` (`ip`,`createdAt`), "
    . "ADD KEY `email` (`email`,`createdAt`)";
