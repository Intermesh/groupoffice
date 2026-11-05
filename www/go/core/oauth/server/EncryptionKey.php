<?php

namespace go\core\oauth\server;

use Defuse\Crypto\Key;
use Exception;

/**
 * The key the OAuth2 / OpenID Connect server uses to encrypt authorization codes and refresh tokens.
 *
 * Everybody who knows this key can create authorization codes and refresh tokens that the server accepts, so every
 * installation has its own random key. It is stored with the other keys of the OAuth2 server in the data folder.
 *
 * Replacing or removing the file invalidates all authorization codes and refresh tokens that were issued.
 */
class EncryptionKey
{
	/**
	 * @throws Exception
	 */
	public static function get(): Key
	{
		$file = go()->getDataFolder()->getFile('oauth2/encryption.key');

		if (!$file->exists()) {
			self::create($file);
		}

		return Key::loadFromAsciiSafeString(trim($file->getContents()));
	}

	/**
	 * @throws Exception
	 */
	private static function create(\go\core\fs\File $file): void
	{
		$file->getFolder()->create();

		// Write a complete file with the right permissions first and then link it into place. Linking fails when the
		// file exists. So when two requests do this at the same time they end up using the same key.
		$tmp = $file->getFolder()->getFile(uniqid('encryption', true) . '.tmp');
		// restrict the permissions before the key is written to it
		$tmp->touch();
		$tmp->chmod(0600);
		if (!$tmp->putContents(Key::createNewRandomKey()->saveToAsciiSafeString())) {
			$tmp->delete();
			throw new Exception("Could not create the OAuth2 encryption key file");
		}

		try {
			@link($tmp->getPath(), $file->getPath());
		} finally {
			$tmp->delete();
		}

		if (!$file->exists()) {
			throw new Exception("Could not create the OAuth2 encryption key file");
		}
	}
}
