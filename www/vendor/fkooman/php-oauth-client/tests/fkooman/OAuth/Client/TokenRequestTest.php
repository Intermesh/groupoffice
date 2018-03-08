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

use Guzzle\Http\Client;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;
use fkooman\OAuth\Common\Scope;

class TokenRequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $clientConfig;

    /** @var array */
    private $tokenResponse;

    public function setUp()
    {
        $this->clientConfig = array();
        $this->tokenResponse = array();

        $this->clientConfig[] = new ClientConfig(
            array(
                "client_id" => "foo",
                "client_secret" => "bar",
                "authorize_endpoint" => "http://www.example.org/authorize",
                "token_endpoint" => "http://www.example.org/token"
            )
        );

        $this->clientConfig[] = new ClientConfig(
            array(
                "client_id" => "foo",
                "client_secret" => "bar",
                "authorize_endpoint" => "http://www.example.org/authorize",
                "token_endpoint" => "http://www.example.org/token",
                "redirect_uri" => "http://foo.example.org/callback",
                "credentials_in_request_body" => true
            )
        );

        $this->clientConfig[] = new ClientConfig(
            array(
                "client_id" => "foo",
                "client_secret" => "bar",
                "authorize_endpoint" => "http://www.example.org/authorize",
                "token_endpoint" => "http://www.example.org/token",
                "redirect_uri" => "http://foo.example.org/callback",
                "allow_string_expires_in" => true
            )
        );

        $this->clientConfig[] = new ClientConfig(
            array(
                "client_id" => "foo",
                "client_secret" => "bar",
                "authorize_endpoint" => "http://www.example.org/authorize",
                "token_endpoint" => "http://www.example.org/token",
                "redirect_uri" => "http://foo.example.org/callback",
                "use_array_scope" => true
            )
        );

        $this->clientConfig[] = new ClientConfig(
            array(
                "client_id" => "foo",
                "client_secret" => "bar",
                "authorize_endpoint" => "http://www.example.org/authorize",
                "token_endpoint" => "http://www.example.org/token",
                "redirect_uri" => "http://foo.example.org/callback",
                "use_comma_separated_scope" => true
            )
        );

        $this->tokenResponse[] = json_encode(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer"
            )
        );

        $this->tokenResponse[] = "{";

        $this->tokenResponse[] = json_encode(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "expires_in" => "1200"
            )
        );

        $this->tokenResponse[] = json_encode(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "scope" => array("foo", "bar")
            )
        );

        $this->tokenResponse[] = json_encode(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "scope" => "foo,bar"
            )
        );
    }

    public function testWithAuthorizationCode()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[0]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[0]);
        $tokenRequest->withAuthorizationCode("12345");
        $lastRequest = $history->getLastRequest();
        $this->assertEquals("POST", $lastRequest->getMethod());
        $this->assertEquals("code=12345&grant_type=authorization_code", $lastRequest->getPostFields()->__toString());
        $this->assertEquals("Basic Zm9vOmJhcg==", $lastRequest->getHeader("Authorization"));
        $this->assertEquals(
            "application/x-www-form-urlencoded; charset=utf-8",
            $lastRequest->getHeader("Content-Type")
        );
    }

    public function testWithAuthorizationCodeCredentialsInRequestBody()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[0]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[1]);
        $tokenRequest->withAuthorizationCode("12345");
        $lastRequest = $history->getLastRequest();
        $this->assertEquals("POST", $lastRequest->getMethod());
        $this->assertEquals(
            "code=12345&grant_type=authorization_code&redirect_uri=http%3A%2F%2Ffoo.example.org%2Fcallback&client_id=foo&client_secret=bar",
            $lastRequest->getPostFields()->__toString()
        );
        $this->assertEquals(
            "application/x-www-form-urlencoded; charset=utf-8",
            $lastRequest->getHeader("Content-Type")
        );
    }

    public function testAllowStringExpiresIn()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[2]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[2]);
        $tokenResponse = $tokenRequest->withAuthorizationCode("12345");
        $this->assertEquals(1200, $tokenResponse->getExpiresIn());
    }

    public function testAllowArrayScope()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[3]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[3]);
        $tokenResponse = $tokenRequest->withAuthorizationCode("12345");
        $this->assertTrue($tokenResponse->getScope()->equals(Scope::fromString("foo bar")));
    }

    public function testAllowCommaSeparatedScope()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[4]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[4]);
        $tokenResponse = $tokenRequest->withAuthorizationCode("12345");
        $this->assertTrue($tokenResponse->getScope()->equals(Scope::fromString("foo bar")));
    }

    public function testWithRefreshToken()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[0]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[0]);
        $tokenRequest->withRefreshToken("refresh_123_456");
        $lastRequest = $history->getLastRequest();
        $this->assertEquals("POST", $lastRequest->getMethod());
        $this->assertEquals("Basic Zm9vOmJhcg==", $lastRequest->getHeader("Authorization"));
        $this->assertEquals(
            "refresh_token=refresh_123_456&grant_type=refresh_token",
            $lastRequest->getPostFields()->__toString()
        );
        $this->assertEquals(
            "application/x-www-form-urlencoded; charset=utf-8",
            $lastRequest->getHeader("Content-Type")
        );
    }

    public function testBrokenJsonResponse()
    {
        $client = new Client();
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, $this->tokenResponse[1]));
        $client->addSubscriber($mock);
        $history = new HistoryPlugin();
        $history->setLimit(5);
        $client->addSubscriber($history);
        $tokenRequest = new TokenRequest($client, $this->clientConfig[0]);
        $this->assertFalse($tokenRequest->withRefreshToken("refresh_123_456"));
    }
}
