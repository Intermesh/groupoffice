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
 * The Tasklist controller
 *
 * @package GO.modules.Tasks
 * @version $Id: Tasklist.php 7607 2011-09-20 10:08:21Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */


namespace GO\Tasks\Controller;


class TasklistController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Tasks\Model\Tasklist';
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name','$model->user->name');
		
		return parent::formatColumns($columnModel);
	}
	
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams) {
		
		$multiSel = new \GO\Base\Component\MultiSelectGrid(
						'ta-taskslists', 
						"GO\Tasks\Model\Tasklist",$store, $params);
		$multiSel->setFindParamsForDefaultSelection($storeParams);
		$multiSel->formatCheckedColumn();
		
		return parent::beforeStoreStatement($response, $params, $store, $storeParams);
	}
	
	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name','ASC');
		return parent::beforeStore($response, $params, $store);
	}
	
	protected function remoteComboFields(){
		return array(
				'user_name'=>'$model->user->name'
				);
	}
	
	public function actionImportIcs($params) {
		$response = array( 'success' => true );
		$count = 0;
		$failed=array();
		if (!file_exists($_FILES['ical_file']['tmp_name'][0])) {
			throw new \Exception($lang['common']['noFileUploaded']);
		}else {
			$file = new \GO\Base\Fs\File($_FILES['ical_file']['tmp_name'][0]);
			$file->convertToUtf8();
			$contents = $file->getContents();
			$vcal = \GO\Base\VObject\Reader::read($contents);
			\GO\Base\VObject\Reader::convertVCalendarToICalendar($vcal);
			foreach($vcal->vtodo as $vtask) {
				$event = new \GO\Tasks\Model\Task();			
				try{
					$event->importVObject( $vtask, array('tasklist_id'=>$params['tasklist_id']) );
		
					$count++;
				}catch(\Exception $e){
					$failed[]=$e->getMessage();
				}
			}
		}
		$response['feedback'] = sprintf(\GO::t("%s tasks were imported", "tasks"), $count);
		
		if(count($failed)){
			$response['feedback'] .= "\n\n".count($failed)." tasks failed: ".implode('\n', $failed);
		}
		return $response;
	}
	
	
	public function actionTruncate($params){
		$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($params['tasklist_id']);
		
		if(!$tasklist)
			throw new \GO\Base\Exception\NotFound();
		
		$tasklist->truncate();
		
		$response['success']=true;
		
		return $response;
	}
	
	
	public function actionRemoveDuplicates($params){
		
		\GO::setMaxExecutionTime(300);
		\GO::setMemoryLimit(1024);
		
		$this->render('externalHeader');
		
		$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($params['tasklist_id']);
		
		if(!$tasklist)
			throw new \GO\Base\Exception\NotFound();
		
		\GO\Base\Fs\File::setAllowDeletes(false);
		//VERY IMPORTANT:
		\GO\Files\Model\Folder::$deleteInDatabaseOnly=true;
		
		
		\GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$checkModels = array(
				"GO\Tasks\Model\Task"=>array('name', 'start_time', 'due_time', 'rrule', 'user_id', 'tasklist_id'),
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
				
				$findParams->getCriteria()->addCondition('tasklist_id', $tasklist->id);

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
					
					$findParams->getCriteria()->addCondition('tasklist_id', $tasklist->id);

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
				echo '<br /><br /><a href="'.\GO::url('tasks/tasklist/removeDuplicates', array('delete'=>true, 'tasklist_id'=>$tasklist->id)).'">'.\GO::t("Click here to delete the newest duplicates marked in red.").'</a>';
				
			}
		}
		
		$this->render('externalFooter');
		
		
	}
}
