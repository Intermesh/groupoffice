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
 * The Portlet controller
 *
 * @package GO.modules.Addressbook.controller
 * @version $Id: PortletController.php 16757 2014-01-30 10:54:43Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
namespace GO\Addressbook\Controller;

class PortletController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model from where the MANY_MANY relation is called
	 * @return String 
	 */
	public function modelName() {
		return 'GO\Addressbook\Model\Addressbook';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Addressbook\Model\BirthdaysPortletSetting';
	}
	
	/**
	 * The name of the field in the linkModel where the key of the current model is defined.
	 * @return String
	 */
	public function linkModelField() {
		return 'addressbook_id';
	}
	
	/**
	 * Get the data for the grid that shows all the tasks from the selected tasklists.
	 * 
	 * @param Array $params
	 * @return Array The array with the data for the grid. 
	 */
	protected function actionBirthdays($params) {
		
		$today = mktime(0,0,0);
		$next_month = \GO\Base\Util\Date::date_add(mktime(0,0,0),30);
		//\GO::debug($yesterday);
		
		$start = date('Y-m-d',$today);
		$end = date('Y-m-d',$next_month);
		//\GO::debug($start);
		
		$select = "t.id, birthday, first_name, middle_name, last_name, addressbook_id, photo, "
			."IF (STR_TO_DATE(CONCAT(YEAR('$start'),'/',MONTH(birthday),'/',DAY(birthday)),'%Y/%c/%e') >= '$start', "
			."STR_TO_DATE(CONCAT(YEAR('$start'),'/',MONTH(birthday),'/',DAY(birthday)),'%Y/%c/%e') , "
			."STR_TO_DATE(CONCAT(YEAR('$start')+1,'/',MONTH(birthday),'/',DAY(birthday)),'%Y/%c/%e')) "
			."as upcoming ";
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('birthday', '0000-00-00', '!=')
						->addRawCondition('birthday', 'NULL', 'IS NOT');
		
		$settings = \GO\Addressbook\Model\BirthdaysPortletSetting::model()->findByAttribute('user_id', \GO::user()->id);
		
		
		$abooks=array_map(function($value) {
			return $value->addressbook_id;
		}, $settings->fetchAll());
		if(count($abooks)) {
			$findCriteria->addInCondition('addressbook_id', $abooks);
		}
		
		
		$having = "upcoming BETWEEN '$start' AND '$end'";
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
			->distinct()
			->select($select)
			->criteria($findCriteria)
			->having($having)
			->order('upcoming');
		
		
		//$response['data']['original_photo_url']=$model->photoURL;
		$columnModel = new \GO\Base\Data\ColumnModel('GO\Addressbook\Model\Contact');
		$columnModel->formatColumn('addressbook_id', '$model->addressbook->name');
		$columnModel->formatColumn('photo_url', '$model->getPhotoThumbURL()');
		$columnModel->formatColumn('age', '($model->upcoming != date("Y-m-d")) ? $model->age+1 : $model->age');
		
		$store = new \GO\Base\Data\DbStore('GO\Addressbook\Model\Contact', $columnModel, $_POST, $findParams);
		
		return $store->getData();
		
	}
	
}
