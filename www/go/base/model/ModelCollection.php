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
 * A collection of models. Finds models and indexes them by their primary key
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model 
 */


namespace GO\Base\Model;


class ModelCollection{
	
	protected $_models;
	/**
	 *
	 * @var \GO\Base\Db\ActiveRecord 
	 */
	protected $model;
	
	public function __construct($model){
		$this->model = call_user_func(array($model,'model'));		
	}
	
	public function __get($name){
		try{
			$model =  $this->model->findByPk($name);
		}catch(\GO\Base\Exception\AccessDenied $e){
			return false;
		}
		
		return $model;
	}
	
	public function __isset($name){
		try{
			return $this->model->findByPk($name)!==false;
		}catch(\GO\Base\Exception\AccessDenied $e){
			return false;
		}
	}
	/**
	 * Query all modules.
	 * 
	 * @return \GO\Base\Db\ActiveStatement 
	 */
	public function getAll(){
		return $this->model->find();
	}
}
