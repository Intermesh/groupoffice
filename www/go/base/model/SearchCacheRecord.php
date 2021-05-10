<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * The searchcache model
 *
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model
 * @property int $acl_id
 * @property int $mtime
 * @property string $keywords
 * @property string $model_name
 * @property int $model_type_id
 * @property string $description
 * @property string $name
 * @property string $module
 * @property int $model_id
 * @property int $user_id
 * @property string $url
 * @property string $table
 */


namespace GO\Base\Model;
use GO;

class SearchCacheRecord extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 *
	 * @param String $className
	 * @return SearchCacheRecord
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	public function aclField() {
		return 'aclId';
	}

	public function tableName() {
		return 'core_search';
	}

	public function primaryKey(){
		return array('entityId', 'id');
	}

	public function checkDatabaseSupported() {
		return false;
	}

	/**
	 * Find all links of this model type to a given model.
	 *
	 * @param type $model
	 * @param type $findParams
	 * @return type
	 */
	public function findLinks($model, $findParams=array()){

		if($model instanceof \GO\Base\Model\SearchCacheRecord) {
			$entityId = $model->entityId;
			$entityTypeId = $model->entityTypeId;
		} else
		{
			$entityId = $model->id;
			$entityTypeId = $model->entityType()->getId();
		}


		$params = \GO\Base\Db\FindParams::newInstance()
						->select("t.*,l.description AS link_description")
						->order('modifiedAt','DESC')
						->join('core_link',  \GO\Base\Db\FindCriteria::newInstance()
										->ignoreUnknownColumns() //we don't have models for go_links_* tables
										->addCondition('fromId', $entityId,'=','l')
										->addCondition('fromEntityTypeId', $entityTypeId,'=','l')
										->addCondition('entityId', 'l.toId','=','t',true, true)
										->addCondition('entityTypeId', 'l.toEntityTypeId','=','t',true, true),
										'l')
						->mergeWith($findParams);

		return $this->find($params);
	}


	public function getFindSearchQueryParamFields($prefixTable = 't', $withCustomFields = true) {
		return array('t.keywords');

		//keywords are matched with fulltext index
	}

	/**
	 * Set this to true so it won't be deleted.
	 * @var type
	 */
	public $isJoinedAclField = true;


	public function countLinks($model_id = 0) {

		$model_name = \go\core\orm\EntityType::findById($this->entityTypeId)->getClassName();

		return class_exists($model_name) ? GO::getModel($model_name)->countLinks($model_id) :0;
	}

	public function getModel_name() {
		$e = \go\core\orm\EntityType::findById($this->entityTypeId);
		if(!$e){
			return null;
		}
		return $e->getClassName();
	}

	public function getModel_id() {
		return $this->entityId;
	}

	public function getAttributes($outputType = null) {
		$attr = parent::getAttributes($outputType);
		$attr['model_id'] = $this->entityId;
		$attr['model_name'] = $this->getModel_name();

		return $attr;
	}

}
