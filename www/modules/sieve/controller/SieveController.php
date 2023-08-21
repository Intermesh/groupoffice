<?php

namespace GO\Sieve\Controller;


class SieveController extends \GO\Base\Controller\AbstractModelController{
	
	private $_sieve;
	
	function __construct() {
		$this->_sieve = new \GO\Sieve\Util\Sieve();
		parent::__construct();
	}
	
	private function _sieveConnect($accountId) {		
		$accountModel = \GO\Email\Model\Account::model()->findByPk($accountId);
		
		if (!empty($accountModel))
		$connectResponse = $this->_sieve->connect(
				$accountModel->username,
				$accountModel->decryptPassword(),
				$accountModel->host,
				$accountModel->sieve_port,
				null,
				!empty($accountModel->sieve_usetls),
				array(),
				true);
		
		if (empty($connectResponse))
		{
			throw new \Exception('Sorry, manage sieve filtering not supported on '.$accountModel->host.' using port '.$accountModel->sieve_port);				
		}
		
		return true;
		
	}
	
	protected function actionIsSupported($params){

		$error = null;
		try{
			$supported=$this->_sieveConnect($params['account_id']);
		}catch (\Exception $e){
			$supported=false;

			go()->debug($e);

			$error = (string) $e;
		}
		$extensions = array();
		if($supported){
			$extensions = (array) $this->_sieve->get_extensions();
		}
		
		return array('success'=>true, 'supported'=>$supported,'server_extensions'=>$extensions, 'error'=>$error);
	}
	
	protected function actionScripts($params) {
		
		$this->_sieveConnect($params['account_id']);
		
		if(!empty($params['set_active_script_name']))
			$this->_sieve->activate($params['set_active_script_name']);				

		$response['active']=$this->_sieve->get_active($params['account_id']);
		$all_scripts = $this->_sieve->get_scripts();
		
		$response['results'] = array();
		foreach($all_scripts as $script)
		{
			$name = $script;

			if($script == $response['active'])
			{
				$name .= ' ('.\GO::t("Active", "sieve").')';
			}

			$response['results'][]=array('value'=>$script,'name'=>$name, 'active'=>$script == $response['active']);
		}
		
		$response['success'] = true;

		return $response;
	}
	
	protected function actionRules($params) {
		
		$this->_sieveConnect($params['account_id']);

		if(!empty($params['script_name']))
			$scriptName = $params['script_name'];
		else
			$scriptName = $this->_sieve->get_active($params['account_id']);

		$response['results']=array();

		$this->_sieve->load($scriptName);
		if(isset($params['delete_keys']))
		{
			try
			{
				$keys = json_decode($params['delete_keys']);

				foreach($keys as $key)
				{
					if($this->_sieve->script->delete_rule($key))
						$this->_sieve->save();
				}
				$response['deleteSuccess']=true;
			}

			catch(\Exception $e)
			{
				$response['deleteSuccess']=false;
				$response['deleteFeedback']=$e->getMessage();
			}
		}

		if(!empty($this->_sieve->script->content)) {
			$index=0;
			foreach($this->_sieve->script->content as $item)
			{
				// Hide the "Out of office" script because it need to be loaded in a separate dialog
				if (isset($item['name']) && $item['name']!='Out of office')
				{
					$i['name']=$item['name'];
					$i['index']=$index;
					$i['script_name']=$scriptName;
					$i['active']= !$item['disabled'];

//					$response['results'][$item['name']]=$i;
					$response['results'][]=$i;
				}
				$index++;
			}
		}
		
//		ksort($response['results']);		
//		$response['results']=array_values($response['results']);

		$response['success']=true;
		return $response;
	}
	
