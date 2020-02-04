<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.db
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
use GO;


class Columns{
	
	public static $forceLoad = false;
	
	private static $_columns=array();
	
	private static function getCacheKey(ActiveRecord $model){
		$tableName = $model->tableName();
		return 'modelColumns_'.$tableName;		
	}
	/**
	 * Clear the column cache for a particular model.
	 * 
	 * @param ActiveRecord $model
	 */
	public static function clearCache(ActiveRecord $model){
		\GO::cache()->delete(self::getCacheKey($model));
	}
	
	/**
	 * Get all columns of a model
	 * 
	 * @param ActiveRecord $model
	 * @return array
	 */
	public static function getColumns(ActiveRecord $model) {
		$tableName = $model->tableName();
		$cacheKey = self::getCacheKey($model);
		
		if(self::$forceLoad){
			unset(self::$_columns[$tableName]);
			\GO::cache()->delete($cacheKey);
		}
		
		if(isset(self::$_columns[$tableName]) && !self::$forceLoad){
			return self::$_columns[$tableName];
		}elseif(($columns = \GO::cache()->get($cacheKey))){
//			\GO::debug("Got columns from cache for $tableName");
			self::$_columns[$tableName]=$columns;
			return self::$_columns[$tableName];
		}else
		{	
//			\GO::debug("Loading columns for $tableName");
			self::$_columns[$tableName]=array();
			$sql = "SHOW COLUMNS FROM `" . $tableName. "`;";
			$stmt = $model->getDbConnection()->query($sql);
			while ($field = $stmt->fetch()) {					
				preg_match('/([a-zA-Z].*)\(([1-9].*)\)/', $field['Type'], $matches);
				if ($matches) {
					$length = $matches[2];
					$type = $matches[1];
				} else {
					$type = $field['Type'];
					$length = 0;
				}
				
				$required=false;
				$gotype = 'textfield';
				$default = $field['Default'];
				
				$ai =  strpos($field['Extra'],'auto_increment')!==false;
				
				$pdoType = PDO::PARAM_STR;
				switch ($type) {
					case 'int':
					case 'tinyint':
					case 'bigint':

						$pdoType = PDO::PARAM_INT;
						if($length==1 && $type=='tinyint')
							$gotype='boolean';
						else
							$gotype = '';

						$length = 0;
						
						$default = $ai || !isset($field['Default']) ? null : intval($default);

						break;		

					case 'float':
					case 'double':
					case 'decimal':
						$pdoType = PDO::PARAM_STR;
						$length = 0;
						$gotype = 'number';
						$default = $default==null ? null : floatval($default);
						break;

					case 'mediumtext':
						$length = 16777215 ;
						$gotype = 'textarea';
						break;
					case 'longtext':
						$gotype = 'textarea';
						break;
					case 'text':
						$length = 65535;
						$gotype = 'textarea';
						break;

					case 'mediumblob':
					case 'longblob':
					case 'blob':
						$gotype = 'blob';
						break;

					case 'date':
						$gotype='date';
						break;
					case 'datetime':
						$gotype='datetime';
						break;
					case 'time':
						$gotype='time';
						break;
				}

				switch($field['Field']){
					case 'ctime':
					case 'mtime':
						$gotype = 'unixtimestamp';			
						break;
					case 'name':
						$required=true;							
						break;
					case 'user_id':
					case 'muser_id':
						$gotype = 'user';
						break;
					
				}

				

				//HACK: When a database may not be null and has no default value value is empty string
				if(!GO::config()->debug){
					if($field['Null']=='NO' && is_null($default) && !$ai)
						$default='';
				}
				
				//workaround for old boolean fields as enums. Should be using bool now.
				
				if($type=="enum('0','1')"){
					$gotype='boolean';
					$default='0';
				}
				

				//$required = is_null($default) && $field['Null']=='NO' && strpos($field['Extra'],'auto_increment')===false;

				self::$_columns[$tableName][$field['Field']]=array(
						'type'=>$pdoType,
						'required'=>$required,
						'length'=>$length,
						'gotype'=>$gotype,
						'default'=>$default,
						'dbtype'=>$type,
						'null'=>$field['Null']=='YES'
				);

			}
			
			\GO::cache()->set($cacheKey, self::$_columns[$tableName]);
			
			return self::$_columns[$tableName];			
		}		
	}
}
