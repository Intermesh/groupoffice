<?php


namespace GO\Site\Controller;


class MultifileController extends \GO\Base\Controller\AbstractJsonController {
	
	public function actionStore($params){
		
		if(!isset($params['model_id']))
			Throw new \GO\Base\Exception\NotFound('NO MODEL ID GIVEN');
		
		if(!isset($params['field_id']))
			Throw new \GO\Base\Exception\NotFound('NO FIELD ID GIVEN');
		
		// When a file is added to the multifileview
		if(isset($params['addFileStorageFilesById'])){
			$files = json_decode($params['addFileStorageFilesById'],true);
			foreach($files as $fileId){
				\GO\Site\Model\MultifileFile::addFromFileSelector($fileId, $params['model_id'], $params['field_id']);
			}
		}
		
		if(isset($params['delete_keys'])){
			$deleteSuccess = true;
			
			$keys = json_decode($params['delete_keys']);
			unset($params['delete_keys']);
			
			foreach ($keys as $fileId){
				\GO\Site\Model\MultifileFile::deleteFromFileSelector($fileId, $params['model_id'], $params['field_id']);
			}
		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->select('t.*,mf.order,mf.model_id,mf.field_id');
		$findParams->ignoreAcl();
		$findParams->order('mf.order');
		$findParams->joinModel(array(
			'model' => 'GO\Site\Model\MultifileFile',
			'localTableAlias' => 't',
			'localField' => 'id',
			'foreignField' => 'file_id',
			'tableAlias' => 'mf',
			'criteria' => \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('model_id', $params['model_id'],'=','mf')
						->addCondition('field_id', $params['field_id'],'=','mf')
		));
		
		$model = new \GO\Files\Model\File();
		
		$columnModel = new \GO\Base\Data\ColumnModel($model);
		$columnModel->formatColumn('thumb_url', '$model->getThumbUrl(array("lw"=>100, "ph"=>100, "zc"=>1))');
		
		$store = new \GO\Base\Data\DbStore('GO\Files\Model\File',$columnModel,$params,$findParams);

		$response = $this->renderStore($store);
		
		if(isset($deleteSuccess) && $deleteSuccess){
			$response['deleteSuccess']=true;
		}
		
		echo $response;
	}
	
	public function getThumbURL($urlParams=array("lw"=>100, "ph"=>100, "zc"=>1)) {
		
		$urlParams['filemtime']=$this->mtime;
		$urlParams['src']=$this->path;
		return \GO::url('core/thumb', $urlParams);
	}
	
	
	public function actionAdd($params){
		
		$model = new \GO\Site\Model\MultifileFile();
		$model->setAttributes($params);
		$model->save();
		
		echo $this->renderSubmit($model);
	}

	/**
	 * Save the sort of the multifiles
	 * 
	 * @param array $params
	 * @throws \GO\Base\Exception
	 */
	public function actionSaveSort($params){

		if(!isset($params['sort']))
			Throw new \GO\Base\Exception('Failed to save the sort.');
		
		$sortOrder = json_decode($params['sort']);
		
		foreach($sortOrder as $order){
			$file = \GO\Site\Model\MultifileFile::model()->findByPk(array(
				'model_id' => $order->model_id,
				'field_id' => $order->field_id,
				'file_id' => $order->file_id
			));
			
			if(!$file)
				continue;
			
			$file->order = $order->sort_index;
			$file->save();
		}
		
		echo $this->renderJson(array('success'=>true));
	}
}
