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
use go\core\oauth\server\OAuthServer;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier(string $identifier): ?\League\OAuth2\Server\Entities\ScopeEntityInterface
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

        foreach (OAuthServer::moduleScopes() as $name => $scope) {
            $scopes[$name] = ['description' => $scope['description']];
        }

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
    public function finalizeScopes(array $scopes, string $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null, ?string $authCodeId = null): array
		{
        // A token for a module, like an MCP client, only gets what it asked for. It may not read the profile of the
        // user with /userinfo unless it asked for "openid".
        $moduleScopes = OAuthServer::moduleScopes();
        foreach ($scopes as $requested) {
            if (isset($moduleScopes[$requested->getIdentifier()])) {
                return $scopes;
            }
        }

        $scope = new OauthScope();
        $scope->setIdentifier('openid');
        $scopes[] = $scope;

        return $scopes;
    }
}
