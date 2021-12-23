<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Gets SQL queries from an SQL dump file
 *  
 * @copyright Copyright Intermesh
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util
 */

namespace GO\Base\Util;


class SQL {

	/**
	 * Get's all queries from an SQL dump file in an array
	 *
	 * @param	StringHelper $file The absolute path to the SQL file
	 * @access public
	 * @return array An array of SQL strings
	 */
	public static function getSqlQueries($file) {
		$sql = '';
		$queries = array();
		if ($handle = fopen($file, "r")) {
			while (!feof($handle)) {
				$buffer = trim(fgets($handle, 4096));
				if ($buffer != '' && substr($buffer, 0, 1) != '#' && substr($buffer, 0, 1) != '-') {
					$sql .= $buffer."\n";
				}
			}
			fclose($handle);
		} else {
			die("Could not read SQL dump file $file!");
		}
		$length = strlen($sql);
		$in_string = false;
		$start = 0;
		$escaped = false;
		for ($i = 0; $i < $length; $i++) {
			$char = $sql[$i];
			if ($char == '\'' && !$escaped) {
				$in_string = !$in_string;
			}
			if ($char == ';' && !$in_string) {
				$offset = $i - $start;
				$query = substr($sql, $start, $offset);
				if(!empty($query)) {
					$queries[] = $query;
				}

				$start = $i + 1;
			}
			if ($char == '\\') {
				$escaped = true;
			} else {
				$escaped = false;
			}
		}
		return $queries;
	}
	
	public static function executeSqlFile($file){
		$queries = self::getSqlQueries($file);
		try{
			foreach($queries as $query)
				\GO::getDbConnection ()->query($query);
		}catch(\Exception $e){
			throw new \Exception("Could not execute query: ".$query."\n\n".(string) $e);
		}
		
		return true;
	}

}
