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

use \GO\Base\Db\FindParams;
use \GO\Files\Model\Version;

class VersionController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Files\Model\Version';

	protected function actionDownload($params){
		$version = \GO\Files\Model\Version::model()->findByPk($params['id']);
		$file = $version->getFilesystemFile();
		\GO\Base\Util\Http::outputDownloadHeaders($file);
		$file->output();
	}
	
	/**
	 * Will find all versioning files and put the filesize in the database
	 */
	protected function actionRecalculate() {
		$fp = FindParams::newInstance()->ignoreAcl();
		$stmt = Version::model()->find($fp);
		
		$success = 0; $failed = 0;
		while($version = $stmt->fetch()) {
			$path = \GO::config()->file_storage_path.$version->path;
			if(file_exists($path)) {
				$pdo_statement = \GO::getDbConnection()->query('UPDATE '.Version::model()->tableName(). ' SET `size_bytes` = '.filesize($path).';');
				if($pdo_statement->execute()) {
					$success++;
				} else
					$failed++;
			}
		}
		echo $success.' Done<br> '.$failed. ' Failed';
	}
	
	protected function getStoreParams($params) {		
		$findParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();
		$findParams->getCriteria()->addCondition('file_id', $params['file_id']);		
		
		return $findParams;
	}
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		
		$columnModel->formatColumn('user_name', '$model->user->name');
		
		return parent::formatColumns($columnModel);
	}
}
