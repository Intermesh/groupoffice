-- --------------------------------------------------------

--
-- Table structure for table `fav_calendar`
--

CREATE TABLE IF NOT EXISTS `fav_calendar` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT  '0'
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `fav_tasklist`
--

CREATE TABLE IF NOT EXISTS `fav_tasklist` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT  '0'
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `fav_addressbook`
--

CREATE TABLE IF NOT EXISTS `fav_addressbook` (
  `user_id` int(11) NOT NULL,
  `addressbook_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT  '0'
) ENGINE=InnoDB;