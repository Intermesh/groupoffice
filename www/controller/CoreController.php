<?php

/**
 * TODO
 * 
 * The whole init process of Group-Office has to be remodelled.
 * The default_scripts.inc.php file is ugly and bad design. Instead all init
 * views in modules should register client scripts and css files.
 */

namespace GO\Core\Controller;

use Exception;
use GO;
use GO\Base\Language;
use GO\Base\Util\StringHelper;


class CoreController extends \GO\Base\Controller\AbstractController {
	
	protected function allowGuests() {
		return array('compress','cron','language','clientscripts');
	}
	
	protected function ignoreAclPermissions() {
		return array('cron');
	}
	
	protected function actionSaveSetting($params){
		$response['success']=GO::config()->save_setting($params['name'], $params['value'], $params['user_id']);
		
		return $response;
	}
	
	protected function actionDebug($params){
		
		if(empty(GO::session()->values['debug'])){
			// if(!GO::user()->isAdmin())
			// 	throw new \GO\Base\Exception\AccessDenied("Debugging can only be enabled by an admin. Tip: You can enable it as admin and switch to any user with the 'Switch user' module.");
		
			GO::session()->values['debug']=true;
		}
		
		GO::session()->values['debugSql']=!empty($params['debugSql']);
		
		// The default length of the tail command, when passing the "length" parameter this can be increased or decreased
		$length = 300;
		if(isset($params['length'])){
			$length = $params['length'];
		}
		
		$debugFile = new \GO\Base\Fs\File(GO::config()->file_storage_path.'log/debug.log');
		if(!$debugFile->exists())
			$debugFile->touch(true);

		
		return array(
				'success'=>true, 
				'debugLog'=>$debugFile->tail($length)
				);
	}
	
	protected function actionInfo($params){
		
		if(empty(GO::session()->values['debug'])){
			throw new \GO\Base\Exception\AccessDenied("Debugging can only be enabled by an admin");
		}
			
		$response = array('success'=>true, 'info'=>'');
		
		$info['username']=GO::user()->username;
		$info['config']=GO::config()->get_config_file();
		$info['database']=GO::config()->db_name;
		
		$modules = GO::modules()->getAllModules();		
		foreach($modules as $module){
			if(!isset($info['modules']))
				$info['modules']=$module->name;
			else
				$info['modules'].=', '.$module->name;
		}
		
		$info = array_merge($info,$_SERVER);
		
		
		$response['info']='<table>';
		
		foreach($info as $key=>$value)
			$response['info'] .= '<tr><td>'.$key.':</td><td>'.$value.'</td></tr>';
		
		$response['info'].='</table>';
		
		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_get_clean();
		
		$response['info'].= \GO\Base\Util\StringHelper::sanitizeHtml($phpinfo);
		return $response;
		
	}
	
	protected function actionLink($params) {

		$fromLinks = json_decode($params['fromLinks'], true);
		$toLinks = json_decode($params['toLinks'], true);
		$from_folder_id = isset($params['from_folder_id']) ? $params['from_folder_id'] : 0;
		$to_folder_id = isset($params['to_folder_id']) ? $params['to_folder_id'] : 0;

		foreach ($fromLinks as $fromLink) {
			
			$fromModel = GO::getModel($fromLink['model_name'])->findByPk($fromLink['model_id']);

			foreach ($toLinks as $toLink) {
				$model = GO::getModel($toLink['model_name'])->findByPk($toLink['model_id']);
				$fromModel->link($model, $params['description'], $from_folder_id, $to_folder_id);
			}
		}

		$response['success'] = true;

		return $response;
	}
	
	protected function actionUnlink($params){
		$linkedModel1 = GO::getModel($params['model_name1'])->findByPk($params['id1']);				
		$linkedModel2 = GO::getModel($params['model_name2'])->findByPk($params['id2']);			
		$linkedModel1->unlink($linkedModel2);	
		
		return array('success'=>true);
	}
	
	protected function actionUpdateLink($params){
		$model1 = GO::getModel($params['model_name1'])->findByPk($params['model_id1']);
		$model2 = GO::getModel($params['model_name2'])->findByPk($params['model_id2']);
		$model1->updateLink($model2, array('description'=>$params['description']));
		$model2->updateLink($model1, array('description'=>$params['description']));
		
		return array('success'=>true);
	}

