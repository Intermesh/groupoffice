<?php

namespace GO\Files\Model;

use GO;
use go\core\http\Exception;
use go\core\util\DateTime;
use go\core\orm\EntityType;
use GO\Files\Model\File;
use GO\Files\Model\Folder;

/**
 * The Folder model
 *
 * Top level folders with parent_id=0 are readable to everyone with access to
 * the files module automatically. This is done in the validate() function of this model.
 *
 * A shared folder has an acl_id set. When the system checks permissions it will
 * recursively search up the tree until it finds a folder that has an acl_id.
 *
 * @property int $id
 * @property int $parentId
 * @property int $aclId
 * @property int $entityId
 * @property int $entityTypeId
 * @property int $deletedBy
 * @property DateTime $deletedAt
 * @property StringHelper $name
 * @property StringHelper $fullPath Relative path from \GO::config()->file_storage_path
 */
final class TrashedItem extends \GO\Base\Db\ActiveRecord
{
	public function tableName()
	{
		return 'fs_trash';
	}

	public function aclField()
	{
		return 'aclId';
	}

	public function relations() {
		return array(
			'parent' => array('type' => self::BELONGS_TO, 'model' => 'GO\Files\Model\Folder', 'field' => 'parent_id'),
			'deletedByUser' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\User', 'field' => 'deletedBy'),
			'entityType' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\ModelType', 'field' => 'entityTypeId'),
		);
	}


	/**
	 * A folder has just been moved to Trash. Create a fs_trash record if it does not exist yet
	 *
	 * @param \GO\Files\Model\Folder $folder
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 */
	public function saveForFolder(Folder $folder): void
	{
		$entityType = EntityType::findByName('Folder');

		$t = self::findSingleByAttributes(['entityTypeId' => $entityType->getId(), 'entityId' => $folder->id]);
		if ($t) {
			return;
		}
		$t = new self;
		$t->aclId = $folder->acl_id;
		$t->parentId = $folder->parent_id;
		$t->entityTypeId = $entityType->getId();
		$t->entityId = $folder->id;
		$t->deletedAt = gmdate('Y-m-d H:i:s');
		$t->deletedBy = GO()->getUserId();
		$t->name = $folder->name;
		$t->fullPath = $folder->parent->path;
		$t->save(true);
	}

	/**
	 * A file has just been trashed. Create as fs_trash record if it does not exist
	 *
	 * @param \GO\Files\Model\File $file
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws \go\core\db\DbException
	 */

	public function saveForFile(File $file): void
	{
		$entityType = EntityType::findByName('File');

		$t = self::findSingleByAttributes(['entityTypeId' => $entityType->getId(), 'entityId' => $file->id]);
		if ($t) {
			return;
		}
		$folder = Folder::model()->findByPk($file->folder_id);
		$t = new self;
		$t->aclId = $folder->acl_id;
		$t->parentId = $folder->id;
		$t->entityTypeId = $entityType->getId();
		$t->entityId = $file->id;
		$t->deletedAt = gmdate('Y-m-d H:i:s');
		$t->deletedBy = GO()->getUserId();
		$t->name = $file->name;
		$t->fullPath = $folder->path;
		$t->save(true);

	}

	/**
	 * Restore a file or folder to its original position. If either does not exist, throw an error.
	 *
	 * @return void
	 * @throws Exception
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws GO\Base\Exception\RelationDeleteRestrict
	 * @throws \go\core\db\DbException
	 */
	public function restore(): void
	{
		$parentFolder = Folder::model()->findByPath($this->fullPath, true);

		$entityType = EntityType::findById($this->entityTypeId);
		if ($entityType->getName() == 'File') {
			$f = \GO\Files\Model\File::model()->findByPk($this->entityId, false, true);
			if (!$f) {
				throw new Exception(404, GO()->t("File not found."));
			}
		} else {
			$f = \GO\Files\Model\Folder::model()->findByPk($this->entityId, false, true);
			if (!$f) {
				throw new Exception(404, GO()->t("Folder not found."));
			}
		}
		if ($f->move($parentFolder, true, true)) {
			$this->delete();
		}
	}

	public function deletePermanently(): void
	{
		$entityType = EntityType::findById($this->entityTypeId);
		if ($entityType->getName() == 'File') {
			$f = \GO\Files\Model\File::model()->findByPk($this->entityId, false, true);
		} else {
			$f = \GO\Files\Model\Folder::model()->findByPk($this->entityId, false, true);
		}
		if (isset($f)) {
			$f->delete(true);
		}
		$this->delete(true);
	}

}