<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Calendar.php 7607 2011-09-14 10:07:02Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */  

/**
 * The Calendar controller
 *
 */


namespace GO\Calendar\Controller;

use SimpleXMLElement;

use GO;
use GO\Base\Fs\File;
use GO\Calendar\Model\Event;


class CalendarController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Calendar\Model\Calendar';
	
	protected function allowGuests() {
		return array('exportics');
	}
	
//	protected function ignoreAclPermissions() {
//		return array('exportics');
//	}

	protected function getStoreParams($params) {
		
		$findParams =\GO\Base\Db\FindParams::newInstance();
		
		$c = $findParams->getCriteria();
		
		if(!empty($params['resources'])){
			$c->addCondition('group_id', 1,'!=');
		}else
		{
			$c->addCondition('group_id', 1,'=');
		}

		if(!empty($params['ownedBy'])) {

			$c->addCondition('user_id', $params['ownedBy']);
		}
		return $findParams;
	}
	
	protected function remoteComboFields() {
		return array(
				'user_id'=>'$model->user->name',
				'tasklist_id' => '$model->tasklist?$model->tasklist->name:""'
		);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		$url = \GO::createExternalUrl('calendar', 'openCalendar', array(
			'calendars'=>array($response['data']['id']),
			'group_id'=>$response['data']['group_id'])
		);
//		GO::debug($model->getAttributes());
		$response['remoteComboTexts']['group_id'] = $model->group->name;
		
		// Show "None" in the Caldav Tasklist selection when tasklist_id is 0
		if(empty($response['data']['tasklist_id']))
				$response['data']['tasklist_id'] = "";
		
		$response['data']['url']='<a class="normal-link" target="_blank" href="'.$url.'">'.\GO::t("Right click to copy link location", "calendar").'</a>';
		
		// Get a link to the ics exporter
		//$response['data']['ics_url']='<a class="normal-link" target="_blank" href="'.\GO::url("calendar/calendar/exportIcs", array("calendar_id"=>$response['data']['id'],"months_in_past"=>1)).'">'.\GO::t("Right click to copy link location", "calendar").'</a>';

		// Get a link to the ics file that is exported
		$response['data']['ics_url'] = '<a class="normal-link" target="_blank" href="'.$model->getPublicIcsUrl().'">'.\GO::t("Right click to copy link location", "calendar").'</a>';
		return parent::afterLoad($response, $model, $params);
	}
	
	protected function actionCalendarsWithGroup($params){
		
		$store = \GO\Base\Data\Store::newInstance(\GO\Calendar\Model\Calendar::model());
		
		if(!isset($params['permissionLevel']))
			$params['permissionLevel']=\GO\Base\Model\Acl::READ_PERMISSION;
		
		$store->getColumnModel()->formatColumn('permissionLevel', '$model->permissionLevel');
		
		$this->processStoreDelete($store, $params);
		
		$store->setDefaultSortOrder(array('g.name','t.name'), array('ASC','ASC'));
		
		$findParams = $store->getDefaultParams($params)
						->join(\GO\Calendar\Model\Group::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()->addCondition('group_id', 'g.id', '=', 't', true, true),'g')				
						->select('t.*,g.name AS group_name')
						->permissionLevel($params['permissionLevel']);
		
		if(!empty($params['resourcesOnly']))
			$findParams->getCriteria ()->addCondition ('group_id', 1,'>');
		elseif(!empty($params['calendarsOnly']))
			$findParams->getCriteria ()->addCondition ('group_id', 1,'=');

		if(!empty($params['ownedBy'])) {
			$findParams->getCriteria ()->addCondition('user_id', $params['ownedBy']);
		}
		
		$stmt = \GO\Calendar\Model\Calendar::model()->find($findParams);
		
		
		$store->setStatement($stmt);

		
		return $store->getData();
	}
		
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('user_name','$model->user->name');
		
		return parent::formatColumns($columnModel);
	}
	
	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name','ASC');
		return parent::beforeStore($response, $params, $store);
	}
		
	public function formatStoreRecord($record, $model, $store) {
		
		$record['group_name']= !empty($model->group) ? $model->group->name : '';
	
		
		return $record;
	}	
	
//	public function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
			
