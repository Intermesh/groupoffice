CREATE TABLE `apikeys_key` (
  `id` int(11) NOT NULL,
  `accessToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB;



--
-- Indexes for dumped tables
--

--
-- Indexes for table `apikeys_key`
--
ALTER TABLE `apikeys_key`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apikeys_key`
--
ALTER TABLE `apikeys_key`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `apikeys_key` ADD FOREIGN KEY (`accessToken`) REFERENCES `core_auth_token`(`accessToken`) ON DELETE RESTRICT ON UPDATE RESTRICT;

