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

	public function validate() {
		// The class is instantiated when exporting
		if(!self::isExportClass($this->class_name)) {
			$this->setValidationError('class_name', \GO::t("Invalid export type"));
		}

		return parent::validate();
	}

	/**
	 * Check if the class name given by the client is an export class
	 *
	 * @param string|null $className
	 * @return bool
	 */
	public static function isExportClass(?string $className): bool {
		return !empty($className) && class_exists($className) && is_subclass_of($className, AbstractExport::class);
	}

}
