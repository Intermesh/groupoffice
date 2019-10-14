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
 * @package GO.modules.comments.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Comment model
 *
 * @package GO.modules.comments.model
 * @property string $comments
 * @property int $mtime
 * @property int $ctime
 * @property int $user_id
 * @property int $model_type_id
 * @property int $model_id
 * @property int $id
 * @property int $category_id
 */


namespace GO\Comments\Model;


class Comment extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Comment 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {
		$this->columns['model_id']['required']=true;
		$this->columns['model_type_id']['required']=true;
		$this->columns['category_id']['required']=\GO\Comments\CommentsModule::commentsRequired();
		
		return parent::init();
	}
	
	protected function getCacheAttributes() {
		
		if(!$this->getAttachedObject()){
			return false;
		}
		
		return array(
				'name' => empty($this->comments)?\GO::t("Empty comment", "comments"):\GO\Base\Util\StringHelper::cut_string($this->comments, 100),
				'description'=>'',
				'aclId' => $this->getAttachedObject()->findAclId()
		);
	}
	
	public function tableName(){
		return 'co_comments';
	}
	
	public function relations(){
		return array(	
			'category' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Comments\Model\Category', 'field'=>'category_id'),		);
	}
	
	public function getAttachedObject(){
		
		
		
		$modelType = \GO\Base\Model\ModelType::model()->findByPk($this->model_type_id);
		
		
		
		
		
		if($modelType){
			$model_name = $modelType->model_name;
			
			if(is_a($model_name, \go\core\orm\Entity::class, true)) {
				return $model_name::findById($this->model_id);
			}
			
			$obj = \GO::getModel($model_name)->findByPk($this->model_id);
			
			if($obj)
				return $obj;
		}
		
		return false;
	}
	
	
}
