<?php


namespace GO\Smime\Controller;


use GO\Base\Fs\File;
use GO\Base\Util\HttpClient;
use go\core\http\Exception;
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

		if (!isset($_FILES['cert']['tmp_name'][0]) || !is_uploaded_file($_FILES['cert']['tmp_name'][0])) {
			throw new \Exception("No file was received");
		}

		$certData = file_get_contents($_FILES['cert']['tmp_name'][0]);
		if(!$certData) {
			throw new Exception("No certificate data was found");
		}

		//password may not be empty.
		if (empty($params['smime_password']))
			throw new \Exception(\GO::t("Your SMIME key has no password. This is prohibited for security reasons!", "smime"));

		$success = false;
		$cert = new Certificate();
		$cert->cert = $certData;
		$cert->account_id = $params['account_id'];
		openssl_pkcs12_read($certData, $certs, $params['smime_password']);
		if(!$certs) {
			$error =  openssl_error_string();
			if(strpos($error, "11800071") !== false) {
				throw new \Exception(\GO::t("The SMIME password was incorrect.", "smime"));
			} else {
				throw new \Exception(\GO::t("Could not read p12 file:", "smime").' ' .$error);
			}
		}
		$data = openssl_x509_parse($certs['cert']);
		if($data) {
			$cert->serial = $data['serialNumber'];
			$cert->valid_since = date('Y-m-d H:i:s',$data['validFrom_time_t']);
			$cert->valid_until = date('Y-m-d H:i:s',$data['validTo_time_t']);
			$cert->provided_by = $data['issuer']['CN'];
			$success = $cert->save();

			if(!$success) {
				go()->error($cert->getValidationErrors());
			}
		}

		return ['success' => $success, 'feedback' => $success ? "" : go()->t("This certificate already exists","legacy","smime") ];
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
