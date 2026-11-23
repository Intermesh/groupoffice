<?php
namespace go\core\cache;

/** Atomic shared counter storage; false means cooldown, errors must throw. */
interface AuthenticationCounterStore {
	public function updateAuthenticationCounter(string $key, string $operation, int $limit, int $window): bool;
}
