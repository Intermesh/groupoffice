<?php

namespace go\modules\community\pgpencryption\convert;

use Exception;
use go\core\data\convert\AbstractConverter;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\orm\Entity;
use go\core\util\DateTime;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\pgpencryption\model\PrivateKey;
use go\modules\community\pgpencryption\model\PublicKey;

class Key extends AbstractConverter
{
	private ?string $publicArmored = null;

	private ?string $privateArmored = null;

	private bool $recordServed = false;

	private string $exportBuffer = '';

	/**
	 * @inheritDoc
	 */
	public static function supportedExtensions(): array
	{
		return ['asc', 'key', 'gpg'];
	}

	/**
	 * @inheritDoc
	 */
	protected function initImport(File $file): void
	{
		$contents = $file->getContents();

		if ($contents === false || $contents === '') {
			throw new Exception("Could not read file: " . $file->getName());
		}

		if (preg_match(
			'/-----BEGIN PGP PUBLIC KEY BLOCK-----.*?-----END PGP PUBLIC KEY BLOCK-----/s',
			$contents,
			$m
		)) {
			$this->publicArmored = trim($m[0]);
		}

		if (preg_match(
			'/-----BEGIN PGP PRIVATE KEY BLOCK-----.*?-----END PGP PRIVATE KEY BLOCK-----/s',
			$contents,
			$m
		)) {
			$this->privateArmored = trim($m[0]);
		}

		if (!$this->publicArmored) {
			throw new Exception("A public key block is required in " . $file->getName());
		}

		if (empty($this->clientParams['values']['email'])) {
			throw new Exception("Missing 'email' value for imported key");
		}

		$this->total = 1;
	}

	/**
	 * @inheritDoc
	 */
	protected function nextImportRecord(): bool
	{
		if ($this->recordServed) {
			return false;
		}

		$this->recordServed = true;

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function importEntity()
	{
		$entity = new PublicKey();
		$entity->key = $this->publicArmored;
		$entity->email = $this->clientParams['values']['email'];

		if (!empty($this->clientParams['values']['expiresAt'])) {
			$entity->expiresAt = new DateTime($this->clientParams['values']['expiresAt']);
		}

		$contactId = Contact::findForUser(go()->getUserId())->id;

		if (isset($contactId)) {
			$entity->contactId = $contactId;
		}


		return $entity;
	}

	/**
	 * @inheritDoc
	 */
	protected function afterSave(Entity $entity): bool
	{
		if (!$this->privateArmored) {
			return true;
		}

		$privateKey = new PrivateKey();
		$privateKey->key = $this->privateArmored;
		$privateKey->email = $entity->email;
		$privateKey->publicKeyId = $entity->id;
		$privateKey->userId = go()->getUserId();

		if (!empty($entity->expiresAt)) {
			$privateKey->expiresAt = $entity->expiresAt;
		}

		if (!$privateKey->save()) {
			$this->notifyError(true, "Public key was imported, but the private key could not be stored.");
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function initExport(): void
	{
		$this->exportBuffer = '';
	}

	/**
	 * @inheritDoc
	 */
	protected function exportEntity(Entity $entity): void
	{
		$this->exportBuffer .= $entity->key . "\n\n";
	}

	/**
	 * @inheritDoc
	 */
	protected function finishExport(): Blob
	{
		$blob = Blob::fromString($this->exportBuffer);
		$blob->name = 'pgp-key.asc';
		$blob->type = 'application/pgp-keys';

		if (!$blob->save()) {
			throw new Exception("Could not save export blob");
		}

		return $blob;
	}
}