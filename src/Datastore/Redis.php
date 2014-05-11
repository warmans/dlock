<?php
namespace Dlock\Datastore;

/**
 * Store locks in Redis via PECL extension
 *
 * @author warmans
 */
class Redis implements DatastoreInterface
{
    private $conn;
    private $lockTtl;

    public function __construct(\Redis $conn, $lockTtl=3600)
    {
        $this->conn = $conn;
        $this->lockTtl = $lockTtl;
    }

    public function aquireLock($lockId)
    {
        if ($this->conn->setnx($lockId, 1)) {
            $this->conn->expire($lockId, $this->lockTtl);
            return true;
        }
        return false;
    }

    public function releaseLock($lockId)
    {
        $this->conn->del($lockId);
    }
}
