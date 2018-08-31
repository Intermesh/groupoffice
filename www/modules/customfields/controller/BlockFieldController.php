<?php

namespace GO\Customfields\Controller;


class BlockFieldController extends \GO\Base\Controller\AbstractJsonController{

	protected function actionSelectStore($params) {
		
		$columnModel = new \GO\Base\Data\ColumnModel(\GO\Customfields\Model\Field::model());
		$columnModel->formatColumn('extendsModel', '$model->category->getExtendsModel()', array(), 'category_id');
		$columnModel->formatColumn('full_info','"[".\GO::t($model->category->extendsModel,"customfields")."] ".$model->category->name." : ".$model->name." (".$model->databaseName.")"', array(), 'category_id');
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
			->joinModel(array(
				'model'=>'GO\Customfields\Model\Category',
				'localTableAlias'=>'t',
				'localField'=>'fieldSetId',
				'foreignField'=>'id',
				'tableAlias'=>'c'
			))
			->join('core_entity', \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('ce.id', 'c.entityId'), 'ce')
			->criteria(
				\GO\Base\Db\FindCriteria::newInstance()
					->addInCondition(
						'name',
						array(
							'Contact',
							'Company',
							'Project',
							'User'
						),
						'ce'
					)
					->addInCondition(
						'datatype',
						array(
							'GO\Addressbook\Customfieldtype\Contact',
							'GO\Addressbook\Customfieldtype\Company'
						),
						't'
					)
			);
		
		$store = new \GO\Base\Data\DbStore('GO\Customfields\Model\Field', $columnModel, $params, $findParams);

		echo $this->renderStore($store);
		
	}
	
}
