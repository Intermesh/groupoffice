<?php

namespace go\modules\community\maildomains\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\validate\ErrorCode;

final class DkimKey extends Property
{
	public string $domainId;

	public string $selector;

	protected ?string $publicKey;

	public bool $status;

	protected string $privateKey;

	public bool $enabled = false;

	private bool $isImported = false;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_dkim_key");
	}

	protected function init()
	{
		parent::init();

		if(!isset($this->privateKey)) {
			$this->generate();
		}
	}

	private function generate() : void {

		// see https://the-art-of-web.com/php/dkim-key-pair-generation/

		$pkey = openssl_pkey_new([
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA
		]);
		$this->privateKey = "";
		openssl_pkey_export($pkey, $this->privateKey);

		$details = openssl_pkey_get_details($pkey);
		$this->publicKey = $details['key'];
	}

	public function getDNS() : string {
		return "v=DKIM1; k=rsa; p=" . $this->parsePublicKey();
	}

	public function parsePublicKey() : string {
		$key = explode(PHP_EOL, trim($this->publicKey));
		return implode("", array_filter($key, fn($val) => !preg_match("/^-+/", $val)));
	}

	public function setPrivateKey(string $key): void
	{
		$this->privateKey = $key;
		$this->publicKey = null;
		$this->isImported = true;
	}

	protected function internalValidate()
	{
		parent::internalValidate();

		if($this->isImported) {
			$this->validateKeys();
		}
	}

	private function validateKeys(): void
	{
		try {
			$pkey = openssl_pkey_get_private($this->privateKey);
		}catch(\Exception $e) {
			$this->setValidationError("privateKey", ErrorCode::INVALID_INPUT, "Could not read private key: ". $e->getMessage());
			return;
		}


		if(!$pkey) {
			$this->setValidationError("privateKey", ErrorCode::INVALID_INPUT, "Could not read private key");
			return;
		}


		try {
			$details = openssl_pkey_get_details($pkey);
			$this->publicKey = $details['key'];
		}catch(\Exception $e) {
			$this->setValidationError("publicKey", ErrorCode::INVALID_INPUT, "Could not read public key: ". $e->getMessage());
			return;
		}


	}


}