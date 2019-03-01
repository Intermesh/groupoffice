<?php
/**
 * 
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 */

/**
 * All Group-Office models should extend this ActiveRecord class.
 *
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 */


namespace GO\Base\Db;
use GO\Base\Db\PDO;

class Utils{
	
	/**
	 * Check if a database exists
	 * 
	 * @param StringHelper $tableName
	 * @return boolean 
	 */
	public static function databaseExists($databaseName){
		$stmt = \GO::getDbConnection()->query('SHOW DATABASES');
		while($r=$stmt->fetch()){
			if($r[0]==$databaseName){
				return true;
			}		
		}
		
		return false;
	}
	
	/**
	 * Check if a table exists in the Group-Office database.
	 * 
	 * @param StringHelper $tableName
	 * @return boolean 
	 */
	public static function tableExists($tableName){
		$stmt = \GO::getDbConnection()->query('SHOW TABLES');
		while($r=$stmt->fetch()){
			if($r[0]==$tableName){
				$stmt->closeCursor();
				return true;
			}		
		}
		
		return false;
	}
	
	public static function fieldExists($tableName, $fieldName){
		$sql = "SHOW FIELDS FROM `".$tableName."`";
		$stmt = \GO::getDbConnection()->query($sql);
		while($record = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($record['Field']==$fieldName)
				return true;
		}
		return false;
	}
}
