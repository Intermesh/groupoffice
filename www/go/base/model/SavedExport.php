<?php


namespace GO\Base\Model;

use GO\Base\Db\ActiveRecord;


/**
 * 
 * The SavedExport model
 * 
 * @property int $id
 * @property String $name
 * @property String $class_name
 * @property String $view
 * @property String $export_columns
 * @property String $orientation (V or H)
 * @property boolean $include_column_names
 * @property boolean $use_db_column_names
 *
 */
class SavedExport extends ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'go_saved_exports';
	}
	
}
