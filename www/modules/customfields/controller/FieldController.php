<?php


namespace GO\Customfields\Controller;


class FieldController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Customfields\Model\Field';

	protected function actionTypes($params) {

		if(isset($params['extendsModel']))
			$response['results'] = \GO\Customfields\CustomfieldsModule::getCustomfieldTypes($params['extendsModel']);
		else
			$response['results'] = \GO\Customfields\CustomfieldsModule::getCustomfieldTypes();
		$response['success']=true;

		return $response;
	}

	protected function afterLoad(&$response, &$model, &$params) {
		$response['data']['hasLength'] = $model->hasLength();
		return parent::afterLoad($response, $model, $params);
	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
							
		if ($model->datatype == 'GO\Customfields\Customfieldtype\Select') {

			//select_options
			$ids = array();
			if (isset($params['select_options'])) {
				$select_options = json_decode($_POST['select_options'], true);
				for ($i = 0; $i < count($select_options); $i++) {

					if (!empty($select_options[$i]['id'])) {
						$so = \GO\Customfields\Model\FieldSelectOption::model()->findByPk($select_options[$i]['id']);
					} else {
						$so = new \GO\Customfields\Model\FieldSelectOption();
					}
					$so->sort_order = $i;
					$so->field_id = $model->id;
					$so->text = $select_options[$i]['text'];
					$so->save();
					if (empty($select_options[$i]['id'])) {
						$response['new_select_options'][$i] = $so->id;
					}
					$ids[] = $so->id;
				}

				//delete all other field options
				$stmt = \GO\Customfields\Model\FieldSelectOption::model()->find(array(
						'by' => array(
								array('field_id', $model->id),
								array('id', $ids, 'NOT IN'),
						)
								));
				$stmt->callOnEach('delete');
			}
		}

		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}

	protected function actionSubmit($params) {
		
		try {
			return parent::actionSubmit($params);
		} catch (PDOException $e) {
			$msg = $e->getMessage();
			if (strpos($msg,'SQLSTATE[42000]')===0 && strpos($msg,'1118')>14) {
				$catModel = \GO\Customfields\Model\Category::model()->findByPk($params['fieldSetId']);
				throw new \Exception(sprintf(\GO::t("The total amount of data reserved for your custom fields (belonging to object type %s) exceeded the limit. You can correct this by lowering the maximum number of characters of some custom fields. The current custom field was not saved.", "customfields"),  \GO::t($catModel->extendsModel,'customfields')));
			} else if (strpos($msg,'SQLSTATE[42000]')===0 && strpos($msg,'1074')>14) {
				preg_match('/(max = ([0-9]+))/',$msg,$matches);
				$str = !empty($matches[2]) ? $matches[2] : '';
				throw new \Exception(sprintf(\GO::t("The custom field you tried to save, has more than the allowed number of characters (%s). Please decrease the maximum number of characters of this extra field and try to save again.", "customfields"),$str));
			} else {
				throw $e;
			}
		}
		
	}
	
	protected function actionSelectOptions($params) {
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->order('sort_order')->debugSql();
		$findParams->getCriteria()->addCondition('field_id', $params["field_id"]);
		if(isset($params['query'])) {
			$findParams->searchQuery('%'.preg_replace ('/[\s*]+/','%', $params['query']).'%');
		}
		
		$stmt = \GO\Customfields\Model\FieldSelectOption::model()->find($findParams);

		$store = \GO\Base\Data\Store::newInstance(\GO\Customfields\Model\FieldSelectOption::model());
		$store->setStatement($stmt);
		$store->getColumnModel()->formatColumn('text', 'html_entity_decode($model->text)');
		$store->getColumnModel()->getColumn('text')->setModelFormatType('raw');
		return $store->getData();
	}

	protected function actionSaveSort($params) {
		$fields = json_decode($params['fields'], true);
		$sort = 0;
		foreach ($fields as $field) {
			$model = \GO\Customfields\Model\Field::model()->findByPk($field['id']);
			$model->sortOrder = $sort;
			$model->fieldSetId = $field['fieldSetId'];
			$model->save();
			$sort++;
		}

		return array('success' => true);
	}

	protected function getStoreParams($params) {
//		return array(
//				'where' => 'category.extendsModel=:extendsModel',
//				'bindParams' => array('extendsModel' => $params['extendsModel']),
//
//		);
		
		$entityId = $params['extendsModel']::getType()->getId();;
		
		$sfp = \GO\Base\Db\FindParams::newInstance()
						->limit(0)
						->order(array('category.sortOrder', 't.sortOrder'), array('ASC', 'ASC'))
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('entityId', $entityId,'=','category'));
		
		if(isset($params['datatype'])){
			$sfp->getCriteria()->addCondition('datatype', $params['datatype']);
		}
		
		return $sfp;
	}

	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('category_name', '$model->category->name');
		$columnModel->formatColumn('column_name', '$model->columnName()');
		$columnModel->formatColumn('type', '$model->customfieldtype->name()');
		$columnModel->formatColumn('unique_values', '$model->unique_values ? \GO::t("Yes") : \GO::t("No")');
		return parent::formatColumns($columnModel);
	}

	protected function actionSaveTreeSelectOption($params) {
		if (empty($params['tree_select_option_id'])) {
			$model = new \GO\Customfields\Model\FieldTreeSelectOption();
		} else {
			$model = \GO\Customfields\Model\FieldTreeSelectOption::model()->findByPk($params['tree_select_option_id']);
		}

		$model->setAttributes($params);
		$response['success'] = $model->save();

		return $response;
	}

	protected function actionImportSelectOptions($params) {

		$importFile = \GO::config()->getTempFolder() . 'selectoptionsimport.csv';
		if (is_uploaded_file($_FILES['importfile']['tmp_name'][0])) {
			move_uploaded_file($_FILES['importfile']['tmp_name'][0], $importFile);
		}

		if (!file_exists($importFile)) {
			throw new \Exception('File was not uploaded!');
		}
		$csv = new \GO\Base\Fs\CsvFile($importFile);
		$sortOrder = 0;
		while ($record = $csv->getRecord()) {
			$o = new \GO\Customfields\Model\FieldSelectOption();
			$o->field_id = $params['field_id'];
			$o->text = $record[0];
			$o->sort_order = $sortOrder++;
			$o->save();
		}

		return array('success' => true);
	}

	protected function actionImportTreeSelectOptions($params) {

		$importFile = \GO::config()->getTempFolder() . 'selectoptionsimport.csv';
		
		if (is_uploaded_file($_FILES['importfile']['tmp_name'][0])) {
			move_uploaded_file($_FILES['importfile']['tmp_name'][0], $importFile);
		}

		if (!file_exists($importFile)) {
			throw new \Exception('File was not uploaded!');
		}
		
		$field = \GO\Customfields\Model\Field::model()->findByPk($params['field_id']);
		
		$sort=1;
		$csv = new \GO\Base\Fs\CsvFile($importFile);
		while ($record = $csv->getRecord()) {

			for ($i = 0; $i < count($record); $i++) {
				
				if($i==0)
					$parent_id=0;

				if (!empty($record[$i])) {
					$existingModel = \GO\Customfields\Model\FieldTreeSelectOption::model()->findSingleByAttributes(array(							
						'field_id'=>$params['field_id'],
						'parent_id'=>$parent_id,
						'name'=> $record[$i]
					));					
					
					if($existingModel)
						$parent_id=$existingModel->id;
					else{
						$o = new \GO\Customfields\Model\FieldTreeSelectOption();
						
						$o->checkSlaves=false;
						
						$o->field_id = $params['field_id'];
						$o->name = $record[$i];
						$o->parent_id=$parent_id;
						$o->sort=$sort;
						$o->save();
						
						$sort++;
						
						$parent_id=$o->id;
					}
				}			
			}
		}
		
		$field->checkTreeSelectSlaves();

		return array('success' => true);
	}

}

