<?php

namespace GO\Files\Model;

use GO;
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
		$t->save();
	}

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
		$t->save();

	}


}