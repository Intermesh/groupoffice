<?php
$updates["201202020834"][]="ALTER TABLE `smi_pkcs12` CHANGE `cert` `cert` MEDIUMBLOB NULL";
$updates["201203160943"][]="ALTER TABLE `smi_certs` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";


$updates['201610281650'][] = 'ALTER TABLE `smi_certs` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `smi_certs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `smi_pkcs12` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `smi_pkcs12` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
