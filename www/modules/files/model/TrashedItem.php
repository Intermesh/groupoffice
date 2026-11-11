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
	 * @param \GO\Files\Model\Folder $originalParent The folder it was in before it was moved to trash
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 */
	public function saveForFolder(Folder $folder, Folder $originalParent): void
	{
		$entityType = EntityType::findByName('Folder');

		$t = self::findSingleByAttributes(['entityTypeId' => $entityType->getId(), 'entityId' => $folder->id]);
		if ($t) {
			return;
		}
		$t = new self;
		// the folder's own ACL or the one it inherited in its original place
		$t->aclId = $folder->acl_id > 0 ? $folder->acl_id : $originalParent->findAclId();
		$t->parentId = $originalParent->id;
		$t->entityTypeId = $entityType->getId();
		$t->entityId = $folder->id;
		$t->deletedAt = gmdate('Y-m-d H:i:s');
		$t->deletedBy = GO()->getUserId();
		$t->name = $folder->name;
		$t->fullPath = $originalParent->path;
		$t->save(true);

		$this->touchAcl($folder);
	}

	private function touchAcl(Folder $folder) {
		$acl = $this->acl;
		if(!empty($acl)) {
			//quick hack so that shares folder is rebuilt in SHaredRootFolder::rebuildCache()
			$acl->mtime = time();
			$acl->save();
		}
	}

	/**
	 * A file has just been trashed. Create as fs_trash record if it does not exist
	 *
	 * @param \GO\Files\Model\File $file
	 * @param \GO\Files\Model\Folder $folder The folder it was in before it was moved to trash
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 * @throws \go\core\db\DbException
	 */

	public function saveForFile(File $file, Folder $folder): void
	{
		$entityType = EntityType::findByName('File');

		$t = self::findSingleByAttributes(['entityTypeId' => $entityType->getId(), 'entityId' => $file->id]);
		if ($t) {
			return;
		}
		$t = new self;
		$t->aclId = $folder->findAclId();
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
		$this->checkMayRemoveFromTrash();

		// The user must be allowed to write in the folder it's restored to
		$parentFolder = Folder::model()->findOrCreateByPathWithPermission($this->fullPath, \GO\Base\Model\Acl::WRITE_PERMISSION);

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

			$this->touchAcl($f);
			// a system folder may be put back in its original place
			$f->systemSave = true;
		}
		if (!$f->move($parentFolder, true, true)) {
			throw new Exception(500, \GO::t("Could not restore the item", "files"));
		}
		$this->delete(true);
	}

	/**
	 * Delete the file or folder permanently
	 *
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 */
	public function deletePermanently(): void
	{
		$this->checkMayRemoveFromTrash();

		$entityType = EntityType::findById($this->entityTypeId);
		if ($entityType->getName() == 'File') {
			$f = \GO\Files\Model\File::model()->findByPk($this->entityId, false, true, true);
		} else {
			$f = \GO\Files\Model\Folder::model()->findByPk($this->entityId, false, true, true);
		}
		if (!empty($f)) {
			$f->delete(true);
		}
		$this->delete(true);
	}

	/**
	 * Restoring and deleting a trashed item requires the same permission as deleting it in its original place
	 *
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 */
	private function checkMayRemoveFromTrash(): void
	{
		$folder = $this->getPermissionFolder();
		// when the original folder is gone only an administrator may do this
		$allowed = $folder ? $folder->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION) : (GO::$ignoreAclPermissions || GO::user()?->isAdmin());
		if (!$allowed) {
			throw new GO\Base\Exception\AccessDenied();
		}
	}

	/**
	 * The folder that had the permissions of this item before it was moved to trash.
	 *
	 * A folder with its own ACL kept it in the trash. Other items had the permissions of their original parent folder.
	 */
	private function getPermissionFolder(): ?Folder
	{
		$entityType = EntityType::findById($this->entityTypeId);
		if ($entityType->getName() == 'Folder') {
			$folder = Folder::model()->findByPk($this->entityId, false, true);
			if ($folder && $folder->acl_id > 0) {
				return $folder;
			}
		}

		return Folder::model()->findByPk($this->parentId, false, true) ?: null;
	}

}