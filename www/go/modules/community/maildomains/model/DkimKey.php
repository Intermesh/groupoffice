<?php

namespace go\modules\community\maildomains\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

final class DkimKey extends Property
{
	public int $domainId;

	public string $selector;

	protected string $publicKey;

	public bool $status;

	protected string $privateKey;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_dkim_key");
	}

	protected function init()
	{
		parent::init();

		if(!isset($this->key)) {
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

	public function getPublicKey() : string {

		$key = explode(PHP_EOL, trim($this->publicKey));
		return implode("", array_filter($key, fn($val) => !preg_match("/^-+/", $val)));
	}


}