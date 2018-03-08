<?php


namespace GO\Servermanager\Controller;

use Exception;


class TrialController extends \GO\Site\Components\Controller {

	protected $newTrial;
	
	public function allowGuests() {
		return array('create', 'newtrial','emailsent');
	}
	
	public function ignoreAclPermissions() {
		return array('create');
	}
	
		/**
	 * Render a page for creating new trail installation
	 * @throws Exception calling when trails are disabled in config when no wildcard domain is specified
	 */
	protected function actionNewTrial(){
		
		if(empty(\GO::config()->servermanager_trials_enabled))
			throw new Exception("Trials are not enabled. Set \$config['servermanager_trials_enabled']=true;");
		
		if(!isset(\GO::config()->servermanager_wildcard_domain))
			throw new Exception("\$config['servermanager_wildcard_domain']='example.com'; is not defined in /etc/groupoffice/config.php");
		
		
		$newTrial =  new \GO\ServerManager\Model\NewTrial();
		
		if (\GO\Base\Util\Http::isPostRequest()) {
		
			$newTrial->setAttributes($_POST['NewTrial']);
			if($newTrial->validate())
			{	
				$newTrial->save();

				$tplStr = file_get_contents(\GO::config()->root_path.'modules/defaultsite/views/site/servermanager/emails/trial.txt');
				$newTrial->sendMail($tplStr);

				$this->redirect(array('servermanager/trial/emailsent','key'=>$newTrial->key));
			}
		}		
		
		echo $this->render('newtrial', array('model' => $newTrial));
	}
	
	public function actionEmailSent(){
		$newTrial = \GO\ServerManager\Model\NewTrial::model()->findSingleByAttribute('key', $_REQUEST['key']);
		echo $this->render('emailsent', array('model' => $newTrial));
	}

	public function actionCreate($params) {
		
		if(empty(\GO::config()->servermanager_trials_enabled))
			throw new Exception("Trials are not enabled. Set \$config['servermanager_trials_enabled']=true;");

	
		$this->newTrial = \GO\ServerManager\Model\NewTrial::model()->findSingleByAttribute('key', $params['key']);
		if(!$this->newTrial)
			throw new Exception("Sorry, Could not find your trial subscription!");
		
		if (\GO\Base\Util\Http::isPostRequest()) {
			
			//clean up old trial requests that were never
			$stmt = \GO\ServerManager\Model\NewTrial::model()->find(\GO\Base\Db\FindParams::newInstance()->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('ctime', \GO\Base\Util\Date::date_add(time(),-1), '<')));
			$stmt->callOnEach("delete");
			
			
			$installation = new \GO\ServerManager\Model\Installation();
			$installation->status=\GO\ServerManager\Model\Installation::STATUS_TRIAL;
			$installation->name = $this->newTrial->name.'.'.\GO::config()->servermanager_wildcard_domain;
			
			if(\GO\Base\Html\Error::validateModel($installation)){

				$installation->save();

				$tmpConfigFile = $this->_createConfig($params, $installation, $this->newTrial);

				$cmd = 'sudo TERM=dumb ' . \GO::config()->root_path .
								'groupofficecli.php -r=servermanager/installation/create' .
								' -c=' . \GO::config()->get_config_file() .
								' --tmp_config=' . $tmpConfigFile->path() .
								' --name=' . $installation->name.
								' --adminpassword=' . $this->newTrial->password . ' 2>&1';

				exec($cmd, $output, $return_var);

				if ($return_var != 0) {
					throw new Exception(implode('<br />', $output));
				}
				
				
				echo $this->render('trialcreated', array('installation'=>$installation));
				
				$this->newTrial->delete();
				exit();
			}
		}

		echo $this->render('createtrial');
	}
	
	
	private function _createConfig($params, \GO\ServerManager\Model\Installation $model, \GO\ServerManager\Model\NewTrial $newTrial) {
		
		if(!file_exists($model->configPath)){
			//only create these values on new config files.
			
			//for testing		
			$config['debug']=\GO::config()->debug;

			$config['id']=$model->dbName;
			$config['db_name']=$model->dbName;
			$config['db_user']=$model->dbUser;
			$config['db_host']=\GO::config()->db_host;
			$config['db_pass']= \GO\Base\Util\StringHelper::randomPassword(8,'a-z,A-Z,1-9');
			$config['host']='/';
			$config['root_path']=$model->installPath.'groupoffice/';
			$config['tmpdir']='/tmp/'.$model->name.'/';
			$config['file_storage_path']=$model->installPath.'data/';
		}
				
		$config['enabled']=true;
		$config['max_users'] = $model->max_users;

		$config['webmaster_email'] = $newTrial->email;
		$config['title'] = $newTrial->title;
		$config['default_country'] = $params['default_country'];
		$config['language'] = $params['language'];
		$config['default_timezone'] = $params['default_timezone'];
		$config['default_currency'] = $params['default_currency'];
		$config['default_time_format'] = $params['default_time_format'];
		$config['default_date_format'] = $params['default_date_format'];
		$config['default_date_separator'] = $params['default_date_separator'];
		$config['default_thousands_separator'] = $params['default_thousands_separator'];
		$config['default_decimal_separator'] = $params['default_decimal_separator'];
		$config['first_weekday'] = $params['first_weekday'];
		$config['disable_mail'] = true;
		

		if (intval($config['max_users']) < 1)
			throw new Exception('You must set a maximum number of users');

		if (!\GO\Base\Util\StringHelper::validate_email($config['webmaster_email']))
			throw new Exception(\GO::t('invalidEmail','servermanager'));
		
		$tmpFile = \GO\Base\Fs\File::tempFile('', 'php');
		
		if(!\GO\Base\Util\ConfigEditor::save($tmpFile, $config)){
			throw new Exception("Failed to save config file!");
		}
		
		return $tmpFile;
	}

}

