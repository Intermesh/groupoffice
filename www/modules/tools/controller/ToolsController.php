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
 * The GO\Tools\Controller\Tools controller
 *
 * @package GO.modules.Tools
 * @version $Id: ToolsController.php 22115 2018-01-12 10:41:26Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */

namespace GO\Tools\Controller;
use GO;

class ToolsController extends GO\Base\Controller\AbstractJsonController{
	
	public function actionStore($params){
	
		$columnModel = new GO\Base\Data\ColumnModel(false,array(),array('name','description','script'));
		
		$store = new GO\Base\Data\ArrayStore($columnModel);

		$store->addRecord(array('name'=>GO::t("System check", "tools"),'script'=>GO::url('tools/tools/systemTest')));
		$store->addRecord(array('name'=>GO::t("Database check", "tools"),'script'=>GO::url('maintenance/checkDatabase')));
		$store->addRecord(array('name'=>GO::t("Create search index", "tools"),'script'=>GO::url('maintenance/buildSearchCache')));
		$store->addRecord(array('name'=>GO::t("Remove duplicate contacts and events", "tools"),'script'=>GO::url('maintenance/removeDuplicates')));
		
		if(GO::modules()->files) {
			$store->addRecord(array('name'=>'Sync filesystem with files database','script'=>GO::url('files/folder/syncFilesystem')));
			$store->addRecord(array('name'=>'Recalculate disk usage','script'=>GO::url('files/file/recalculateDiskUsage')));
		}
		
		if(GO::modules()->filesearch)
			$store->addRecord(array('name'=>'Update filesearch index','script'=>GO::url('filesearch/filesearch/sync')));
		
		if(GO::modules()->calendar){
			$store->addRecord(array('name'=>GO::t("Clear calendar holiday cache", "calendar"), 'script'=>GO::url('calendar/calendar/truncateHolidays')));
		}

		echo $this->renderStore($store);
	}
	
	protected function actionSystemTest(){
		require(GO::config()->root_path.'install/gotest.php');
		
		
		$this->render('externalHeader');
		output_system_test();
		
		$this->render('externalFooter');
	}
	
}
