<?php

/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * The GO\Files\Model\Template controller
 *
 * @package GO.modules.files
 * @FolderPreference $Id: GO\Files\Model\Template.php 7607 2011-09-29 08:42:37Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\files\Controller;


class FolderPreferenceController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Files\Model\FolderPreference';
	
	/**
	 * Can be overriden if you have a primary key that's not 'id' or is an array.
	 * 
	 * @param array $params
	 * @return mixed 
	 */
	protected function getPrimaryKeyFromParams($params){	
		return empty($params['folder_id']) ? false : array('folder_id'=>$params['folder_id'],'user_id'=>\GO::user()->id);
	}
	
	protected function getModelFromParams($params){
		$modelName = $this->model;
		$model=false;
		$pk = $this->getPrimaryKeyFromParams($params);
		if(!empty($pk))
			$model = \GO::getModel($modelName)->findByPk($pk);
			
		if(!$model){				
			$model = new $modelName;
			$model->setAttributes($params);
		}	
		
		return $model;
	}
}