//		if(!empty($params['tasklists'])){
//			$visible_tasklists = json_decode($params['tasklists']);
//		
//			foreach($visible_tasklists as $vtsklst) {
//				if($vtsklst->visible)
//					$model->addManyMany('visible_tasklists', $vtsklst->id);
//				else
//					$model->removeManyMany ('visible_tasklists', $vtsklst->id);
//			}
//		}
		
//		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
//	}
	
	public function actionImportIcs($params) {
		\GO::setMaxExecutionTime(0);
		
		\GO::session()->closeWriting();
		\GO::$disableModelCache=true;
		$response = array( 'success' => true );
		$count = 0;
		$failed=array();
		if (!isset($_FILES['ical_file']) || !file_exists($_FILES['ical_file']['tmp_name'][0])) {
			throw new \Exception(\GO::t("No file was received"));
		}else {
			$file = new File($_FILES['ical_file']['tmp_name'][0]);
			
			$ext = strtolower(File::getExtension($_FILES['ical_file']['name'][0]));
			
			if($ext == 'ics'){
				$i = new \GO\Base\Vobject\Iterator($file, "VEVENT");
				foreach($i as $vevent){					

					$event = new Event();
					try{
						$event->importVObject( $vevent, array('calendar_id'=>$params['calendar_id']) );
						$count++;
					}catch(\Exception $e){
						$failed[]=$e->getMessage();
					}
				}
			}elseif($ext=='xml')
			{
				return $this->_importSmarterMailXml($params, $file);
			}else
			{
				throw new \Exception("Extension $ext is not supported");
			}
		}
		$response['feedback'] = sprintf(\GO::t("%s events were imported", "calendar"), $count);
		
		if(count($failed)){
			$response['feedback'] .= "\n\n".count($failed)." events failed: ".implode("\n", $failed);
		}
		
		return $response;
	}
	
	private function _importSmarterMailXml($params, File $file){
		$sXml=new SimpleXMLElement($file->getContents());
		
		$count = 0;
		$failed=array();
		
		foreach($sXml->children() as $obj)
		{
			if($obj->getName()=='Event'){
				$event = new Event();
				try{
					
					$data = (string) $obj->Data;
					
//					\GO::debug($data);
					
					$vObject = \GO\Base\VObject\Reader::read($data);
					
					$event->importVObject($vObject->vevent[0], array('calendar_id'=>$params['calendar_id']) );
					$count++;
					
//					break;
				}catch(\Exception $e){
					$failed[]=$e->getMessage();
				}
			}		
		}
		
		$response['feedback'] = sprintf(\GO::t("%s events were imported", "calendar"), $count);
		
		if(count($failed)){
			$response['feedback'] .= "\n\n".count($failed)." events failed: ".implode("\n", $failed);
		}
		
		return $response;
	}
	
	/**
	* Load the user_calendar_colors 
	* 
	* @param array $params
	* @return array 
	*/	
	public function actionLoadColors($params){
		$store = \GO\Base\Data\Store::newInstance(\GO\Calendar\Model\Calendar::model());
		
		$findParams = $store->getDefaultParams($params)
						->join(\GO\Calendar\Model\CalendarUserColor::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
										->addCondition('id', 'col.calendar_id', '=', 't', true, true)->addCondition('user_id', \GO::user()->id, '=','col'),'col','LEFT')
						->order(array('t.name'))	
						->select('col.*,name,t.id');
		
		$findParams->getCriteria()->addCondition('group_id', 1);
		
		$stmt = \GO\Calendar\Model\Calendar::model()->find($findParams);
		
		$store->setStatement($stmt);

		$store->getColumnModel()->setFormatRecordFunction(array($this,'getCalendarColor'));
		
		return $store->getData();
	}
	
	private $_colors = array(
		'F0AE67','FFCC00','FFFF00','CCFF00','66FF00',
		'00FFCC','00CCFF','0066FF','95C5D3','6704FB',
		'CC00FF','FF00CC','CC99FF','FB0404','FF6600',
		'C43B3B','996600','66FF99','999999','00FFFF'
	);
	private $_colorIndex = 0;
	
	public function getCalendarColor($formattedrecord,$model,$controller){

//		if(empty($formattedrecord->color))
//			$color = false;
		//$color = $model->getColor(\GO::user()->id);
		if(empty($formattedrecord['color'])){
			if($this->_colorIndex >= count($this->_colors))
				$this->_colorIndex = 0;
			
			$formattedrecord['color'] = $this->_colors[$this->_colorIndex];
			$this->_colorIndex++;
		}
		
		return $formattedrecord;
	}
	
	
	/**
	 * Save the user_calendar_colors
	 * 
	 * @param array $params
	 * @return array 
	 */
	public function actionSubmitColors($params){
		$params = json_decode($params['griddata']);
		
		foreach($params as $cC){			
			$calendarColor = \GO\Calendar\Model\CalendarUserColor::model()->findByPk(array('calendar_id'=>$cC->id,'user_id'=>\GO::user()->id));
			
			if(!$calendarColor){
				$calendarColor = new \GO\Calendar\Model\CalendarUserColor();
				$calendarColor->user_id = \GO::user()->id;
				$calendarColor->calendar_id = $cC->id;
			}
			
			$calendarColor->color = $cC->color;
			
			$calendarColor->save();
		}
		$response['success']=true;

		return $response;
	}

	
	public function actionExportIcs($params){
		
		if($this->isCli()) {
			\GO::session()->runAsRoot();
		}
		
		$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($params["calendar_id"],false, true);
		
		if(!$calendar->public && !$calendar->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION))
			throw new \GO\Base\Exception\AccessDenied();
		
