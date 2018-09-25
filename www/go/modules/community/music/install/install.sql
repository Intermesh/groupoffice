CREATE TABLE `music_album` (
		`id` int(11) NOT NULL,
		`artistId` int(11) NOT NULL,
		`name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
		`releaseDate` date NOT NULL,
		`genreId` int(11) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

	CREATE TABLE `music_artist` (
		`id` int(11) NOT NULL,
		`name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
		`photo` binary(40) DEFAULT NULL,
		`createdAt` datetime NOT NULL,
		`modifiedAt` datetime NOT NULL,
		`createdBy` int(11) NOT NULL,
		`modifiedBy` int(11) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

	
	CREATE TABLE `music_genre` (
		`id` int(11) NOT NULL,
		`name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

	INSERT INTO `music_genre` (`id`, `name`) VALUES
	(1, 'Pop'),
	(2, 'Rock'),
	(3, 'Blues'),
	(4, 'Jazz');


	ALTER TABLE `music_album`
		ADD PRIMARY KEY (`id`),
		ADD KEY `artistId` (`artistId`),
		ADD KEY `genreId` (`genreId`);

	ALTER TABLE `music_artist`
		ADD PRIMARY KEY (`id`),
		ADD KEY `photo` (`photo`);

	ALTER TABLE `music_genre`
		ADD PRIMARY KEY (`id`);


	ALTER TABLE `music_album`
		MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

	ALTER TABLE `music_artist`
		MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

	ALTER TABLE `music_genre`
		MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


	ALTER TABLE `music_album`
		ADD CONSTRAINT `music_album_ibfk_1` FOREIGN KEY (`artistId`) REFERENCES `music_artist` (`id`) ON DELETE CASCADE,
		ADD CONSTRAINT `music_album_ibfk_2` FOREIGN KEY (`genreId`) REFERENCES `music_genre` (`id`);

	ALTER TABLE `music_artist`
		ADD CONSTRAINT `music_artist_ibfk_1` FOREIGN KEY (`photo`) REFERENCES `core_blob` (`id`);