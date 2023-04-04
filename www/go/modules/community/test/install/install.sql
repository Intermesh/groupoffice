

CREATE TABLE `test_a` (
  `id` int(11) NOT NULL,
  `propA` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `test_a_has_many`
--

CREATE TABLE `test_a_has_many` (
  `id` int(11) NOT NULL,
  `aId` int(11) NOT NULL,
  `propOfHasManyA` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `test_a_has_one`
--

CREATE TABLE `test_a_has_one` (
  `id` int(11) NOT NULL,
  `aId` int(11) NOT NULL,
  `propA` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `test_b`
--

CREATE TABLE `test_b` (
  `id` int(11) NOT NULL,
  `propB` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cId` int(11) DEFAULT NULL,
  `userId` int(11) NOT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `test_c`
--

CREATE TABLE `test_c` (
  `id` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `test_a`
--
ALTER TABLE `test_a`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_a_has_many`
--
ALTER TABLE `test_a_has_many`
  ADD PRIMARY KEY (`id`,`aId`),
  ADD KEY `aId` (`aId`);

--
-- Indexes for table `test_a_has_one`
--
ALTER TABLE `test_a_has_one`
  ADD PRIMARY KEY (`id`,`aId`),
  ADD KEY `aId` (`aId`);

--
-- Indexes for table `test_b`
--
ALTER TABLE `test_b`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cId` (`cId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `test_c`
--
ALTER TABLE `test_c`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `test_a`
--
ALTER TABLE `test_a`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `test_a_has_many`
--
ALTER TABLE `test_a_has_many`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `test_a_has_one`
--
ALTER TABLE `test_a_has_one`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_c`
--
ALTER TABLE `test_c`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `test_a_has_many`
--
ALTER TABLE `test_a_has_many`
  ADD CONSTRAINT `test_a_has_many_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_a_has_one`
--
ALTER TABLE `test_a_has_one`
  ADD CONSTRAINT `test_a_has_one_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_b`
--
ALTER TABLE `test_b`
  ADD CONSTRAINT `test_b_ibfk_1` FOREIGN KEY (`id`) REFERENCES `test_a` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_b_ibfk_2` FOREIGN KEY (`cId`) REFERENCES `test_c` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `test_b_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;


CREATE TABLE `test_d` (
  `id` int(11) NOT NULL,
  `propD` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `test_d`
--
ALTER TABLE `test_d`
  ADD PRIMARY KEY (`id`);



CREATE TABLE `test_a_map` (
  `aId` int(11) NOT NULL,
  `anotherAId` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `test_a_map`
  ADD PRIMARY KEY (`aId`,`anotherAId`),
  ADD KEY `anotherAId` (`anotherAId`);


ALTER TABLE `test_a_map`
  ADD CONSTRAINT `test_a_map_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_a_map_ibfk_2` FOREIGN KEY (`anotherAId`) REFERENCES `test_a` (`id`);