	/**
	 * Get users
	 * 
	 * @param array $params @see \GO\Base\Data\Store::getDefaultParams()
	 * @return  
	 */
	protected function actionUsers($params) {
		
		if(GO::user()->isAdmin())
			GO::config()->limit_usersearch=0;
		
//		GO::config()->limit_usersearch=10;
		
//		if(empty($params['query']) && !empty($params['queryRequired'])){
//			return array(
////					'emptyText'=>"Enter queSry",
//					'success'=>true,
//					'results'=>array()
//			);
//		}
		
		if(!isset($params['limit']))
			$params['limit']=0;
		
		if(!isset($params['start']))
			$params['start']=0;
		
		// Check for the value "limit_usersearch" in the group-office config file and then add the limit.
		if(!empty(GO::config()->limit_usersearch)){
			if($params['limit']>GO::config()->limit_usersearch)
				$params['limit'] = GO::config()->limit_usersearch;			
			
			if($params['start']+$params['limit']>GO::config()->limit_usersearch)
				$params['start']=0;
		}
		
		$store = \GO\Base\Data\Store::newInstance(\GO\Base\Model\User::model(), array('password','digest'));
		$store->setDefaultSortOrder('name', 'ASC');

		$store->getColumnModel()->formatColumn('id', '$model->id', array(), array('t.id'));
		$store->getColumnModel()->formatColumn('name', '$model->name', array(), array('displayName'));
		$store->getColumnModel()->formatColumn('cf', '$model->id.":".$model->name'); //special field used by custom fields. They need an id an value in one.
		
		//only get users that are enabled
		$enabledParam = \GO\Base\Db\FindParams::newInstance()->debugSql();
		if(!empty(\GO::config()->hide_disabled_users)) {
			$enabledParam->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('enabled', true));
		}
		
		$store->setStatement (\GO\Base\Model\User::model()->find($store->getDefaultParams($params, $enabledParam)));
		$response = $store->getData();
		
		if(!empty(GO::config()->limit_usersearch) && $response['total']>GO::config()->limit_usersearch)
			$response['total']=GO::config()->limit_usersearch;	
		