//		$c = new \GO\Base\VObject\VCalendar();				
//		$c->add(new \GO\Base\VObject\VTimezone());
		
		$months_in_past = isset($params['months_in_past']) ? intval($params['months_in_past']) : 0;
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->select("t.*")->order('start_time','ASC');
		$findParams->getCriteria()->addCondition("calendar_id", $params["calendar_id"]);
		
		if(!empty($params['months_in_past']))		
			$stmt = Event::model()->findForPeriod($findParams, \GO\Base\Util\Date::date_add(time(), 0, -$months_in_past));
		else
			$stmt = Event::model()->find($findParams);		
		if(empty($params['no_download']) && !$this->isCli()) {
			\GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\FS\File($calendar->name.'.ics'));
		}
		echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Intermesh//NONSGML ".\GO::config()->product_name." ".\GO::config()->version."//EN\r\n";

		$t = new \GO\Base\VObject\VTimezone();
		echo $t->serialize();
		
		while($event = $stmt->fetch()){
			$v = $event->toVObject();
			echo $v->serialize();
		}
		
		echo "END:VCALENDAR\r\n";
	}	
	
	public function actionTruncate($params){
		$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($params['calendar_id']);
		
		if(!$calendar)
			throw new \GO\Base\Exception\NotFound();
		
		$calendar->truncate();
		
		$response['success']=true;
		
		return $response;
	}
	
	public function actionRemoveDuplicates($params){
		
		$this->render('externalHeader');
		
		\GO::setMaxExecutionTime(300);
		\GO::setMemoryLimit(1024);
		
		$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($params['calendar_id']);
		
		if(!$calendar)
			throw new \GO\Base\Exception\NotFound();
		
		File::setAllowDeletes(false);
		//VERY IMPORTANT:
		\GO\Files\Model\Folder::$deleteInDatabaseOnly=true;
		
		
		\GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$checkModels = array(
				"GO\Calendar\Model\Event"=>array('name', 'start_time', 'end_time', 'rrule','calendar_id'),
			);		
		
		foreach($checkModels as $modelName=>$checkFields){
			
			if(empty($params['model']) || $modelName==$params['model']){

				echo '<h1>'.\GO::t("Remove duplicates").'</h1>';

				$checkFieldsStr = 't.'.implode(', t.',$checkFields);
				$findParams = \GO\Base\Db\FindParams::newInstance()
								->ignoreAcl()
								->select('t.id, count(*) AS n, '.$checkFieldsStr)
								->group($checkFields)
								->having('n>1');
				
				$findParams->getCriteria()->addCondition('calendar_id', $calendar->id);

				$stmt1 = \GO::getModel($modelName)->find($findParams);

				echo '<table border="1">';
				echo '<tr><td>ID</th><th>'.implode('</th><th>',$checkFields).'</th></tr>';

				$count = 0;

				while($dupModel = $stmt1->fetch()){
					
					$select = 't.id';
					
					if(\GO::getModel($modelName)->hasFiles()){
						$select .= ', t.files_folder_id';
					}

					$findParams = \GO\Base\Db\FindParams::newInstance()
								->ignoreAcl()
								->select($select.', '.$checkFieldsStr)
								->order('id','ASC');
					
					$findParams->getCriteria()->addCondition('calendar_id', $calendar->id);

					foreach($checkFields as $field){
						$findParams->getCriteria()->addCondition($field, $dupModel->getAttribute($field));
					}							

					$stmt = \GO::getModel($modelName)->find($findParams);

					$first = true;

					while($model = $stmt->fetch()){
						echo '<tr><td>';
						if(!$first)
							echo '<span style="color:red">';
						echo $model->id;
						if(!$first)
							echo '</span>';
						echo '</th>';				

						foreach($checkFields as $field)
						{
							echo '<td>'.$model->getAttribute($field,'html').'</td>';
						}

						echo '</tr>';

						if(!$first){							
							if(!empty($params['delete'])){

								if($model->hasLinks() && $model->countLinks()){
									echo '<tr><td colspan="99">'.\GO::t("Skipped delete because model has links").'</td></tr>';
								}elseif(($filesFolder = $model->getFilesFolder(false)) && ($filesFolder->hasFileChildren() || $filesFolder->hasFolderChildren())){
									echo '<tr><td colspan="99">'.\GO::t("Skipped delete because model has folder or files").'</td></tr>';
								}else{									
									$model->delete();
								}
							}

							$count++;
						}

						$first=false;
					}
				}	
					

				echo '</table>';

				echo '<p>'.sprintf(\GO::t("Found %s duplicates."),$count).'</p>';
				echo '<br /><br /><a href="'.\GO::url('calendar/calendar/removeDuplicates', array('delete'=>true, 'calendar_id'=>$calendar->id)).'">'.\GO::t("Click here to delete the newest duplicates marked in red.").'</a>';
				
			}
		}
		
		$this->render('externalFooter');
		
		
	}
	
	/**
	 * get a pdf of a count of all categories
	 * 
	 * @param array $params
	 * @return PDF file 
	 */
	public function actionPrintCategoryCount($params){
		
		
		\GO::session()->closeWriting();
		
		// If a year is posted then determine the correct start and end date and set them here
		if(isset($params['fullYear'])){
			$params['startDate'] = ''; // TODO: Find this 
			$params['endDate'] = ''; // TODO: Find this 
		}
		
		if(isset($params['startDate'])){
			$startDate = $params['startDate'];
		}else{
			$startDate = \GO\Base\Util\Date::date_add(time(),-14); // - (2 * 7 * 24 * 60 * 60); // -2weken
			$startDate = \GO\Base\Util\Date::get_timestamp($startDate,false);
		}
		
		if(isset($params['endDate'])){
			$endDate = $params['endDate'];
		}else{
			$endDate = time();
			$endDate = \GO\Base\Util\Date::get_timestamp($endDate,false);
		}

		$categoryCountModel = new \GO\Calendar\Model\PrintCategoryCount($startDate,$endDate);
		
//		//Set the PDF filename
		$filename = \GO::t("Number of events for each category", "calendar").'.pdf';
//		
//		// Start building the PDF file
		$pdf = new \GO\Calendar\Reports\PrintCategoryCount($orientation='L');
		
		$pdf->setTitle(\GO::t("Number of events for each category", "calendar"));
		//$pdf->setSubTitle($startDate.' '.\GO::t("till", "calendar").' '.$endDate);
		
		$pdf->render($categoryCountModel);

		return $pdf->Output($filename,'D');
	}
	
	/**
	 * Truncate the holidays table so they will be generated again based on the holiday files
	 * 
	 * @param array $params
	 */
	public function actionTruncateHolidays($params){
		
		$pdo_statement = \GO::$db->query('TRUNCATE TABLE '.\GO\Base\Model\Holiday::model()->tableName(). ';');
		
		if($pdo_statement->execute()){
			echo 'Table '.\GO\Base\Model\Holiday::model()->tableName().' is truncated.';
		} else {
			echo "Something went wrong";
		}
	}
	
}
