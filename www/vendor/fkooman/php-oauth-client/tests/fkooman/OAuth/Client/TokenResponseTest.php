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

class TokenResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testSimple()
    {
        $t = new TokenResponse(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "expires_in" => 5,
                "scope" => "foo",
                "refresh_token" => "bar",
                "unsupported_key" => "foo",
            )
        );
        $this->assertEquals("foo", $t->getAccessToken());
        $this->assertEquals("Bearer", $t->getTokenType());
        $this->assertEquals(5, $t->getExpiresIn());
        $this->assertEquals("bar", $t->getRefreshToken());
        $this->assertEquals("foo", $t->getScope()->toString());
    }

    public function testScope()
    {
        $t = new TokenResponse(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "scope" => "foo bar baz baz",
            )
        );
        // scope will be sorted de-duplicated string space separated
        $this->assertEquals("bar baz foo", $t->getScope()->toString());
    }

    /**
     * @expectedException fkooman\OAuth\Client\Exception\TokenResponseException
     * @expectedExceptionMessage scope must be non empty
     */
    public function testNullScope()
    {
        $t = new TokenResponse(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "scope" => null,
            )
        );
    }

    /**
     * @expectedException fkooman\OAuth\Client\Exception\TokenResponseException
     * @expectedExceptionMessage scope must be non empty
     */
    public function testEmptyScope()
    {
        $t = new TokenResponse(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "scope" => "",
            )
        );
    }

    /**
     * @expectedException fkooman\OAuth\Client\Exception\TokenResponseException
     * @expectedExceptionMessage expires_in needs to be a positive integer
     */
    public function testNegativeExpiresIn()
    {
        $t = new TokenResponse(
            array(
                "access_token" => "foo",
                "token_type" => "Bearer",
                "expires_in" => -5,
            )
        );

    }
}
