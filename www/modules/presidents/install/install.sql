CREATE TABLE IF NOT EXISTS `pm_parties` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`)
);

INSERT INTO `pm_parties` (`id`, `name`) VALUES
(1, 'No Party'),
(2, 'Federalist'),
(3, 'Democratic-Republican'),
(4, 'Democratic'),
(5, 'Whig'),
(6, 'Republican');

CREATE TABLE IF NOT EXISTS `pm_presidents` (
  `id` int(11) NOT NULL auto_increment,
  `party_id` int(11) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(20)  NOT NULL,
  `tookoffice` date NOT NULL,
  `leftoffice` date NOT NULL,
  `income` decimal(14,2) NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
);

INSERT INTO `pm_presidents` (`id`, `party_id`, `firstname`, `lastname`, `tookoffice`, `leftoffice`, `income`) VALUES
(1, 1, 'George', 'Washington', '1789-04-30', '1797-03-04', 135246.32),
(2, 2, 'John', 'Adams', '1797-03-04', '1801-03-04', 236453.34),
(3, 3, 'Thomas', 'Jefferson', '1801-03-04', '1809-03-04', 468043.25),
(4, 3, 'James', 'Madison', '1809-03-04', '1817-03-04', 649273.00),
(5, 3, 'James', 'Monroe', '1817-03-04', '1825-03-04', 374937.23),
(6, 3, 'John', 'Quincy Adams', '1825-03-04', '1829-03-04', 649824.35),
(7, 4, 'Andrew', 'Jackson', '1829-03-04', '1837-03-04', 3972753.12),
(8, 4, 'Martin', 'Van Buren', '1837-03-04', '1841-03-04', 325973.24),
(9, 5, 'William', 'Harrison', '1841-03-04', '1841-04-04', 25532.08),
(10, 5, 'John', 'Tyler', '1841-04-04', '1845-03-04', 235542.35),
(11, 4, 'James', 'Polk', '1845-03-04', '1849-03-04', 3264972.35),
(12, 5, 'Zachary', 'Taylor', '1849-03-04', '1850-07-09', 35974.35),
(13, 5, 'Millard', 'Fillmore', '1850-07-09', '1853-03-04', 35792.45),
(14, 4, 'Franklin', 'Pierce', '1853-03-04', '1857-03-04', 357938.35),
(15, 4, 'James', 'Buchanan', '1857-03-04', '1861-03-04', 357937.35),
(16, 6, 'Abraham', 'Lincoln', '1861-03-04', '1865-04-15', 25073.40),
(17, 4, 'Andrew', 'Johnson', '1865-04-15', '1869-03-04', 356279.35),
(18, 6, 'Ulysses', 'Grant', '1869-03-04', '1877-03-04', 357938.35),
(19, 6, 'Rutherford', 'Hayes', '1877-03-04', '1881-03-04', 2359737.35),
(20, 6, 'James', 'Garfield', '1881-03-04', '1881-09-19', 3579.24),
(21, 6, 'Chester', 'Arthur', '1881-09-19', '1995-03-04', 357932.35),
(22, 4, 'Grover', 'Cleveland', '1885-03-04', '1889-03-04', 369723.35),
(23, 6, 'Benjamin', 'Harrison', '1889-03-04', '1893-03-04', 357392.35),
(24, 4, 'Grover', 'Cleveland', '1893-03-04', '1897-03-04', 3275935.35),
(25, 6, 'William', 'McKinley', '1897-03-04', '1901-09-14', 35793.35),
(26, 6, 'Theodore', 'Roosevelt', '1901-09-14', '1909-03-04', 0.00),
(27, 6, 'William', 'Taft', '1909-03-04', '1913-03-04', 239597.35),
(28, 4, 'Woodrow', 'Wilson', '1913-03-04', '1921-03-04', 32579743.34),
(29, 6, 'Warren', 'Harding', '1921-03-04', '1923-08-02', 35793.24),
(30, 6, 'Calvin', 'Coolidge', '1923-08-02', '1929-03-04', 296529.24),
(31, 6, 'Herbert', 'Hoover', '1929-03-04', '1933-03-04', 3525.25),
(32, 4, 'Franklin', 'Roosevelt', '1933-03-04', '1945-04-12', 35293734.35),
(33, 4, 'Harry', 'Truman', '1945-04-12', '1953-01-20', 23579358.35),
(34, 6, 'Dwight', 'Eisenhower', '1953-01-20', '1961-01-20', 25973535.35),
(35, 4, 'John', 'Kennedy', '1961-01-20', '1963-11-22', 46081.24),
(36, 4, 'Lyndon', 'Johnson', '1963-11-22', '1969-01-20', 2503759.35),
(37, 6, 'Richard', 'Nixon', '1969-01-20', '1974-08-09', 3259744.53),
(38, 6, 'Gerald', 'Ford', '1974-08-09', '1977-01-20', 643076.05),
(39, 4, 'Jimmy', 'Carter', '1977-01-20', '1981-01-20', 1205735.25),
(40, 6, 'Ronald', 'Reagan', '1981-01-20', '1989-01-20', 99867297.35),
(41, 6, 'George H.', 'Bush', '1989-01-20', '1993-01-20', 92048204.24),
(42, 4, 'Bill', 'Clinton', '1993-01-20', '2001-01-20', 12073975.24);


CREATE TABLE IF NOT EXISTS `go_links_pm_presidents` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY `model_id` (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
);

CREATE TABLE IF NOT EXISTS `cf_pm_presidents` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
);