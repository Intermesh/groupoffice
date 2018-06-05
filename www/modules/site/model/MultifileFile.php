<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Site
 * @version $Id: MultifileFile.php 7607 2013-04-16 09:44:35Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The MultifileFile model
 *
 * @package GO.modules.Site
 * @version $Id: MultifileFile.php 7607 2013-04-16 09:44:35Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $model_id
 * @property int $field_id
 * @property int $file_id
 * @property int $order
 */


namespace GO\Site\Model;


class MultifileFile extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'site_multifile_files';
	 }
	 
	 public function primaryKey() {
		 return array('model_id','field_id','file_id');
	 }
	 
	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
				'file'=>array('type'=>self::BELONGS_TO, 'model'=>"GO\Files\Model\File", 'field'=>'file_id')
		 );
	 }
	 
	/**
	 * Add a file from the fileSelector
	 * 
	 * @param int $fileId
	 * @param int $modelId
	 * @param int $fieldId
	 * @return boolean
	 */
	public static function addFromFileSelector($fileId, $modelId, $fieldId) {

		if (empty($fileId) || empty($modelId) || empty($fieldId))
			return false;

		$file = MultifileFile::model()->findByPk(array(
			'model_id' => $modelId,
			'field_id' => $fieldId,
			'file_id' => $fileId
		));

		if (!$file)
			$file = new MultifileFile();

		$file->file_id = $fileId;
		$file->model_id = $modelId;
		$file->field_id = $fieldId;

		return $file->save();
	}
	 
	 /**
	  * Delete a file from the fileSelector
	  * 
	  * @param int $fileId
	  * @param int $modelId
	  * @param int $fieldId
	  * @return boolean
	  */
	 public static function deleteFromFileSelector($fileId,$modelId,$fieldId){
		 
		 if(empty($fileId) || empty($modelId) || empty($fieldId))
			 return false;
		 
		 $file = MultifileFile::model()->findByPk(array(
				'model_id'=>$modelId,
				'field_id'=>$fieldId,
				'file_id'=>$fileId
			));
		 
		 if(!$file)
			 return false;

		 return $file->delete();
	 }
	 
}
