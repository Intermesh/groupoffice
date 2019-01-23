<?php

$updates["201401101440"][]="CREATE TABLE IF NOT EXISTS `site_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `menu_slug` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

$updates["201401101441"][]="CREATE TABLE IF NOT EXISTS `site_menu_item` (
  `menu_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `display_children` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `target` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";


$updates["201403101413"][]="ALTER TABLE  `site_content` ADD  `content_type` VARCHAR( 20 ) NOT NULL DEFAULT  'markdown';";
$updates["201403101413"][]="UPDATE  `site_content` SET  `content_type` ='html';";



$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `cf_site_content` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_site_content` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_site_sites` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_site_sites` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';


$updates['201810161450'][] = "ALTER TABLE `cf_site_content` CHANGE `model_id` `id` INT(11) NOT NULL;";
$updates['201810161450'][] = "RENAME TABLE `cf_site_content` TO `site_content_custom_fields`;";
$updates['201810161450'][] = "delete from site_content_custom_fields where id not in (select id from site_content);";
$updates['201810161450'][] = "ALTER TABLE `site_content_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `site_content`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$updates['201810161450'][] = "ALTER TABLE `cf_site_sites` CHANGE `model_id` `id` INT(11) NOT NULL;";
$updates['201810161450'][] = "RENAME TABLE `cf_site_sites` TO `site_sites_custom_fields`;";
$updates['201810161450'][] = "delete from site_sites_custom_fields where id not in (select id from site_sites);";
$updates['201810161450'][] = "ALTER TABLE `site_sites_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `site_sites`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
