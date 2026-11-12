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
				$this->checkFile($fileId);
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
		// Folders inherit the ACL of their parent, so the ACL can't be joined. Only list the files the user may read.
		$findParams->getCriteria()->addInCondition('id', $this->readableFileIds($params['model_id'], $params['field_id']));
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
	
	/**
	 * The ids of the files of a multi file field that the user may read
	 *
	 * @return int[]
	 */
	private function readableFileIds($modelId, $fieldId): array
	{
		$ids = [0];
		$stmt = \GO\Site\Model\MultifileFile::model()->findByAttributes(['model_id' => $modelId, 'field_id' => $fieldId]);
		foreach ($stmt as $multifileFile) {
			$file = \GO\Files\Model\File::model()->findByPk($multifileFile->file_id, false, true);
			if ($file && $file->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)) {
				$ids[] = (int) $file->id;
			}
		}
		return $ids;
	}

	/**
	 * Only files the user may read can be added
	 *
	 * @throws \GO\Base\Exception\AccessDenied
	 */
	private function checkFile($fileId): void
	{
		$file = \GO\Files\Model\File::model()->findByPk($fileId, false, true);
		if (!$file || !$file->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)) {
			throw new \GO\Base\Exception\AccessDenied();
		}
	}

	public function getThumbURL($urlParams=array("lw"=>100, "ph"=>100, "zc"=>1)) {
		
		$urlParams['filemtime']=$this->mtime;
		$urlParams['src']=$this->path;
		return \GO::url('core/thumb', $urlParams);
	}
	
	
	public function actionAdd($params){
		
		$this->checkFile($params['file_id'] ?? 0);

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
