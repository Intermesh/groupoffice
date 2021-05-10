<?php
//
//namespace GO\Core\Controller;
//
//use GO;
//use GO\Base\Data\Store;
//use GO\Base\Db\FindParams;
//use GO\Base\Model\ModelType;
//
//class SearchController extends \GO\Base\Controller\AbstractModelController{
//	protected $model = 'GO\Base\Model\SearchCacheRecord';
//
//	protected function beforeStore(&$response, &$params, &$store) {
//		//handle deletes for searching differently
//
//		if(!empty($params['delete_keys'])){
//
//			try{
//				$keys = json_decode($params['delete_keys'], true);
//				unset($params['delete_keys']);
//				foreach($keys as $key){
//					$key = explode(':',$key);
//
//					$linkedModel = \GO::getModel($key[0])->findByPk($key[1]);
//					if($linkedModel)
//						$linkedModel->delete();
//				}
//				unset($params['delete_keys']);
//				$response['deleteSuccess']=true;
//			}
//			catch(\Exception $e){
//				$response['deleteSuccess']=false;
//				$response['deleteFeedback']=$e->getMessage();
//			}
//		}
////
////		//search query is required
////		if(empty($params["query"])){
////			return false;
////		}else
////		{
////			//we'll do a full text search in getStoreParams
//////			$params['match']=$params["query"];
//////			unset($params["query"]);
////		}
////
//
//		return parent::beforeStore($response, $params, $store);
//	}
//
//	protected function getStoreParams($params) {
//		$filesupport = false;
//
//		if(isset($params['filesupport'])){
//			$filesupport = $params['filesupport']==="true" || $params['filesupport']==="1"?true:false;
//		}
//
//		$forLinks = isset($params['for_links']) && ($params['for_links'] === "true" || $params['for_links'] === "1");
//		$types = array();
//		$storeParams = FindParams::newInstance();
//		if(isset($params['model_names'])){
//			$model_names = json_decode($params['model_names'], true);
//
//			foreach($model_names as $model_name){
//				if(class_exists($model_name))
//					$types[]=\GO::getModel($model_name)->modelTypeId();
//			}
//		}
//
//		if(!empty($params['type_filter'])) {
//			if(isset($params['types'])) {
//				$types= json_decode($params['types'], true);
//			}else {
//				$types = \GO::config()->get_setting('link_type_filter', \GO::user()->id);
//				$types = empty($types) ? array() : explode(',', $types);
//			}
//
//			//only search for available types. eg. don't search for contacts if the user doesn't have access to the addressbook
//			if(!count($types))
//					$types=$this->_getAllModelTypes($filesupport, $forLinks);
//
//			if(!isset($params['no_filter_save']) && isset($params['types']))
//				\GO::config()->save_setting ('link_type_filter', implode(',',$types), \GO::user()->id);
//		}else if(!count($types)) {
//			$types=$this->_getAllModelTypes($filesupport, $forLinks);
//		}
//
//		$disableLinksFor = GO::config()->disable_links_for ? GO::config()->disable_links_for : array();
//		foreach ($disableLinksFor as $disabledLinkFor) {
//			$id = ModelType::model()->findByModelName($disabledLinkFor);
//			$modelTypePosition = array_search($id, $types);
//			unset($types[$modelTypePosition]);
//		}
//
//		$storeParams->getCriteria()->addInCondition('model_type_id', $types);
//
//		if (!empty($params['minimumWritePermission']) && $params['minimumWritePermission']!='false')
//			$storeParams->getCriteria()->addCondition('level',\GO\Base\Model\Acl::WRITE_PERMISSION,'>=','core_acl_group');
//
////		$subCriteria = \GO\Base\Db\FindCriteria::newInstance();
////
////		if(strlen($params['match'])<4){
////			$subCriteria->addCondition('keywords', '%'.trim($params['match'],' *%').'%', 'LIKE','t',false);
////		}else
////		{
////			$str='+'.preg_replace('/[\s]+/',' +', $params['match']);
////			$subCriteria->addMatchCondition(array('keywords'), $str);
////		}
////
////		$storeParams->getCriteria()->mergeWith($subCriteria);
//
//		return $storeParams;
//	}
//
//	private function _getAllModelTypes($filesupport=false, $forLinks=false){
//		$types=array();
//		$stmt = ModelType::model()->find();
//		while($modelType = $stmt->fetch()){
//			if(class_exists($modelType->name)){
//				$model = \GO::getModel($modelType->name);
//				$module = $modelType->name == "GO\Base\Model\User" ? "users" : $modelType->moduleRel->name;
//				if(GO::modules()->{$module}){
//					if((!$filesupport || $filesupport && $model->hasFiles()) && (!$forLinks || $modelType->name != 'GO\\Comments\\Model\\Comment')) {
//						$types[]=$modelType->id;
//					}
//				}
//			}
//		}
//		return $types;
//
//	}
//
//	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
//		$columnModel->formatColumn('iconCls', '"go-model-".str_replace(\'\\\\\',\'_\',$model->model_name)');
//		$columnModel->formatColumn('name_and_type', '"(".$model->type.") ".$model->name');
//		$columnModel->getColumn('name_and_type')->setModelFormatType('raw');
//		$columnModel->formatColumn('model_name_and_id', '$model->model_name.":".$model->model_id');
//		return parent::formatColumns($columnModel);
//	}
//
//		protected function actionModelTypes($params){
//
//
//		$filesupport = false;
//
//		if(isset($params['filesupport']))
//			$filesupport = $params['filesupport']==="true" || $params['filesupport']==="1"?true:false;
//
//		$forLinks = isset($params['for_links']) && ($params['for_links'] === "true" || $params['for_links'] === "1");
//
//		$theseTypesOnly = false;
//
//		if(isset($params['filter_model_type_ids'])){
//			$theseTypesOnly = json_decode($params['filter_model_type_ids']);
//		}
//
//		$findParams = FindParams::newInstance();
//
//		if(!empty($theseTypesOnly)){
//			$findParams->getCriteria()->addInCondition('id', $theseTypesOnly);
//		}
//
//		$stmt = ModelType::model()->find($findParams);
//
//		$typesString = \GO::config()->get_setting('link_type_filter',\GO::user()->id);
//		$typesArr = explode(',',$typesString);
//
//		$types=array();
//		while($modelType = $stmt->fetch()){
//			if(class_exists($modelType->name)){
//				$model = \GO::getModel($modelType->name);
//
//				$module = $modelType->name == "GO\Base\Model\User" ? "users" : $modelType->moduleRel->name;
//
//				if(GO::modules()->{$module}){
//
//					if((!$filesupport || $filesupport && $model->hasFiles()) && (!$forLinks || $modelType->name != 'GO\\Comments\\Model\\Comment')) {
//						$types[$model->localizedName.$modelType->id]=array('id'=>$modelType->id, 'model_name'=>$modelType->name, 'name'=>$model->localizedName, 'checked'=>in_array($modelType->id,$typesArr));
//					}
//				}
//			}else
//			{
//				\GO::debug("Missing class ".$modelType->name);
//			}
//		}
//
//		ksort($types);
//
//		$response['total']=count($types);
//		$response['results']=array_values($types);
//
//
//		return $response;
//	}
//
//
//
//	protected function actionLinks($params){
//
//		$model = \GO::getModel($params['model_name'])->findByPk($params['model_id']);
//
//
//		$store = Store::newInstance(\GO\Base\Model\SearchCacheRecord::model());
//
//		//$model->unlink($model);
//
//		if(!empty($params['unlinks'])){
//			$keys = json_decode($params['unlinks'], true);
//
//			foreach($keys as $key){
//				$key = explode(':',$key);
//
//				$linkedModel = \GO::getModel($key[0])->findByPk($key[1]);
//				$model->unlink($linkedModel);
//			}
//		}
//
////		if(!empty($params['delete_keys'])){
////
////			$keys = json_decode($params['delete_keys'], true);
////
////			foreach($keys as $key){
////				$key = explode(':',$key);
////
////				$linkedModel = \GO::getModel($key[0])->findByPk($key[1]);
////				$linkedModel->delete();
////			}
////		}
//
//		//we'll do a full text search in getStoreParams
////		$params['match']=isset($params["query"]) ? $params["query"] : '';
////		unset($params["query"]);
//
//		$storeParams = $store->getDefaultParams($params)->select("t.*,l.description AS link_description");
//
//		$storeParams->mergeWith($this->getStoreParams($params));
//
//		//if(!empty($params['folder_id']))
//		$storeParams->getCriteria ()->addCondition ('folder_id', $params['folder_id'],'=','l');
//
//		if(isset($params['types'])){
//			$types = json_decode($params['types'], true);
//			if(count($types))
//				$storeParams->getCriteria ()->addInCondition ('model_type_id', $types);
//		}
//
//
//		$stmt = \GO\Base\Model\SearchCacheRecord::model()->findLinks($model, $storeParams);
//		$store->setStatement($stmt);
//
//		$cm = $store->getColumnModel();
//		$cm->formatColumn('iconCls', '"go-model-".str_replace(\'\\\\\',\'_\',$model->model_name)');
//		$cm->formatColumn('name_and_type', '"(".$model->type.") ".$model->name');
//		$cm->formatColumn('model_name_and_id', '$model->model_name.":".$model->model_id');
//		$cm->formatColumn('link_count','\GO::getModel($model->model_name)->countLinks($model->model_id)');
//
//		$data = $store->getData();
//
//		$data['permissionLevel']=$model->getPermissionLevel();
//		return $data;
//	}
//}
