<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id$
 * @copyright Copyright Intermesh
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Notes\Controller;

use Exception;
use GO;
use GO\Base\Controller\AbstractController;
use GO\Notes\Model\Note;
use GO\Notes\Model\Category;
use GO\Base\Db\FindParams;

/**
 * The note controller provides action for basic crud functionality for the note model
 */
class NoteController extends AbstractController {

	protected $view = 'json';

	/**
	 * Updates a note POST for save and GET for retrieve
	 *
	 * @param $id Note ID
	 */
	protected function actionUpdate($id, $password = null) {

		$model = Note::model()->findByPk($id);

		if (!$model)
			throw new \GO\Base\Exception\NotFound();

		if (GO::request()->isPost()) {
			$note = GO::request()->post['note'];

			if (!empty($note['encrypted']) && $note['encrypted'] == true) {
				//if the note was encrypted and no new password was supplied the current
				//pasword is sent.
				if (isset($note['currentPassword']) && empty($note['userInputPassword1'])) {
					$note['userInputPassword1'] = $note['userInputPassword2'] = $note['currentPassword'];
				} else if (empty($note['userInputPassword1']) || empty($note['userInputPassword2'])) {
					throw new \Exception('Missing input password.');
				}
			}

			$model->setAttributes($note);
			if (isset($note['encrypted']) && $note['encrypted'] == false) {
				$model->password = "";
			}

			$model->save();

			echo $this->render('submit', array('note' => $model));
		} else {
			//a password is entered to decrypt the content
			if (isset($password)) {
				if (!$model->decrypt($password))
					throw new Exception(GO::t('badPassword'));
			}

			echo $this->render(
					  'form', array(
				 'note' => $model
					  )
			);
		}
	}

	/**
	 * Creates a note
	 */
	protected function actionCreate($category_id = 0) {

		$model = new Note();

		if (GO::request()->isPost()) {
			$note = GO::request()->post['note'];

			if (isset($note['currentPassword'])) {
				//if the note was encrypted and no new password was supplied the current
				//pasword is sent.
				$note['userInputPassword1'] = $note['userInputPassword2'] = $note['currentPassword'];
			}

			$model->setAttributes($note);

			if ($model->save()) {
				if (GO::modules()->files) {
					$f = new \GO\Files\Controller\FolderController();
					$response = array(); //never used in processAttachements?
					$f->processAttachments($response, $model, $note);
				}
			}

			echo $this->render('submit', array('note' => $model));
		} else {

			if (empty($category_id)) {
				$defaultCategory = GO\Notes\NotesModule::getDefaultNoteCategory(GO::user()->id);
				$model->category_id = $defaultCategory->id;
			} else {
				$model->category_id = $category_id;
			}

			echo $this->render(
					  'form', array(
				 'note' => $model
					  )
			);
		}
	}

	protected function actionGet($id, $userInputPassword = null) {
		$model = Note::model()->findByPk($id);
		if (!$model)
			throw new \GO\Base\Exception\NotFound();


		// decrypt model if password provided
		if (isset($userInputPassword)) {
			if (!$model->decrypt($userInputPassword))
				throw new Exception(GO::t('badPassword'));
		}

		echo $this->render('get', array('note' => $model));
	}

	/**
	 * Load a note model from the database and call the renderDisplay function to render the JSON
	 * output for a ExtJS Display Panel
	 * @param array $params the $_REQUEST object
	 * @throws \GO\Base\Exception\NotFound when the note model cant be found in database
	 * @throws Exception When the encryption password provided is incorrect
	 */
	protected function actionDisplay($id, $userInputPassword = null) {

		$model = Note::model()->findByPk($id);
		if (!$model)
			throw new \GO\Base\Exception\NotFound();


		// decrypt model if password provided
		if (isset($userInputPassword)) {
			if (!$model->decrypt($userInputPassword))
				throw new Exception(GO::t('badPassword'));
		}

		$response = $this->render('display', array('model' => $model));

		if ($model->encrypted)
			$response->data['data']['content'] = GO::t('clickHereToDecrypt');

		$response->data['data']['encrypted'] = $model->encrypted;

		echo $response;
	}

	/**
	 * Render JSON output that can be used by ExtJS GridPanel
	 * @param array $params the $_REQUEST params
	 */
	protected function actionStore($excerpt = false) {
		//Create ColumnModel from model
		$columnModel = new \GO\Base\Data\ColumnModel();
		$columnModel->formatColumn('user_name', '$model->user->name', array(), 'user_id');


		$findParams = \GO\Base\Db\FindParams::newInstance()->export('notes');

		if ($excerpt) {

			$columnModel->formatColumn('excerpt', '$model->excerpt');

			$findParams->select('t.*');
		}

		$nn = new Note();
		$store = new \GO\Base\Data\DbStore($nn->className(), $columnModel, null, $findParams);
		$store->multiSelect('no-multiselect', Category::className(), 'category_id');

		echo $this->render('store', array('store' => $store));
	}

	/**
	 * Delete a single not. Must be a POST request
	 *
	 * @param int $id
	 * @throws Exception
	 * @throws \GO\Base\Exception\NotFound
	 */
	protected function actionDelete($id) {

		if (!GO::request()->isPost()) {
			throw new Exception('Delete must be a POST request');
		}

		$model = Note::model()->findByPk($id);
		if (!$model)
			throw new \GO\Base\Exception\NotFound();

		$model->delete();

		echo $this->render('delete', array('model' => $model));
	}

}