		return $response;
	}

	/**
	 * Get user groups
	 * 
	 */
	protected function actionGroups($params) {
		$store = \GO\Base\Data\Store::newInstance(\GO\Base\Model\Group::model());
		$store->setDefaultSortOrder('name', 'ASC');
		
		$findParams = $store->getDefaultParams($params);
		
//		if(empty($params['manage'])){
//			
//			//permissions are handled differently. Users may use all groups they are member of.
//			$findParams->ignoreAcl();
//			
//			if(!GO::user()->isAdmin()){
//				$findParams->getCriteria()
//								->addCondition('admin_only', 1,'!=')
//								->addCondition('user_id', GO::user()->id,'=','ug');
//				
//				$findParams->joinModel(array(
//						'model'=>"GO\Base\Model\UserGroup",
//						'localTableAlias'=>'t', //defaults to "t"	  
//						'foreignField'=>'group_id', //defaults to primary key of the remote model
//						'tableAlias'=>'ug', //Optional table alias
//	 			));
//			}
//			
//		}
		
		if(!empty($params['hideUserGroups'])) {
			$findParams->getCriteria()->addCondition('isUserGroupFor', null);
		}
		
		
		$store->setStatement (\GO\Base\Model\Group::model()->find($findParams));
		
		$store->getColumnModel()->formatColumn('cf', '$model->id.":".$model->name');//special field used by custom fields. They need an id an value in one.)

				
		return $store->getData();
	}
	
	/**
	 * Get the holidayfiles that are available groups
	 */
	protected function actionHolidays($params) {
		$available = \GO\Base\Model\Holiday::getAvailableHolidayFiles();
		
		$store = new \GO\Base\Data\ArrayStore();
		$store->setRecords($available);
		return $store->getData();
	}

	/**
	 * Todo replace compress.php with this action
	 */
	protected function actionCompress($params) {
		
		GO::session()->closeWriting();
		
		$this->checkRequiredParameters(array('file'), $params);
	
		$file = GO::config()->getCacheFolder()->child(basename($params['file']));

//		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.'cache/'.basename($params['file']));

		if(!$file){
			throw new \GO\Base\Exception\NotFound();
		}
		
		$ext = $file->extension();

		$type = $ext =='js' ? 'application/javascript' : 'text/css';

		$use_compression = GO::config()->use_zlib_compression();

		if($use_compression){
			ob_start();
			ob_start('ob_gzhandler');
		}
		$offset = 30*24*60*60;
		header ("Content-Type: $type");
		header("Expires: " . date("D, j M Y G:i:s ", time()+$offset) . 'GMT');
		header('Cache-Control: cache');
		header('Pragma: cache');
		if(!$use_compression){
			header("Content-Length: ".$file->size());
		}
		readfile($file->path());

		if($use_compression){
			ob_end_flush();  // The ob_gzhandler one

			header("Content-Length: ".ob_get_length());

			ob_end_flush();  // The main one
		}
	}

	protected function actionThumb($params) {

		GO::session()->closeWriting();

		$dir = GO::config()->root_path . 'views/Extjs3/themes/Paper/img/filetype/';
		$url = GO::config()->host . 'views/Extjs3/themes/Paper/img/filetype/';
		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path . $params['src']);
		
		
		if(isset($params['foldericon'])){
			
			$src = $dir . $params['foldericon'].'.svg';
		} else {
		
//		if (is_dir(GO::config()->file_storage_path . $params['src'])) {
//			$src = $dir . 'folder.svg';
//		} else {

			switch (strtolower($file->extension())) {

				case 'svg':
				case 'ico':
				case 'jpg':
				case 'jpeg':
				case 'png':
				case 'gif':
				case 'xmind':
					$src = GO::config()->file_storage_path . $params['src'];
					break;


				case 'tar':
				case 'tgz':
				case 'gz':
				case 'bz2':
				case 'zip':
					$src = $dir . 'zip.svg';
					break;
				case 'odt':
				case 'docx':
				case 'doc':
				case 'htm':
				case 'html':
				case 'dotx':
					$src = $dir . 'doc.svg';

					break;

				case 'odc':
				case 'ods':
				case 'xls':
				case 'xlsx':
				case 'xltx':
					$src = $dir . 'xls.svg';
					break;

				case 'odp':
				case 'pps':
				case 'pptx':
				case 'ppt':
					$src = $dir . 'pps.svg';
					break;
				case 'eml':
				case 'msg':
					$src = $dir . 'eml.svg';
					break;


				case 'log':
					$src = $dir . 'txt.svg';
					break;
				default:
					if (file_exists($dir . strtolower($file->extension()) . '.svg')) {
						$src = $dir . strtolower($file->extension()) . '.svg';
					} else {
						$src = $dir . 'unknown.svg';
					}
					break;
			}
		}

		$file = new \GO\Base\Fs\File($src);
		
		if($file->size() > \GO::config()->max_thumbnail_size*1024*1024){
			throw new \Exception("Image may not be larger than " . \GO\Base\Util\Number::formatSize(\GO::config()->max_thumbnail_size*1024*1024));
		}
		

		$w = isset($params['w']) ? intval($params['w']) : 0;
		$h = isset($params['h']) ? intval($params['h']) : 0;
		$zc = !empty($params['zc']) && !empty($w) && !empty($h);

		$lw = isset($params['lw']) ? intval($params['lw']) : 0;
		$lh = isset($params['lh']) ? intval($params['lh']) : 0;

		$pw = isset($params['pw']) ? intval($params['pw']) : 0;
		$ph = isset($params['ph']) ? intval($params['ph']) : 0;

		if ($file->extension() == 'xmind') {

//			$filename = $file->nameWithoutExtension().'.jpeg';
//
//			if (!file_exists($GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache/' . $filename) || filectime($GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache/' . $filename) < filectime($GLOBALS['GO_CONFIG']->file_storage_path . $path)) {
//				$zipfile = zip_open($GLOBALS['GO_CONFIG']->file_storage_path . $path);
//
//				while ($entry = zip_read($zipfile)) {
//					if (zip_entry_name($entry) == 'Thumbnails/thumbnail.jpg') {
//						require_once($GLOBALS['GO_CONFIG']->class_path . 'filesystem.class.inc');
//						zip_entry_open($zipfile, $entry, 'r');
//						file_put_contents($GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache/' . $filename, zip_entry_read($entry, zip_entry_filesize($entry)));
//						zip_entry_close($entry);
//						break;
//					}
//				}
//				zip_close($zipfile);
//			}
//			$path = 'thumbcache/' . $filename;
		}

		$cacheFilename = 't_'. $w . '_' . $h . '_' . $lw . '_' . $ph. '_' . $pw . '_' . $lw;
		$cacheDir = new \GO\Base\Fs\Folder(GO::config()->orig_tmpdir . 'thumbcache/'.$file->parent()->stripFileStoragePath());
		$cacheDir->create();
		
		if ($zc) {
			$cacheFilename .= '_zc';
		}
