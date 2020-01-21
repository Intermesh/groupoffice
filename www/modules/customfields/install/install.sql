--
-- Tabelstructuur voor tabel `cf_categories`
--

DROP TABLE IF EXISTS `cf_categories`;
CREATE TABLE IF NOT EXISTS `cf_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extends_model` varchar(100) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `sort_index` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`extends_model`)
) ENGINE=InnoDB;



--
-- Tabelstructuur voor tabel `cf_disable_categories`
--

DROP TABLE IF EXISTS `cf_disable_categories`;
CREATE TABLE IF NOT EXISTS `cf_disable_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cf_enabled_categories`
--

DROP TABLE IF EXISTS `cf_enabled_categories`;
CREATE TABLE IF NOT EXISTS `cf_enabled_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`,`category_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cf_fields`
--

DROP TABLE IF EXISTS `cf_fields`;
CREATE TABLE IF NOT EXISTS `cf_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `name` VARCHAR( 255 ) NOT NULL,
  `datatype` varchar(100) NOT NULL DEFAULT 'GO_Customfields_Customfieldtype_Text',
  `sort_index` int(11) NOT NULL DEFAULT '0',
  `function` varchar(255) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `required_condition` varchar(255) NOT NULL DEFAULT '',
  `validation_regex` varchar(255) NOT NULL DEFAULT '',
  `helptext` varchar(100) NOT NULL DEFAULT '',
  `multiselect` tinyint(1) NOT NULL DEFAULT '0',
  `max` int(11) NOT NULL DEFAULT '0',
  `nesting_level` tinyint(4) NOT NULL DEFAULT '0',
  `treemaster_field_id` int(11) NOT NULL DEFAULT '0',
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
	`number_decimals` tinyint(4) NOT NULL DEFAULT '2',
	`unique_values` tinyint(1) NOT NULL DEFAULT '0',
	`max_length` INT( 5 ) NOT NULL DEFAULT '50',
	`addressbook_ids` VARCHAR(255) NOT NULL DEFAULT '',
	`extra_options` TEXT,
	`prefix` VARCHAR(32) NOT NULL DEFAULT '',
	`suffix` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`category_id`)
) ENGINE=InnoDB;


--
-- Tabelstructuur voor tabel `cf_select_options`
--

DROP TABLE IF EXISTS `cf_select_options`;
CREATE TABLE IF NOT EXISTS `cf_select_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cf_select_tree_options`
--

DROP TABLE IF EXISTS `cf_select_tree_options`;
CREATE TABLE IF NOT EXISTS `cf_select_tree_options` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cf_tree_select_options`
--

DROP TABLE IF EXISTS `cf_tree_select_options`;
CREATE TABLE IF NOT EXISTS `cf_tree_select_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `cf_blocks`;
CREATE TABLE IF NOT EXISTS `cf_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
	`field_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `cf_enabled_blocks`;
CREATE TABLE IF NOT EXISTS `cf_enabled_blocks` (
	`block_id` int(11) NOT NULL DEFAULT 0,
	`model_id` int(11) NOT NULL DEFAULT 0,
  `model_type_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`block_id`,`model_id`,`model_type_name`)
) ENGINE=InnoDB;