<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */


namespace GO\Summary\Controller;


class NoteController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Summary\Model\Note';
	

	protected function getModelFromParams($params) {
		$model = \GO\Summary\Model\Note::model()->findByPk(\GO::user()->id);
		if(!$model){
			$model = new \GO\Summary\Model\Note();
			$model->save();
		}
		
		return $model;
	}
	
	
}