//$cache_filename .= '_'.filesize($full_path);
		$cacheFilename .= '_'.$file->name();

		$readfile = $cacheDir->path() . '/' . $cacheFilename;
		$thumbExists = file_exists($cacheDir->path() . '/' . $cacheFilename);
		$thumbMtime = $thumbExists ? filemtime($cacheDir->path() . '/' . $cacheFilename) : 0;
		
		GO::debug("Thumb mtime: ".$thumbMtime." (".$cacheFilename.")");

		if (!empty($params['nocache']) || !$thumbExists || $thumbMtime < $file->mtime() || $thumbMtime < $file->ctime()) {
			
			GO::debug("Resizing image");
			$image = new \GO\Base\Util\Image($file->path());
			if (!$image->load_success) {
				GO::debug("Failed to load image for thumbnailing");
				//failed. Stream original image
				$readfile = $file->path();
			} else {


				if ($zc) {
					$image->zoomcrop($w, $h);
				} else {
					if ($lw || $lh || $pw || $lw) {
						//treat landscape and portrait differently
						$landscape = $image->landscape();
						if ($landscape) {
							$w = $lw;
							$h = $lh;
						} else {
							$w = $pw;
							$h = $ph;
						}
					}
					
					GO::debug($w."x".$h);

					if ($w && $h) {
						$image->resize($w, $h);
					} elseif ($w) {
						$image->resizeToWidth($w);
					} else {
						$image->resizeToHeight($h);
					}
				}
				$image->save($cacheDir->path() . '/' . $cacheFilename);
			}
		}

				header("Expires: " . date("D, j M Y G:i:s ", time() + (86400 * 365)) . 'GMT'); //expires in 1 year
				header('Cache-Control: cache');
				header('Pragma: cache');
				header('Content-Type: ' . $file->mimeType());
				header('Content-Disposition: inline; filename="' . $cacheFilename . '"');
				header('Content-Transfer-Encoding: binary');

		readfile($readfile);


