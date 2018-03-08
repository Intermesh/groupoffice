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

use fkooman\OAuth\Client\Exception\TokenException;
use fkooman\OAuth\Common\Scope;

class Token
{
    /** @var string */
    private $clientConfigId;

    /** @var string */
    private $userId;

    /** @var Scope | null */
    private $scope;

    /** @var int */
    private $issueTime;

    public function __construct(array $data)
    {
        foreach (array('client_config_id', 'user_id', 'scope', 'issue_time') as $key) {
            if (!array_key_exists($key, $data)) {
                throw new TokenException(sprintf("missing field '%s'", $key));
            }
        }
        $this->setClientConfigId($data['client_config_id']);
        $this->setUserId($data['user_id']);
        $this->setScope($data['scope']);
        $this->setIssueTime($data['issue_time']);
    }

    public function setClientConfigId($clientConfigId)
    {
        if (!is_string($clientConfigId) || 0 >= strlen($clientConfigId)) {
            throw new TokenException("client_config_id needs to be a non-empty string");
        }
        $this->clientConfigId = $clientConfigId;
    }

    public function getClientConfigId()
    {
        return $this->clientConfigId;
    }

    public function setUserId($userId)
    {
        if (!is_string($userId) || 0 >= strlen($userId)) {
            throw new TokenException("client_config_id needs to be a non-empty string");
        }
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setScope(Scope $scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setIssueTime($issueTime)
    {
        if (!is_numeric($issueTime) || 0 >= $issueTime) {
            throw new TokenException("issue_time should be positive integer");
        }
        $this->issueTime = (int) $issueTime;

    }

    public function getIssueTime()
    {
        return $this->issueTime;
    }
}
