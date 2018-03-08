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

namespace fkooman\OAuth\Common;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testScope()
    {
        $s = new Scope(array("read", "write", "foo"));
        $this->assertFalse($s->isEmpty());
        $this->assertTrue($s->hasScope(new Scope(array("read"))));
        $this->assertTrue($s->hasScope(new Scope(array("write"))));
        $this->assertTrue($s->hasScope(new Scope(array("foo"))));
        $this->assertTrue($s->hasAnyScope(new Scope(array("foo", "bar"))));
        $this->assertTrue($s->equals(new Scope(array("foo", "write", "read"))));
        $this->assertFalse($s->equals(new Scope(array("read", "write"))));
        $this->assertFalse($s->equals(new Scope(array("bar", "foo", "read", "write"))));
        $this->assertFalse($s->hasAnyScope(new Scope(array("bar", "baz"))));
        $this->assertEquals("foo read write", $s->toString());
        $this->assertEquals("foo read write", $s->__toString());
        $this->assertEquals("foo,read,write", $s->toString(","));
    }

    public function testEmptyScope()
    {
        $s = new Scope();
        $this->assertTrue($s->isEmpty());
        $this->assertTrue($s->equals(new Scope()));
        $this->assertFalse($s->hasScope(new Scope(array("foo"))));
        $this->assertTrue($s->hasScope(new Scope()));
        $this->assertTrue($s->hasAnyScope(new Scope()));
    }

    public function testScopeFromString()
    {
        $s = Scope::fromString("foo bar");
        $this->assertEquals(array("bar", "foo"), $s->toArray());
    }

    public function testScopeFromStringCommaSeparated()
    {
        $s = Scope::fromString("foo,bar", ",");
        $this->assertEquals(array("bar", "foo"), $s->toArray());
    }

    public function testHasOnlyScope()
    {
        $scope = new Scope(array("foo", "bar"));
        $this->assertTrue($scope->hasOnlyScope(new Scope(array("foo", "bar", "baz"))));
        $this->assertFalse($scope->hasOnlyScope(new Scope(array("foo"))));
        $this->assertFalse($scope->hasOnlyScope(new Scope()));
        $scopeTwo = new Scope();
        $this->assertTrue($scopeTwo->hasOnlyScope(new Scope(array("foo"))));
    }

    /**
     * @expectedException fkooman\OAuth\Common\Exception\ScopeException
     * @expectedExceptionMessage invalid scope token
     */
    public function testInvalidScopeToken()
    {
        $s = new Scope(array("François"));
    }

    /**
     * @expectedException fkooman\OAuth\Common\Exception\ScopeException
     * @expectedExceptionMessage scope token must be a non-empty string
     */
    public function testEmptyArrayScope()
    {
        $s = new Scope(array("foo", "", "bar"));
    }

    /**
     * @expectedException fkooman\OAuth\Common\Exception\ScopeException
     * @expectedExceptionMessage scope must be string
     */
    public function testNonStringFromString()
    {
        $s = Scope::fromString(5);
    }

    public function testNullFromString()
    {
        $s = Scope::fromString(null);
        $this->assertTrue($s->isEmpty());
    }

    public function testEmptyStringFromString()
    {
        $s = Scope::fromString("");
        $this->assertTrue($s->isEmpty());
    }

    /**
     * @expectedException fkooman\OAuth\Common\Exception\ScopeException
     * @expectedExceptionMessage invalid scope token
     */
    public function testEmptyStringScope()
    {
        $s = new Scope(array("foo ", "bar"));
    }

    public function testSerialize()
    {
        $s = new Scope(array("foo", "bar", "baz"));
        $t = new Scope($s->toArray());
        $this->assertTrue($t->equals($s));
    }
}
