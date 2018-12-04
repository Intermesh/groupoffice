<?php


namespace GO\Favorites\Controller;


class FavoritesController extends \GO\Base\Controller\AbstractJsonController {
	
	protected function actionCalendarStore(){
		$colModel = new \GO\Base\Data\ColumnModel(\GO\Favorites\Model\Calendar::model());
		$colModel->setColumnsFromModel(\GO\Calendar\Model\Calendar::model());

		$findParams = new \GO\Base\Db\FindParams();
		$findParams->getCriteria()->addCondition('user_id', \GO::user()->id, '=','cal');
		$findParams->order('name');
		$findParams->joinModel(
			array(
				'model'=>'GO\Favorites\Model\Calendar',
				'localTableAlias'=>'t', //defaults to "t"
				'localField'=>'id', //defaults to "id"
				'foreignField'=>'calendar_id', //defaults to primary key of the remote model
				'tableAlias'=>'cal', //Optional table alias
				'type'=>'INNER' //defaults to INNER,
			)
		);
				
		$store = new \GO\Base\Data\DbStore('GO\Calendar\Model\Calendar',$colModel , $_POST, $findParams);
		
		$store->defaultSort = array('name');
		$store->multiSelectable('calendars');
		
		echo $this->renderStore($store);	
	}
	
	protected function actionTasklistStore(){
		$colModel = new \GO\Base\Data\ColumnModel(\GO\Favorites\Model\Tasklist::model());
//		$colModel->formatColumn('type', '$model->customfieldtype->name()');
		
		$findParams = new \GO\Base\Db\FindParams();
		$findParams->getCriteria()->addCondition('user_id', \GO::user()->id, '=','tal');
		$findParams->order('name');
		$findParams->joinModel(
			array(
				'model'=>'GO\Favorites\Model\Tasklist',
				'localTableAlias'=>'t', //defaults to "t"
				'localField'=>'id', //defaults to "id"
				'foreignField'=>'tasklist_id', //defaults to primary key of the remote model
				'tableAlias'=>'tal', //Optional table alias
				'type'=>'INNER' //defaults to INNER,
			)
		);
		$store = new \GO\Base\Data\DbStore('GO\Tasks\Model\Tasklist',$colModel , $_POST, $findParams);
//		$store->defaultSort = array('sort','name');
		$store->multiSelectable('ta-taskslists');
		
		echo $this->renderStore($store);	
	}
	
}
