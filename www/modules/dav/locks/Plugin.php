<?php
namespace GO\Dav\Locks;

use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class Plugin extends \Sabre\DAV\Locks\Plugin {
	/**
	 * Override to handle case where file is not locked but client sends old lock token.
	 *
	 * PR for this is made: https://github.com/sabre-io/dav/pull/1672
	 */
	public function httpUnlock(RequestInterface $request, ResponseInterface $response)
	{
		$lockToken = $request->getHeader('Lock-Token');

		// If the locktoken header is not supplied, we need to throw a bad request exception
		if (!$lockToken) {
			throw new \Sabre\DAV\Exception\BadRequest('No lock token was supplied');
		}
		$path = $request->getPath();
		$locks = $this->getLocks($path);

		// No locks at all. Treat as OK
		if(!count($locks)) {
			$response->setHeader('Content-Length', '0');
			$response->setStatus(204);
			return false;
		}

		// Windows sometimes forgets to include < and > in the Lock-Token
		// header
		if ('<' !== $lockToken[0]) {
			$lockToken = '<'.$lockToken.'>';
		}

		foreach ($locks as $lock) {
			if ('<opaquelocktoken:'.$lock->token.'>' == $lockToken) {
				$this->unlockNode($path, $lock);
				$response->setHeader('Content-Length', '0');
				$response->setStatus(204);

				// Returning false will break the method chain, and mark the
				// method as 'handled'.
				return false;
			}
		}

		// If we got here, it means the locktoken was invalid
		throw new \Sabre\DAV\Exception\LockTokenMatchesRequestUri();
	}
}