	protected function actionSubmitRules($params) {	


		try {
			$this->_sieveConnect($params['account_id']);

			$rule['disabled'] = !isset($params['active']);

			$rule['name'] = $params['rule_name'];
			$rule['tests'] = json_decode($params['criteria'], true);
			$rule['actions'] = json_decode($params['actions'], true);
						
			for($i=0,$c=count($rule['tests']);$i<$c;$i++)
			{
				//\GO::debug("TEST: ".$rule['tests'][$i]['arg1']);
				if(preg_match('/[^a-z_\-_0-9]/i',$rule['tests'][$i]['arg1'])){
					throw new \Exception("Invalid value ".$rule['tests'][$i]['arg1']);
				}
			}
		
			for($i=0,$c=count($rule['actions']);$i<$c;$i++)
			{
				if(strpos($rule['actions'][$i]['type'],'_copy')){
					$rule['actions'][$i]['copy']=true;
					$rule['actions'][$i]['type']=str_replace('_copy','',$rule['actions'][$i]['type']);
//					var_dump($rule['actions'][$i]);
				}else
				{
					$rule['actions'][$i]['copy']=false;
				}
				
								
				if(!empty($rule['actions'][$i]['addresses'])) { // && !is_array($rule['actions'][$i]['addresses'])){
					if($rule['actions'][$i]['type']=='vacation') {
						if (!empty(\GO::config()->sieve_vacation_subject))
							$rule['actions'][$i]['subject']=\GO::config()->sieve_vacation_subject;
					}

					$rule['actions'][$i]['addresses']= is_array($rule['actions'][$i]['addresses']) ? $rule['actions'][$i]['addresses'] : explode(',',$rule['actions'][$i]['addresses']);
					$rule['actions'][$i]['addresses']=array_map('trim', $rule['actions'][$i]['addresses']);
				} else {
					unset($rule['actions'][$i]['vacationStart']);
					unset($rule['actions'][$i]['vacationEnd']);
				}
				
				if($rule['actions'][$i]['type'] == 'stop' && $i < $c-1){
					Throw new \GO\Base\Exception\Save(\GO::t("Stop needs to be on the end!", "sieve"));
				}
			}
			
			if($params['join'] == 'allof') {
				$rule['join'] = 1;
			}
			else if($params['join'] == 'any')
			{
				$rule['join'] = '';
				$rule['tests'] = array();
				$rule['tests'][0]['test'] = 'true';
				$rule['tests'][0]['not'] = '';
				$rule['tests'][0]['type'] = '';
				$rule['tests'][0]['arg'] = '';
				$rule['tests'][0]['arg1'] = '';
				$rule['tests'][0]['arg2'] = '';
			}
			else
			{
				$rule['join'] = '';
				if($rule['tests'][0]['test'] == 'true' &&
						$rule['tests'][0]['not'] == '' &&
						$rule['tests'][0]['type'] == '' &&
						$rule['tests'][0]['arg'] == '' &&
						$rule['tests'][0]['arg1'] == '' &&
						$rule['tests'][0]['arg2'] == '')
				{
					// Remove the first item from the array if it is an empty one where only TEST == true
					array_shift($rule['tests']);
				}
			}

			$response['results'] = array();

			// Het script laden
			$this->_sieve->load($params['script_name']);

			// Het script ophalen en terugzetten
			if($params['script_index']>-1 && isset($this->_sieve->script->content[$params['script_index']]))
				$this->_sieve->script->update_rule($params['script_index'],$rule);
			else {
				
				// If the rule is a spam rule then it needs to be placed at the top.
				if($this->_checkIsSpam($rule)){
					$this->_sieve->script->add_rule($rule,0);
				} else {
					$this->_sieve->script->add_rule($rule);
				}
			}
			
			// Het script opslaan
			if($this->_sieve->save()) {
				$response['success'] = true;
			} else {
				$response['feedback'] = "Could not save filtering rules. Please check your input.<br />".$this->_sieve->error();
				$response['success'] = false;
			}
		} catch (\Exception $e) {
			// you can change the feedback when debugging
			$response['feedback'] = nl2br($e->getMessage()); //.'<br>'.$e->getTraceAsString();
		}
		return $response;
	}
	
	/**
	 * Check if the tests in the given rule are spam message tests
	 * 
	 * @param array $rule
	 * @return boolean
	 * @throws \Exception
	 */
	private function _checkIsSpam($rule){
		
		if(!is_array($rule) || !is_array($rule['tests'])){
			Throw new \Exception('Rule is not an array');
		}
		
		$isSpam = false;

		foreach($rule['tests'] as $test){
			if($test['test'] == 'header' && $test['type'] == 'contains' && $test['arg1'] == 'X-Spam-Flag'){
				$isSpam = true;
			}
			
			if($test['test'] == 'header' && $test['type'] == 'contains' && $test['arg1'] == 'Subject' && $test['arg2'] == 'spam'){
				$isSpam = true;
			}
		}
		
		return $isSpam;

	}
	
