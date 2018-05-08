--
-- Table structure for table `multi_instance_instance`
--

CREATE TABLE `multi_instance_instance` (
  `id` int(11) NOT NULL,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `multi_instance_instance`
--
ALTER TABLE `multi_instance_instance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hostname` (`hostname`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `multi_instance_instance`
--
ALTER TABLE `multi_instance_instance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

