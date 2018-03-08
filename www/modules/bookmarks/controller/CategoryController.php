<?php

namespace GO\Bookmarks\Controller;


class CategoryController extends \GO\Base\Controller\AbstractModelController{

	protected $model ='GO\Bookmarks\Model\Category';
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name', '$model->user->name');
		return parent::formatColumns($columnModel);
	}
	protected function getStoreParams($params) {
		return array(
				'order' => 'name',
				'orderDirection' => 'ASC'
		);
	}

}