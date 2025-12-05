<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\repositories;;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use go\core\model\OauthScope;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier($identifier): ?\League\OAuth2\Server\Entities\ScopeEntityInterface
		{
        $scopes = [
            'basic' => [
                'description' => 'Basic details about you',
            ],
            'email' => [
                'description' => 'Your email address',
            ],
            'phone' => [
                'description' => 'Your phone number',
            ],
		        'openid' => [
			          'description' => 'OpenID Connect support',
		        ],
		        'profile' => [
			        'description' => 'Your full profile',
		        ]
        ];

        if (array_key_exists($identifier, $scopes) === false) {
            return null;
        }

        $scope = new OauthScope();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    /**
     * {@inheritdoc}
		 * @param array $scopes
		 * @param string $grantType
		 * @param ClientEntityInterface $clientEntity
		 * @param null $userIdentifier
		 * @param string|null $authCodeId
		 */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null, ?string $authCodeId = null): array
		{
        $scope = new OauthScope();
        $scope->setIdentifier('openid');
        $scopes[] = $scope;

        return $scopes;
    }
}
