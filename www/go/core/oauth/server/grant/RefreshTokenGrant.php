<?php

namespace go\core\oauth\server\grant;

use League\OAuth2\Server\Grant as OAuth2Grant;

class RefreshTokenGrant extends OAuth2Grant\RefreshTokenGrant
{
    use traits\GetClientTrait;
}