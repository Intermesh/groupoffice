<?php


$counter = 201610281650;
//16_update_db_collation_utf8_general_ci_to_utf8mb4_unicode_ci


//https://confluence.atlassian.com/jirakb/how-to-change-all-columns-and-tables-collation-to-utf8_bin-in-mysql-601456761.html
//https://confluence.atlassian.com/confkb/mysql-collation-repair-column-level-changes-670958189.html


//ALTER TABLE $value CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci

//try {
		
	
	\GO::getDbConnection()->query("SET sql_mode = '';");
	
	// set table to ROW_FORMAT COMPACT!
	// test script old DBs 5.5<
//	$q = "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE';";
//	
//	$stmtTebles = \GO::getDbConnection()->query($q);
//	
//	foreach ($stmtTebles->fetchAll() as $tebles) {
//		
//		$q = "ALTER TABLE ". $tebles['Tables_in_'. \GO::config()->db_name] ." ROW_FORMAT=COMPACT;";
//			echo $q."\n";
//			\GO::getDbConnection()->query($q);
//	}
//	
	
		// update DB
		$q = "ALTER DATABASE `". \GO::config()->db_name ."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
//		$q = "ALTER DATABASE ". \GO::config()->db_name ." CHARACTER SET utf8 COLLATE utf8_general_ci;";
//		echo '$updates[\''. $counter .'\'][] = \''. $q.'\';'."\n";
		echo $q."\n";
		\GO::getDbConnection()->query($q);
		
		$q = "SET foreign_key_checks = 0;";
//		echo $q."\n";
		\GO::getDbConnection()->query($q);
//		echo '$updates[\''. $counter .'\'][] = \''. $q.'\';'."\n";
		echo $q."\n";
		
		// get all de table whitout the views
//		$q = "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE';";
		$q = "SHOW TABLE STATUS  WHERE Comment != 'VIEW' AND INSTR(`Collation`, 'utf8mb4_') != 1;";
		echo $q."\n";
		$stmtTebles = \GO::getDbConnection()->query($q);
//		
		foreach ($stmtTebles->fetchAll() as $tebles) {
			
//			$tableName = $tebles['Tables_in_'. \GO::config()->db_name];
			$tableName = $tebles['Name'];
			
			if($tableName !='fs_filesearch' && $tableName !='cms_files'){//filesearch requires fulltext index
				$q = "ALTER TABLE `".$tableName ."` ENGINE=InnoDB;";
//				echo '$updates[\''. $counter .'\'][] = \''. $q.'\';'."\n";
				echo $q."\n";
				GO::getDbConnection()->query($q);
			}
			
			/**
			 * Go fix key size problem by non ROW_FORMAT dynamic tables
			 */

			// Get all the index's from a table
			$q = "SHOW INDEX FROM `". $tableName ."`;";

			//echo '$updates[\''. $counter .'\'][] = \''. $q."\'\n";
			$stmtIndex = \GO::getDbConnection()->query($q);

			foreach ($stmtIndex->fetchAll() as $indexColum) {
				
				// Get the colum info for each index
				$q = "SHOW COLUMNS FROM  `".$tableName ."` WHERE `Field` = '". $indexColum['Column_name'] ."';";
				//echo '$updates[\''. $counter .'\'][] = \''. $q."\'\n";
				echo $q."\n";
				$stmtColumns = \GO::getDbConnection()->query($q);

				$columnData = $stmtColumns->fetch();


				// check if it is a varchar en length is more then 190 then edit this to 190
				if(strpos($columnData['Type'], 'varchar') ===0) {

					$rowString = str_replace('varchar(', '', $columnData['Type']);
					$length = str_replace(')', '', $rowString);

					if($length > 190) {

						// update VARCHAR levgth to max 190
						$q = "ALTER TABLE `". $tableName ."` CHANGE `". $columnData['Field'] ."` `". $columnData['Field'] ."` VARCHAR(190);";
//						echo '$updates[\''. $counter .'\'][] = \''. $q.'\';'."\n";
						echo $q."\n";
						\GO::getDbConnection()->query($q);
						
						
					}		
					
				}


			}
		
		
		
			
//			$q = "SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY `', column_name, '` ', DATA_TYPE, ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', (CASE WHEN IS_NULLABLE = 'NO' THEN ' NOT NULL' ELSE '' END), ';') AS q
//				FROM information_schema.COLUMNS 
//				WHERE TABLE_SCHEMA = '". \GO::config()->db_name ."'
//				AND TABLE_NAME = '". $tebles['Tables_in_'. \GO::config()->db_name] ."'	
//				AND DATA_TYPE != 'varchar'
//				AND CHARACTER_SET_NAME != 'utf8'
//				AND (DATA_TYPE = 'mediumtext' OR DATA_TYPE = 'text' OR DATA_TYPE = 'longtext');";
//
//			echo $q."\n";
//			$stmtCols = \GO::getDbConnection()->query($q);
			
			
//			foreach ($stmtCols as $qeury) {
//			
//				//ALTER TABLE `ab_companies` MODIFY `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
//
//				echo $qeury['q']."\n";
//				\GO::getDbConnection()->query($qeury['q']);
//			}
			
			
//			// update table to utf8mb4
			$q = "ALTER TABLE `". $tableName ."` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
//			$q = "ALTER TABLE ". $tebles['Tables_in_'. \GO::config()->db_name] ." CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;";
//			echo '$updates[\''. $counter .'\'][] = \''. $q.'\';'."\n";
			echo $q."\n";
			\GO::getDbConnection()->query($q);
//			
		}
		
		
		// select and bild update qeury
//									
//		$q = "SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY `', column_name, '` ', DATA_TYPE, ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', (CASE WHEN IS_NULLABLE = 'NO' THEN ' NOT NULL' ELSE '' END), ';') AS q
//			FROM information_schema.COLUMNS 
//			WHERE TABLE_SCHEMA = '". \GO::config()->db_name ."'
//			AND DATA_TYPE != 'varchar'
//			AND CHARACTER_SET_NAME != 'utf8'
//			AND (DATA_TYPE = 'mediumtext' OR DATA_TYPE = 'text' OR DATA_TYPE = 'longtext');";
//		
//		echo $q."\n";
//		$stmtTeble = \GO::getDbConnection()->query($q);
//		
//		
//		
//		
//		foreach ($stmtTeble as $qeury) {
//			
//			//ALTER TABLE `ab_companies` MODIFY `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
//			
//			echo $qeury['q']."\n";
//			\GO::getDbConnection()->query($qeury['q']);
//		}
		
		$q = "SET foreign_key_checks = 1;";
		echo $q."\n";
		\GO::getDbConnection()->query($q);
//	}catch(\Exception $e) {
//		
//		echo $e->getMessage()."\n";
//	}
		