	protected function actionAccountAliases($params) {
		$response = array();
		$aliasesStmt = \GO\Email\Model\Alias::model()->findByAttribute('account_id',$params['account_id']);
		$aliases = array();
		while ($aliasModel = $aliasesStmt->fetch()) {
			$aliases[] = $aliasModel->email;
		}
		$response['data']['aliases'] = implode(',',$aliases);
		$response['success'] = true;
		return $response;
	}
	
	protected function actionRule($params) {
		
		$this->_sieveConnect($params['account_id']);

		$response['criteria']=array();
		$response['actions']=array();

		$this->_sieve->load($params['script_name']);

		$current_rule = $this->_sieve->script->content[$params['script_index']];

		if($current_rule['join'] == 1)
			$response['data']['join'] = 'allof';
		else if($current_rule['join'] == '' && $current_rule['tests'][0]['test'] == 'true')
			$response['data']['join'] = 'any';
		else
			$response['data']['join']= 'anyof';

		$response['data']['active']= !$current_rule['disabled'];
		$response['data']['rule_name']=$current_rule['name'];
	
		foreach($current_rule['tests'] as $test)
		{
				//$test['test'];
				//$test['not'];
				//$test['type'];
				//$test['arg1'];
				//$test['arg2'];

				$response['criteria'][] = $test;
		}

		foreach($current_rule['actions'] as $action)
		{
				switch($action['type'])
				{
					case 'addflag':
						if($action['target'] == '\\Seen'){
							$action['text'] = \GO::t("Mark message as read", "sieve");
						}
						break;
					case 'set_read':
							$action['text'] = \GO::t("Mark message as read", "sieve");
						break;
					case 'fileinto':
						if(empty($action['copy'])){
							$action['text'] = \GO::t("Move email to the folder", "sieve").' "'.$action['target'].'"';
						}else{
							$action['text']=\GO::t("Copy email to the folder", "sieve").' "'.$action['target'].'"';
							$action['type'] = 'fileinto_copy';
						}
						break;
					
					case 'redirect':
						if (!empty($action['copy'])) {
							$action['type'] = 'redirect_copy';
							$action['text'] = \GO::t("Send a copy to", "sieve").' "'.$action['target'].'"';
						} else {
							$action['text'] = \GO::t("Redirect to", "sieve").' "'.$action['target'].'"';
						}
						break;
					case 'reject':
						$action['text']=\GO::t("Reject with message:", "sieve").' "'.$action['target'].'"';
						break;
					case 'vacation':
						$addressesText = !empty($action['addresses']) && is_array($action['addresses'])
							? \GO::t("Autoreply is active for", "sieve").': '.implode(',',$action['addresses']).'. '
							: '';
						
						if(empty($action['days']))
							$action['days']=7;
						
						$action['text']=\GO::t("Reply every", "sieve").' '.$action['days'].' '.\GO::t("day(s)", "sieve").'. '.$addressesText.\GO::t("Message:", "sieve").' "'.$action['reason'].'"';
						break;
					case 'discard':
						$action['text']=\GO::t("Discard", "sieve");
						break;
					case 'stop':
						$action['text']=\GO::t("Stop", "sieve");
						break;
					default:
						$action['text']=\GO::t("Error while displaying test line", "sieve");
						break;
				}
				$response['actions'][] = $action;
		}

		$response['success'] = true;
		return $response;
	}

//	protected function actionSetActiveScript($params) {
//		$this->_sieveConnect($params['account_id']);
//
//		$this->_sieve->activate($params['script_name']);
//
//		if($this->_sieve->save())
//			$response['success'] = true;
//		else{
//			$response['success'] = false;
//			$response['feedback']=$this->_sieve->error();
//		}
//		return $response;
//	}
//	
	protected function actionSaveScriptsSortOrder($params) {
		
		$this->_sieveConnect($params['account_id']);

		//$script = $this->_sieve->get_script($params['script_name']);
		$sort_order = json_decode($params['sort_order'], true);

		$this->_sieve->load($this->_sieve->get_active($params['account_id']));

		$count=count($sort_order);

		for($new_index=0;$new_index<$count;$new_index++){
			$old_index = $sort_order[$new_index];

			//oude script ophalen
			$temp = $this->_sieve->script->content[$old_index];

			//kopie toevoegen
			$this->_sieve->script->add_rule($temp);
		}
		
		//oude verwijderen
		for($i=0;$i < $count; $i++)
		{
			$this->_sieve->script->delete_rule($i);
		}

		$this->_sieve->save();
		$response['success'] = true;
		return $response;
	}
}
