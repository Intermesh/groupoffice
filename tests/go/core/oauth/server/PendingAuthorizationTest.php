<?php
namespace go\core\oauth\server;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\TestCase;

/**
 * An authorization request that waits in the session while the user logs in may only be resumed by the return from
 * the login page, not by a new authorization request.
 */
class PendingAuthorizationTest extends TestCase
{
	private array $session;

	protected function setUp(): void
	{
		$this->session = $_SESSION ?? [];
	}

	protected function tearDown(): void
	{
		$_SESSION = $this->session;
	}

	public function testReturnFromLoginResumesRequest(): void
	{
		$authRequest = new AuthorizationRequest();
		PendingAuthorization::save($authRequest);

		$this->assertSame($authRequest, PendingAuthorization::take([]));
		$this->assertNull(PendingAuthorization::take([]), 'it can only be resumed once');
	}

	public function testNewAuthorizationRequestDoesNotResumeStoredRequest(): void
	{
		PendingAuthorization::save(new AuthorizationRequest());

		$this->assertNull(PendingAuthorization::take(['client_id' => 'other', 'response_type' => 'code']));
		$this->assertArrayNotHasKey('authRequest', $_SESSION);
	}

	public function testOldRequestIsNotResumed(): void
	{
		PendingAuthorization::save(new AuthorizationRequest());
		$_SESSION['authRequestAt'] = time() - PendingAuthorization::LIFETIME - 1;

		$this->assertNull(PendingAuthorization::take([]));
	}

	public function testOtherSessionValueIsNotResumed(): void
	{
		$_SESSION['authRequest'] = 'not a request';
		$_SESSION['authRequestAt'] = time();

		$this->assertNull(PendingAuthorization::take([]));
	}
}
