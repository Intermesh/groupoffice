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
		return 'acl_id';
	}

	public function tableName() {
		return 'go_search_cache';
	}

	public function primaryKey(){
		return array('model_id', 'model_type_id');					
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
		
		$params = \GO\Base\Db\FindParams::newInstance()
						->select("t.*,l.description AS link_description")
						->order('mtime','DESC')
						->join('go_links_'.$model->tableName(),  \GO\Base\Db\FindCriteria::newInstance()
										->ignoreUnknownColumns() //we don't have models for go_links_* tables
										->addCondition('id', $model->id,'=','l')
										->addCondition('model_id', 'l.model_id','=','t',true, true)
										->addCondition('model_type_id', 'l.model_type_id','=','t',true, true),
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
		return class_exists($this->model_name) ? GO::getModel($this->model_name)->countLinks($model_id) :0;
	}

}