//			case 'pdf':
//				$this->redirect($url . 'pdf.png');
//				break;
//
//			case 'tar':
//			case 'tgz':
//			case 'gz':
//			case 'bz2':
//			case 'zip':
//				$this->redirect( $url . 'zip.png');
//				break;
//			case 'odt':
//			case 'docx':
//			case 'doc':
//				$this->redirect( $url . 'doc.png');
//				break;
//
//			case 'odc':
//			case 'ods':
//			case 'xls':
//			case 'xlsx':
//				$this->redirect( $url . 'spreadsheet.png');
//				break;
//
//			case 'odp':
//			case 'pps':
//			case 'pptx':
//			case 'ppt':
//				$this->redirect( $url . 'pps.png');
//				break;
//			case 'eml':
//				$this->redirect( $url . 'message.png');
//				break;
//
//			case 'htm':
//				$this->redirect( $url . 'doc.png');
//				break;
//
//			case 'log':
//				$this->redirect( $url . 'txt.png');
//				break;
//
//			default:
//				if (file_exists($dir . $file->extension() . '.png')) {
//					$this->redirect( $url . $file->extension() . '.png');
//				} else {
//					$this->redirect( $url . 'unknown.png');
//				}
//				break;
	}
	
	
	/**
	 * Download file from GO::config()->tmpdir/user_id/$path
	 * Because download is restricted from <user_id> subfolder this is secure.
	 * The user_id is appended in the config class.
	 * 
	 * 
	 */
	protected function actionDownloadTempfile($params){		
		
		$inline = !isset($params['inline']) || !empty($params['inline']);
		
		$file = new \GO\Base\Fs\File(GO::config()->tmpdir.$params['path']);
		if($file->exists()){
			\GO\Base\Util\Http::outputDownloadHeaders($file, $inline, !empty($params['cache']));
			$file->output();		
		}else
		{
			echo "File not found!";
		}
	}
	
	/**
	 * Public files are files stored in GO::config()->file_storage_path.'public'
	 * They are publicly accessible.
	 * Public files are cached
	 * 
	 * @param String $path 
	 */
	protected function actionDownloadPublicFile($params){
		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.'public/'.$params['path']);
		
		if($file->exists()){
			\GO\Base\Util\Http::outputDownloadHeaders($file,false,!empty($params['cache']));
			$file->output();		
		}else
		{
			echo "File not found!";
		}
	}
	
	
	protected function actionMultiRequest($params){	  
			echo "{\n";
			
			//$router = new \GO\Base\Router();
			
			$this->checkRequiredParameters(array('requests'), $params);

			$requests = json_decode($params['requests'], true);
			if(is_array($requests)){
				foreach($requests as $responseIndex=>$requestParams){
					ob_start();				
					GO::router()->runController($requestParams);
					echo "\n".'"'.$responseIndex.'" : '.ob_get_clean().",\n";
				}
			}
			echo '"success":true}';	
	}
	
	
