<?php
namespace GO\Dav\Locks;
use Sabre\DAV\Server;

class LocksBackend extends \Sabre\DAV\Locks\Backend\PDO {
    public $tableName = 'dav_locks';

    private Server $server;
    public function __construct(Server $server)
    {
        $this->server = $server;

        parent::__construct(go()->getDbConnection()->getPDO());
    }

    public function lock($uri, \Sabre\DAV\Locks\LockInfo $lockInfo)
    {

        $file = $this->server->tree->getNodeForPath($uri);

        $file->lock();


        return parent::lock($uri, $lockInfo);
    }

    public function unlock($uri, \Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        $file = $this->server->tree->getNodeForPath($uri);

        $file->unlock();
        return parent::unlock($uri, $lockInfo);
    }
}