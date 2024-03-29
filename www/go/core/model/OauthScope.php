<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\model;;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

class OauthScope implements ScopeEntityInterface
{
    use EntityTrait, ScopeTrait;

	/**
	 * Override made for php 8.1 compat
	 * @return mixed
	 */
		#[\ReturnTypeWillChange]
		public function jsonSerialize()
		{
			return $this->getIdentifier();
		}
}
