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

namespace GO\Smime\Controller;


use GO\Smime\Model\PublicCertificate;

class PublicCertificateController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Smime\Model\PublicCertificate';
	
	protected function getStoreParams($params)
	{
		$fp = \GO\Base\Db\FindParams::newInstance();
		$fp->getCriteria()->addCondition('user_id', \GO::user()->id);
		return $fp;
	}

	public function actionImport($params) {
		if(empty($params['blobId']) || empty($params['email'])) {
			throw new \InvalidArgumentException('Invalid parameter posted');
		}

		$blob = \go\core\fs\Blob::findById($params['blobId']);
		$content = file_get_contents($blob->path());
		$success = PublicCertificate::import($content, [$params['email']]);
		return ['success' => $success];
	}

	public function actionImportAttachment($params) {
		// account_id mailbox uid number encoding sender
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
		$imap = $account->openImapConnection($params['mailbox']);
		$certData = $imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding']);
		$success = PublicCertificate::import($certData, [$params['sender']]);

		return ['success' => $success];
	}

	public function actionVerify($params) {

		$response['success'] = true;

		$params['email']= strtolower($params['email']);

		$oscpMsg = "Not checked";

		//if file was already stored somewhere after decryption
		if(!empty($params['cert_id'])){
			$cert = PublicCertificate::model()->findByPk($params['cert_id']);
		} else {
			$cert = PublicCertificate::fromEmail($params['account_id'], $params['mailbox'], $params['uid']);
		}

		return ['success'=> true, 'data' => $cert->parse()] ;
	}

}
