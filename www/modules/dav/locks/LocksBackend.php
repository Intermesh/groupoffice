<?php
namespace GO\Dav\Locks;
use GO\Dav\Fs\File;
use Sabre\DAV\Locks\LockInfo;
use Sabre\DAV\Server;

class LocksBackend extends \Sabre\DAV\Locks\Backend\PDO {
    public $tableName = 'dav_locks';

    private Server $server;
	private bool $isLocking = false;

	public function __construct(Server $server)
    {
        $this->server = $server;

        parent::__construct(go()->getDbConnection()->getPDO());
    }

		public function getLocks($uri, $returnChildLocks)
		{
			$locks = parent::getLocks($uri, $returnChildLocks);
			$file = $this->server->tree->getNodeForPath($uri);
			if(!$this->isLocking && $file instanceof File) {

				$fileModel = $file->getFile();
				$exists = false;
				if ($fileModel->lock_id) {
					foreach ($locks as $lock) {
						if ($lock->token == $fileModel->lock_id) {
							$exists = true;
							break;
						}
					}

					if (!$exists) {
						go()->debug("Adding lock from GO ". $fileModel->lock_id);
						$lockInfo = new LockInfo();
						$lockInfo->token = $fileModel->lock_id;
						$lockInfo->owner = "go-" . $fileModel->locked_user_id;
						$lockInfo->timeout = 30 * 60;
						$lockInfo->created = time();
						$lockInfo->uri = $uri;

						$locks[] = $lockInfo;
					}
				}
			}

			return $locks;

		}

		public function lock($uri, \Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        $file = $this->server->tree->getNodeForPath($uri);

        $file->lock($lockInfo->token);

				$this->isLocking = true;
        $success = parent::lock($uri, $lockInfo);
				$this->isLocking = false;

				return $success;
    }

    public function unlock($uri, \Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        $file = $this->server->tree->getNodeForPath($uri);
				$fileModel = $file->getFile();


				if($fileModel->lock_id == $lockInfo->token) {
					$file->unlock();
				}
        return parent::unlock($uri, $lockInfo);
    }
}