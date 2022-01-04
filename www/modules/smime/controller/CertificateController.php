<?php


namespace GO\Smime\Controller;


use GO\Base\Fs\File;
use GO\Base\Util\HttpClient;
use GO\Smime\Model\Smime;
use GO\Smime\Model\Certificate;
use http\Client;

class CertificateController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Smime\Model\Certificate';

	protected function getStoreParams($params)
	{
		$fp = \GO\Base\Db\FindParams::newInstance()->order(['valid_until','id'], ['DESC','DESC']);
		$fp->getCriteria()->addCondition('account_id', $params['account_id']);
		return $fp;
	}

	public function actionDownload($params) {



		$cert = Certificate::model()->findByPk($params['id']);
		if (!$cert)
			throw new \GO\Base\Exception\NotFound();

		//fetch account for permission check.
		$account = \GO\Email\Model\Account::model()->findByPk($cert->account_id);

		$filename = str_replace(array('@', '.'), '-', $account->getDefaultAlias()->email) . '.p12';

		$file = new \GO\Base\Fs\File($filename);
		\GO\Base\Util\Http::outputDownloadHeaders($file);

		echo $cert->cert;
	}

	public function actionUpload($params) {

		if (isset($_FILES['cert']['tmp_name'][0]) && is_uploaded_file($_FILES['cert']['tmp_name'][0])) {
			//check Group-Office password
			if (!\GO::user()->checkPassword($params['go_password']))
				throw new \Exception(\GO::t("The Group-Office password was incorrect.", "smime"));

			$certData = file_get_contents($_FILES['cert']['tmp_name'][0]);
			//Smime::import($certData, $params['smime_password']);

			//smime password may not match the Group-Office password
			if($params['go_password'] === $params['smime_password'])
				throw new \Exception(\GO::t("Your SMIME key password matches your Group-Office password. This is prohibited for security reasons!", "smime"));

			//password may not be empty.
			if (empty($params['smime_password']))
				throw new \Exception(\GO::t("Your SMIME key has no password. This is prohibited for security reasons!", "smime"));
		}

		//$cert = Model\Certificate::model()->findByPk($params['account_id']);
		$success = false;
		if (isset($certData)){
			$cert = new Certificate();
			$cert->cert = $certData;
			$cert->account_id = $params['account_id'];
			openssl_pkcs12_read($certData, $certs, $params['smime_password']);
			if(!$certs) {
				throw new \Exception(\GO::t("The SMIME password was incorrect.", "smime"));
			}
			$data = openssl_x509_parse($certs['cert']);
			if($data) {
				$cert->serial = $data['serialNumber'];
				$cert->valid_since = date('Y-m-d H:i:s',$data['validFrom_time_t']);
				$cert->valid_until = date('Y-m-d H:i:s',$data['validTo_time_t']);
				$cert->provided_by = $data['issuer']['CN'];
				$success = $cert->save();
			}
		}

//		if (isset($certData))
//			$cert->cert = $certData;
//		if (!empty($params['delete_cert']) || empty($cert->cert)) {
//			//$cert->cert = null;
//			$cert->delete();
//		} else {
//			$cert->always_sign = !empty($params['always_sign']);
//			$cert->save();
//		}

		return ['success' => $success];
	}

	public function actionDelete($params)
	{
		$cert = Certificate::model()->findByPk($params['id']);
		if (!$cert)
			throw new \GO\Base\Exception\NotFound();

		//fetch account for permission check.
		\GO\Email\Model\Account::model()->findByPk($cert->account_id);

		return ['success' => $cert->delete()];
	}

	/**
	 * Check password for the private key and store in session when correct.
	 * @param $params ['account_id', 'password'] account to fetch latest certificate from
	 * @return array
	 */
	public function actionCheckPassword($params) {

		$smime = new Smime($params['account_id']);

		return ['success' => true, 'passwordCorrect' => $smime->latestCert()->checkPass($params['password'])];
	}
}
