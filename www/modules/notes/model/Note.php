<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Notes\Model;

use GO;
use GO\Base\Db\ActiveRecord;
use GO\Base\Fs\Base;
use GO\Base\Util\Crypt;
use GO\Notes\NotesModule;

/**
 * The Note model
 *
 * @property int $id
 * @property int $category_id
 * @property int $files_folder_id
 * @property StringHelper $content
 * @property StringHelper $name
 * @property int $mtime
 * @property int $muser_id
 * @property int $ctime
 * @property int $user_id
 *
 * @property boolean $encrypted
 * @property StringHelper $password
 *
 * @method Note model Returns a static model of itself
 */
class Note extends ActiveRecord {

	/**
	 * For setting a new password
	 * 
	 * @var string 
	 */
	public $userInputPassword1;

	/**
	 * For setting a new password
	 * 
	 * @var string 
	 */
	public $userInputPassword2;
	private $_decrypted = false;

	protected function init() {

		$this->columns['name']['required'] = true;
		$this->columns['category_id']['required'] = true;

		return parent::init();
	}

	public function getLocalizedName() {
		return GO::t('note', 'notes');
	}

	public function aclField() {
		return 'category.acl_id';
	}

	public function tableName() {
		return 'no_notes';
	}

	public function hasFiles() {
		return true;
	}

	public function hasLinks() {
		return true;
	}

	public function customfieldsModel() {
		return "GO\Notes\Customfields\Model\Note";
	}

	public function relations() {
		return array(
			 'category' => array(
				  'type' => self::BELONGS_TO,
				  'model' => 'GO\Notes\Model\Category',
				  'field' => 'category_id',
				  'labelAttribute' => function($model) {
					  return $model->category->name;
				  }
			 ),
		);
	}

	protected function getCacheAttributes() {
		return array(
			 'name' => $this->name,
			 'description' => ''
		);
	}

	/**
	 * The files module will use this function.
	 */
	public function buildFilesPath() {

		return 'notes/' . Base::stripInvalidChars($this->category->name) . '/' . date('Y', $this->ctime) . '/' . Base::stripInvalidChars($this->name) . ' (' . $this->id . ')';
	}

	public function defaultAttributes() {
		$attr = parent::defaultAttributes();

		$category = NotesModule::getDefaultNoteCategory(GO::user()->id);
		$attr['category_id'] = $category->id;

		return $attr;
	}

	public function validate() {

		if (!empty($this->userInputPassword1) || !empty($this->userInputPassword2)) {
			if ($this->userInputPassword1 != $this->userInputPassword2) {
				$this->setValidationError('userInputPassword1', GO::t('passwordMatchError'));
			}
		}

		return parent::validate();
	}

	protected function beforeSave() {

		if (!empty($this->userInputPassword1)) {
			$this->password = \GO\Base\Util\Crypt::encryptPassword($this->userInputPassword1);

			$encrypted = Crypt::encrypt($this->content, $this->userInputPassword1);

			if ($encrypted === false) {
				throw new \Exception("Could not encrypt note!");
			}


			$this->content = $encrypted;
		} elseif ($this->getIsNew()) {
			$this->password = "";
		}

		return parent::beforeSave();
	}

	protected function getEncrypted() {
		return !$this->_decrypted && !empty($this->password);
	}

	public function getAttributes($outputType = null) {
		$attr = parent::getAttributes($outputType);

		$attr['encrypted'] = $this->getEncrypted();

		if ($attr['encrypted']) {
			$attr['content'] = '';
		}

		$attr['decrypted'] = $this->_decrypted;
		$attr['password'] = "";

		return $attr;
	}

	public function getExcerpt($maxLength = 100) {
		return $this->getEncrypted() ? GO::t('encryptedContent', 'notes') : GO\Base\Util\StringHelper::cut_string($this->content, $maxLength);
	}

	public function decrypt($password) {

		if ($this->password != crypt($password, $this->password)) {
			return false;
		} else {
			$this->_decrypted = true;
			$this->content = Crypt::decrypt($this->content, $password);
			return true;
		}
	}

}
