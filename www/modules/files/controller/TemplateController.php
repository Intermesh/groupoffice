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
 * The GO\Files\Model\Template controller
 *
 * @package GO.modules.files
 * @version $Id: GO\Files\Model\Template.php 7607 2011-09-29 08:42:37Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\files\Controller;


class TemplateController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Files\Model\Template';

	protected function beforeSubmit(&$response, &$model, &$params) {

		if (isset($_FILES['attachments']['tmp_name'][0]) && is_uploaded_file($_FILES['attachments']['tmp_name'][0])) {
			$file = new \GO\Base\Fs\File($_FILES['attachments']['tmp_name'][0]);
			// Only used to get the extension of the uploaded file name, the file isn't accessed
			// nosemgrep: go-path-from-request
			$fileWithName = new \GO\Base\Fs\File($_FILES['attachments']['name'][0]);
			$model->content = $file->contents();
			$model->extension = $fileWithName->extension();
		} else if($model->isNew()) {
			$response['validationErrors'] = array('attachments'=> \GO::t("Upload failed"));
			$response['success'] = false;
			$response['feedback'] = \GO::t("The upload failed! Ask the server manager for what wrong", "files");
			return false;
		}
		

		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('type', 'GO\Base\Fs\File::getFileTypeDescription($model->extension)');
		
		return parent::formatColumns($columnModel);
	}
	
	protected function getStoreExcludeColumns() {
		return array('content');
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		unset($response['data']['content']);
		
		return parent::afterLoad($response, $model, $params);
	}
	
	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name','ASC');
		return parent::beforeStore($response, $params, $store);
	}
	
	protected function actionDownload($params){
		$template = \GO\Files\Model\Template::model()->findByPk($params['id']);
		
	  \GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\Fs\File($template->name.'.'.$template->extension));
		
		echo $template->content;
	}
	
	protected function actionCreateFile($params){
		
		$filename = \GO\Base\Fs\File::stripInvalidChars($params['filename']);
		if(empty($filename))
			throw new \Exception("Filename can not be empty");
		
		$template = \GO\Files\Model\Template::model()->findByPk($params['template_id']);
		
		if(!$template) {
			throw new \GO\Base\Exception\NotFound();
		}

		$folder = \GO\Files\Model\Folder::model()->findByPk($params['folder_id']);
		if(!$folder) {
			throw new \GO\Base\Exception\NotFound();
		}

		if(!$folder->checkPermissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION)) {
			throw new \GO\Base\Exception\AccessDenied();
		}

		$path = \GO::config()->file_storage_path.$folder->path.'/'.$filename;
		if(!empty($template->extension))
			$path .= '.'.$template->extension;

		$fsFile = new \GO\Base\Fs\File($path);
		$fsFile->checkFileStoragePath();

		// Never overwrite an existing file. Replacing would bypass the WRITE permission check on the existing file.
		if($fsFile->exists() || $folder->hasFile($fsFile->name())) {
			throw new \Exception(sprintf(\GO::t("Filename %s already exists", "files"), $fsFile->stripFileStoragePath()));
		}

		if(!\GO\Files\Model\File::checkQuota(strlen($template->content))) {
			throw new \GO\Base\Exception\InsufficientDiskspace();
		}

		$fsFile->putContents($template->content);

		try {
			$fileModel = $folder->addFile($fsFile->name());
		} catch(\Throwable $e) {
			$fsFile->delete();
			throw $e;
		}

		return array('id'=>$fileModel->id, 'success'=>true);
	}

}
