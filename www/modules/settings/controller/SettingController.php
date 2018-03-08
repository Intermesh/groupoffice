<?php


namespace GO\Settings\Controller;


class SettingController extends \GO\Base\Controller\AbstractController {

	protected function actionLoad($params){
		$response = array();
		
		$response['data']=array();
		
		$t = \GO::config()->get_setting('login_screen_text_enabled');
		$response['data']['login_screen_text_enabled']=!empty($t);

		$t = \GO::config()->get_setting('login_screen_text');
		$response['data']['login_screen_text']=$t ? $t : '';

		$t = \GO::config()->get_setting('login_screen_text_title');
		$response['data']['login_screen_text_title']=$t ? $t : '';

		$response['data']['addressbook_name_template'] = \GO\Base\Model\AbstractUserDefaultModel::getNameTemplate("GO\Addressbook\Model\Addressbook");
		$response['data']['task_name_template'] = \GO\Base\Model\AbstractUserDefaultModel::getNameTemplate("GO\Tasks\Model\Tasklist");
		$response['data']['calendar_name_template'] = \GO\Base\Model\AbstractUserDefaultModel::getNameTemplate("GO\Calendar\Model\Calendar");
		
		$response['success']=true;
		return $response;
	}
	
	protected function actionSubmit($params) {
		
		$text = $params['login_screen_text'];
		$reportFeedback = '';

		if(preg_match("/^<br[^>]*>$/", $text))
			$text="";

		\GO::config()->save_setting('login_screen_text', $text);
		\GO::config()->save_setting('login_screen_text_title', $_POST['login_screen_text_title']);

		\GO::config()->save_setting('login_screen_text_enabled', !empty($_POST['login_screen_text_enabled']) ? '1' : '0');

		if (!empty($params['addressbook_name_template']))
			\GO\Base\Model\AbstractUserDefaultModel::setNameTemplate("GO\Addressbook\Model\Addressbook",$params['addressbook_name_template']);
		
		if (!empty($params['task_name_template']))
			\GO\Base\Model\AbstractUserDefaultModel::setNameTemplate("GO\Tasks\Model\Tasklist",$params['task_name_template']);
		if (isset($params['GO_Tasks_Model_Tasklist_change_all_names']))
			$this->_updateAllDefaultTasklists($reportFeedback);
		
		if (!empty($params['calendar_name_template']))
			\GO\Base\Model\AbstractUserDefaultModel::setNameTemplate("GO\Calendar\Model\Calendar",$params['calendar_name_template']);
		if (isset($params['calendar_change_all_names']))
			$this->_updateAllDefaultCalendars($reportFeedback);	
		
		$response['feedback'] = !empty($reportFeedback) ? $reportFeedback : '';
		$response['success'] = true;
		return $response;
	}
	
	private function _updateAllDefaultTasklists(&$feedback='') {		
		$stmt = \GO\Tasks\Model\Tasklist::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->ignoreAcl()
				->joinModel(
					array(
						'model'=>'GO\Tasks\Model\Settings',
						'localTableAlias'=>'t',
						'localField'=>'id',
						'foreignField'=>'default_tasklist_id',
						'tableAlias'=>'sett'
					)
				));
		while ($updateModel = $stmt->fetch()) {
			try{
				$updateModel->setDefaultAttributes(false);
				$updateModel->save();
			}catch(\Exception $e){
				$feedback .= $e->getMessage();
			}
		}
	}
	
	private function _updateAllDefaultCalendars(&$feedback=''){
		$stmt = \GO\Calendar\Model\Calendar::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->ignoreAcl()
				->joinModel(
					array(
						'model'=>'GO\Calendar\Model\Settings',
						'localTableAlias'=>'t',
						'localField'=>'id',
						'foreignField'=>'calendar_id',
						'tableAlias'=>'sett'
					)
				));
		while ($updateModel = $stmt->fetch()) {
			try{
				$updateModel->setDefaultAttributes(false);
				$updateModel->save();
			}catch(\Exception $e){
				$feedback .= $e->getMessage();
			}
		}
	}

}
?>