//	protected function actionModelAttributes($params){
//		
//		$response['results']=array();
//		
//		$model = GO::getModel($params['modelName']);
//		$labels = $model->attributeLabels();
//		
//		$columns = $model->getColumns();
//		foreach($columns as $name=>$attr){
//			if($name!='id' && $name!='user_id' && $name!='acl_id'){
//				$attr['name']=$name;
//				$attr['label']=$model->getAttributeLabel($name);
//				$response['results'][]=$attr;
//			}
//		}
//		
//		if($model->customfieldsRecord){
//			$columns = $model->customfieldsRecord->getColumns();
//			foreach($columns as $name=>$attr){
//				if($name != 'model_id'){
//					$attr['name']=$name;
//					$attr['label']=$model->customfieldsRecord->getAttributeLabel($name);
//					$response['results'][]=$attr;
//				}
//			}
//		}
//		
//		return $response;		
//	}
	
	protected function actionUpload($params) {

		$tmpFolder = new \GO\Base\Fs\Folder(GO::config()->tmpdir . 'uploadqueue');
//		$tmpFolder->delete();
		$tmpFolder->create();

		$files = \GO\Base\Fs\File::moveUploadedFiles($_FILES['attachments'], $tmpFolder);

		$relativeFiles = array();
		foreach ($files as $file) {
			$relativeFiles[]=str_replace(GO::config()->tmpdir, '', $file->path());
		}

		return array('success' => true, 'files'=>$relativeFiles);
	}
	
	
	protected function actionPlupload($params) {
		
		
		\GO\Base\Component\Plupload::handleUpload();

		//return array('success' => true);
	}
	
	protected function actionPluploads($params){
		
		if(isset($params['addFileStorageFiles'])){
			$files = json_decode($params['addFileStorageFiles'],true);
			foreach($files as $filepath)
				GO::session()->values['files']['uploadqueue'][]=GO::config()->file_storage_path.$filepath;
		}
		
		$response['results']=array();
		
		if(!empty(GO::session()->values['files']['uploadqueue'])){
			foreach(GO::session()->values['files']['uploadqueue'] as $path){
				
				$file = new \GO\Base\Fs\File($path);
				
				$result = array(						
						'human_size'=>$file->humanSize(),
						'extension'=>strtolower($file->extension()),
						'size'=>$file->size(),
						'type'=>$file->mimeType(),
						'name'=>$file->name()
				);
				if($file->isTempFile())
				{
					$result['from_file_storage']=false;
					$result['tmp_file']=$file->stripTempPath();
				}else
				{
					$result['from_file_storage']=true;
					$result['tmp_file']=$file->stripFileStoragePath();
				}
				
				$response['results'][]=$result;
			}
		}
		$response['total']=count($response['results']);
		
		unset(GO::session()->values['files']['uploadqueue']);
		
		return $response;
	}
	
	protected function actionSpellCheck($params) {
		
		if (!isset($params['lang']))
			$params['lang'] = GO::session()->values['language'];
		
		$oldLang = GO::language()->setLanguage($params['lang']);
		
		$pspellLang = GO::t('pspell_lang', 'base', 'common', $found);
		
		if(!$found)
			$pspellLang = $params['lang'];

		if (   !isset($params['tocheck'])
			|| empty($params['tocheck'])
			|| !function_exists('pspell_new')
		) {
			$response['errorcount'] = 0;
			$response['text'] = '';
		} else {

			$mispeltwords = \GO\Base\Util\SpellChecker::check($params['tocheck'], $pspellLang);
			if (!empty($mispeltwords)) {
				$response['errorcount'] = count($mispeltwords);
				$response['text'] = \GO\Base\Util\SpellChecker::replaceMisspeltWords($mispeltwords, $params['tocheck']);
			} else {
				$response['errorcount'] = 0;
				$response['text'] = $params['tocheck'];
			}
		}

		return $response;
	}
	
	
	
	protected function actionSaveState($params){
		//close writing to session so other concurrent requests won't be locked out.
		GO::session()->closeWriting();
		
		if(isset($params['values'])){
			$values = json_decode($params['values'], true);

			if(!is_array($values)){
				error_log ("Invalid value for Core::actionSaveState: ".var_export($params, true));
			}else
			{
				foreach($values as $name=>$value){

					$state = \GO\Base\Model\State::model()->findByPk(array('name'=>$name,'user_id'=>GO::user()->id));

					if(!$state){
						$state = new \GO\Base\Model\State();
						$state->name=$name;
					}

					$state->value=$value;
					$state->save();
				}
			}
		}
		$response['success']=true;
		echo json_encode($response);
	}
	
	
	protected function actionAbout($params){	
		$response['data']['about']=GO::t("Version: {version}<br/>
Copyright (c) 2003-{current_year}, {company_name}<br/>
All rights reserved.");
		
		if(GO::config()->product_name=='Group-Office')
			$response['data']['about']=str_replace('{company_name}', 'Intermesh B.V.', $response['data']['about']);
		else
			$response['data']['about']=str_replace('{company_name}', GO::config()->product_name, $response['data']['about']);
		
		
		$strVersion = GO::config()->version;
		
//		$rssUrl = "https://sourceforge.net/api/file/index/project-id/76359/mtime/desc/limit/20/rss";
//		
//		$httpClient = new  \GO\Base\Util\HttpClient();
//		
//		$res = $httpClient->request($rssUrl);	
//	
//		$sXml = simplexml_load_string($res);	
//		
//			if($sXml){
//
//			$firstItem = $sXml->channel->item[0];		
//
//			$link = (string) $firstItem->link;
//
//			preg_match('/-([0-9]\.[0-9]{1,2}\.[0-9]{1,2})\./', $link, $matches);
//
//			$version = $matches[1];
//
//			$ret = version_compare(GO::config()->version, $version);
//
//			if($ret!== -1){
//				$strVersion .= " <span style=\"color: red\">(v$version available)</span>";
//			}else
//			{
//				$strVersion .= " (latest)";
//			}
//		}
		
		
		$response['data']['about']=str_replace('{version}', $strVersion, $response['data']['about']);
		$response['data']['about']=str_replace('{current_year}', date('Y'), $response['data']['about']);
		$response['data']['about']=str_replace('{product_name}', GO::config()->product_name, $response['data']['about']);

		
		$response['data']['mailbox_usage']=\GO\Base\Util\Number::formatSize((int) GO::config()->get_setting('mailbox_usage'));
		$response['data']['file_storage_usage']= \GO\Base\Util\Number::formatSize((int) GO::config()->get_setting('file_storage_usage')) .' / '.\GO\Base\Util\Number::formatSize(GO::config()->quota * 1024);
		
		$response['data']['database_usage']=\GO\Base\Util\Number::formatSize((int) GO::config()->get_setting('database_usage'));
		$response['data']['total_usage']=\GO\Base\Util\Number::formatSize((int)GO::config()->get_setting('database_usage') + (int) GO::config()->get_setting('file_storage_usage') + (int) GO::config()->get_setting('mailbox_usage'));
		$response['data']['has_usage']=$response['data']['total_usage']>0;
		
		$response['success']=true;
		
		return $response;
	}
	
	
 /* MOVED TO CRONFILE IN Email/Cron/EmailReminders.php
  * 
  * Run a cron job every 5 minutes. Add this to /etc/cron.d/groupoffice :
  *
  STAR/5 * * * * root php /usr/share/groupoffice/groupofficecli.php -c=/path/to/config.php -r=core/cron
  *
  * Replace STAR with a *.
	*
	* @DEPRECATED
  */
//	protected function actionCron($params){		
//		
//		$this->requireCli();
//		GO::session()->runAsRoot();
//		
//		$this->_emailReminders();
//		
//		$this->fireEvent("cron");
//	}
	
// 	/**
// 	 * MOVED TO CRONFILE IN Email/Cron/EmailReminders.php
// 	 *
// 	 *
// 	 *  @DEPRECATED
// 	 */
//	private function _emailReminders(){
//		$usersStmt = \GO\Base\Model\User::model()->find();
//		while ($userModel = $usersStmt->fetch()) {
//			if ($userModel->mail_reminders==1) {
//				$remindersStmt = \GO\Base\Model\Reminder::model()->find(
//					\GO\Base\Db\FindParams::newInstance()
//						->joinModel(array(
//							'model' => 'GO\Base\Model\ReminderUser',
//							'localTableAlias' => 't',
//							'localField' => 'id',
//							'foreignField' => 'reminder_id',
//							'tableAlias' => 'ru'								
//						))
//						->criteria(
//							\GO\Base\Db\FindCriteria::newInstance()
//								->addCondition('user_id', $userModel->id, '=', 'ru')
//								->addCondition('time', time(), '<', 'ru')
//								->addCondition('mail_sent', '0', '=', 'ru')
//						)
//				);
//
//				while ($reminderModel = $remindersStmt->fetch()) {
////					$relatedModel = $reminderModel->getRelatedModel();
//					
////					var_dump($relatedModel->name);
//					
////					$modelName = $relatedModel ? $relatedModel->localizedName : GO::t("Unknown");
//					$subject = GO::t("Reminder").': '.$reminderModel->name;
//
//					$time = !empty($reminderModel->vtime) ? $reminderModel->vtime : $reminderModel->time;
//			
//					date_default_timezone_set($userModel->timezone);
//					
//					$body = GO::t("Time").': '.date($userModel->completeDateFormat.' '.$userModel->time_format,$time)."\n";
//					$body .= GO::t("Name").': '.str_replace('<br />',',',$reminderModel->name)."\n";
//			
////					date_default_timezone_set(GO::user()->timezone);
//					
//					$message = \GO\Base\Mail\Message::newInstance($subject, $body);
//					$message->addFrom(GO::config()->webmaster_email,GO::config()->title);
//					$message->addTo($userModel->email,$userModel->name);
//					\GO\Base\Mail\Mailer::newGoInstance()->send($message);
//					
//					$reminderUserModelSend = \GO\Base\Model\ReminderUser::model()
//						->findSingleByAttributes(array(
//							'user_id' => $userModel->id,
//							'reminder_id' => $reminderModel->id
//						));
//					$reminderUserModelSend->mail_sent = 1;
//					$reminderUserModelSend->save();
//				}
//				
//				date_default_timezone_set(GO::user()->timezone);
//			}
//		}
//	}
	
	protected function actionThemes($params){
		$store = new \GO\Base\Data\ArrayStore();
		
		$view = new \GO\Base\View\Extjs3();
		$themes = $view->getThemeNames();
		
		foreach($themes as $theme){
			$store->addRecord(array('theme'=>$theme, 'label'=>str_replace('Group-Office', GO::config()->product_name, $theme)));
		}
		
		return $store->getData();
	}
	
	protected function actionModules($params){
		$store = new \GO\Base\Data\ArrayStore();
		
		$modules = GO::modules()->getAllModules(true);
		
		foreach($modules as $module){
			$translated = GO::t("name",  $module->name, $module->package);
			$store->addRecord(array('id'=>$module->name,'name'=>$translated));
		}
		
		return $store->getData();
	}
	
	
	
	public function actionPasteUpload($model_id, $model_name, $filename, $filetype){
		
		$site = GO::getModel($model_name)->findByPk($model_id);
		
		
		$type = explode('/', $filetype);
		$extension=$type[1];
		
		
		$_FILES['pastedFile']['name']=$filename.'.'.$extension;
		
		$file = $site->filesFolder->addUploadedFile($_FILES["pastedFile"]);	
			
		
		$response = new \GO\Base\Data\JsonResponse(array(
				'success'=>true,
				'file_id'=>$file->id,
				'path'=>substr($file->path,strlen($site->filesFolder->path)+1)
		));
		
		echo $response;
		
		
	}
	
	public function actionPasteUploadTemporary($filename, $filetype){
		
		
		$type = explode('/', $filetype);
		$extension=$type[1];
		
		
		$_FILES['pastedFile']['name']=$filename.'.'.$extension;
		
		
		$file = new GO\Base\Fs\File($_FILES['pastedFile']['tmp_name']);
		
		$file->move(GO::config()->getTempFolder(), $filename.'.'.$extension, true);
			
		
		$response = new \GO\Base\Data\JsonResponse(array(
				'success'=>true,
				'data'=>array(
						'tmp_file'=>$file->stripTempPath(),
						'name'=>$file->name(),
						'size'=>$file->size(), 
						'type'=>$file->mimeType(),
						'extension'=>$file->extension(),
						'human_size'=>$file->humanSize(),
						'from_file_storage'=>false						
						)
		));
		
		echo $response;
		
		
	}
	
	/**
	 * Create an url to the given model with the given id.
	 * The format of the parameter needs to be: "ModelType:ModelId" ("GO\Projects2\Model\Project:2")
	 * 
	 * @param StringHelper $modelTypeAndKey Example: "GO\Projects2\Model\Project:2"
	 */
	public function actionCreateModelUrl($modelTypeAndKey){
		$response = new \GO\Base\Data\JsonResponse(array(
			'success'=>true,
			'url'=>GO::createExternalUrl('links', 'openModelLink', $modelTypeAndKey)
		));
		
		echo $response;
	}
	
	/**
	 * Send an email
	 * 
	 * @param string $email
	 * @param string $subject
	 * @param string $body
	 * @param array $attachments Array like this: (The given className needs to implement the GO\Base\Mail\SwiftAttachableInterface)
	 *	array(
	 *		array(
	 *			'className'=>'GO\Files\Model\File',
	 *			'pk'=>3',
	 *			'altName'=>'New attachment name'
	 *		),
	 *		array(
	 *			'className'=>'GO\Files\Model\File',
	 *			'pk'=>4',
	 *			'altName'=>'New attachment name2'
	 *		)
	 *	)
	 */
	public function actionSendSystemEmail($email, $subject, $body, $attachments=array()){
		
		$response = array('success'=>false);
		
		//Build system email and mail with the given files
		$systemMessage = new GO\Base\Mail\SystemMessage($subject,$body);
		
		foreach($attachments as $attachmentSpec) {
			
			if(!empty($attachmentSpec['className']) &&  !empty($attachmentSpec['pk'])){
				
				$className = $attachmentSpec['className'];
				$record = $className::model()->findByPk($attachmentSpec['pk']);
				
				if($record){
					
					$altName = null;
					if(!empty($attachmentSpec['altName'])){
						$altName = $attachmentSpec['altName'];
					}

					$swiftAttachment = $record->getAttachment($altName);
					
					if($swiftAttachment !== false){
						$systemMessage->attach($swiftAttachment);
					}
				}
			}			
		}
				
		$response['success'] = $systemMessage->send()?true:false;
		
		if(!$response['success']){
			$response['feedback'] = GO::t('Could not send email');
		}

		echo new \GO\Base\Data\JsonResponse($response);
	}
	
	
}
