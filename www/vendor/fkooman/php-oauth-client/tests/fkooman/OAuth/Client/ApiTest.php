<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace fkooman\OAuth\Client;

use PDO;

use fkooman\OAuth\Common\Scope;

use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $clientConfig;

    /** @var fkooman\OAuth\Client\PdoStorage */
    private $storage;

    public function setUp()
    {
        $this->clientConfig = array();

        $this->clientConfig[] = new ClientConfig(
            array(
                "client_id" => "foo",
                "client_secret" => "bar",
                "authorize_endpoint" => "http://www.example.org/authorize",
                "token_endpoint" => "http://www.example.org/token"
            )
        );

        $this->storage = new PdoStorage(
            new PDO(
                $GLOBALS['DB_DSN'],
                $GLOBALS['DB_USER'],
                $GLOBALS['DB_PASSWD']
            )
        );
        $this->storage->initDatabase();
    }

    public function testGetAccessTokenWithoutToken()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200));
        $client->addSubscriber($mock);

        $api = new Api("foo", $this->clientConfig[0], $this->storage, $client);
        $context = new Context("a_user", array("foo", "bar"));

        $this->assertFalse($api->getAccessToken($context));
        $this->assertEquals("http://www.example.org/authorize?client_id=foo&response_type=code&state=my_custom_state&scope=bar+foo", $api->getAuthorizeUri($context, "my_custom_state"));
    }

    public function testGetAccessTokenWithToken()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200));
        $client->addSubscriber($mock);

        $api = new Api("foo", $this->clientConfig[0], $this->storage, $client);
        $context = new Context("a_user", array("foo", "bar"));

        $accessToken = new AccessToken(
            array(
                "client_config_id" => "foo",
                "user_id" => "a_user",
                "token_type" => "bearer",
                "access_token" => "my_token_value",
                "scope" => Scope::fromString("foo bar"),
                "issue_time" => time() - 100,
                "expires_in" => 3600
            )
        );
        $this->storage->storeAccessToken($accessToken);

        $accessToken = $api->getAccessToken($context);
        $this->assertEquals("my_token_value", $accessToken->getAccessToken());
    }

    public function testGetAccessTokenWithExpiredAccessTokenAndRefreshToken()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(
            new Response(
                200,
                null,
                json_encode(
                    array(
                        "access_token" => "my_new_access_token_value",
                        "token_type" => "Bearer"
                    )
                )
            )
        );
        $client->addSubscriber($mock);

        $api = new Api("foo", $this->clientConfig[0], $this->storage, $client);
        $context = new Context("a_user", array("foo", "bar"));

        $accessToken = new AccessToken(
            array(
                "client_config_id" => "foo",
                "user_id" => "a_user",
                "token_type" => "bearer",
                "access_token" => "my_token_value",
                "scope" => Scope::fromString("foo bar"),
                "issue_time" => time() - 4000,
                "expires_in" => 3600
            )
        );
        $this->storage->storeAccessToken($accessToken);

        $refreshToken = new RefreshToken(
            array(
                "client_config_id" => "foo",
                "user_id" => "a_user",
                "refresh_token" => "my_refresh_token_value",
                "scope" => Scope::fromString("foo bar"),
                "issue_time" => time() - 10000,
            )
        );
        $this->storage->storeRefreshToken($refreshToken);

        $accessToken = $api->getAccessToken($context);
        $this->assertEquals("my_new_access_token_value", $accessToken->getAccessToken());
        //$this->assertFalse($accessToken);
    }
}
