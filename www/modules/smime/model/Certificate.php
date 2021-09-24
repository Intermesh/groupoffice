<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.smime.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 



namespace GO\Smime\Model;

use go\core\util\DateTime;

/**
 * The Certificate model
 *
 * @package GO.modules.smime.model
 * @property int $id
 * @property int $account_id
 * @property string $cert
 * @property DateTime $valid_until
 * @property DateTime $valid_since
 * @property string $serial Serial number extracted from cert file
 * @property string $provided_by
 */
class Certificate extends \GO\Base\Db\ActiveRecord {

	public static $trimOnSave = false;

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'smi_pkcs12';
	}

	public function read($passphrase = null) {
		if($passphrase === null) {
			$passphrase = \GO::session()->values['smime']['passwords'][$this->account_id];
		}
		openssl_pkcs12_read($this->cert, $certs, $passphrase);
		return $certs;
	}

	public function isValid() {
		$now = new DateTime();
		return new DateTime($this->valid_since) < $now && new DateTime($this->valid_until) > $now;
	}

	private $password = '';
	public function setPassword($pass) {
		$this->password = $pass;
		return $this;
	}

	public function decryptFile($file) {
		\GO::debug('decrypting message');

		openssl_pkcs12_read($this->cert, $certs, $this->password);

		if (empty($certs)) {
			//password invalid
			$response['askPassword'] = true;
			GO::debug("Invalid password");
			return false;
		}

		$outfile = \GO\Base\Fs\File::tempFile();
		$return = openssl_pkcs7_decrypt($file->path(), $outfile->path(), $certs['cert'], array($certs['pkey'], $this->password));
		$file->delete();

		if (!$return || !$outfile->exists() || !$outfile->size()) {
			$result = GO::t("SMIME Decryption of this message failed.", "smime") . '<br />';
			while ($str = openssl_error_string()) {
				$result.='<br />' . $str;
			}
			GO::debug("Decryption failed");
			return $result;
		}


		return $outfile;
	}

	public function checkPass($phrase) {

		$check = $this->read($phrase);

		if (!empty($check)) {
			//store in session for later usage
			\GO::session()->values['smime']['passwords'][$this->account_id] = $phrase;
			return true;
		}
		return false;
	}
}
