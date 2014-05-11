<?php
namespace Dlock\Datastore;

/**
 * Store locks in Memcache via PECL extension
 *
 * @author warmans
 */
class Memcache implements DatastoreInterface
{
    private $conn;
    private $lockTtl;

    public function __construct(\Memcache $conn, $lockTtl=3600)
    {
        $this->conn = $conn;
        $this->lockTtl = $lockTtl;
    }

    public function aquireLock($lockId)
    {
        return $this->conn->add($lockId, 1, false, $this->lockTtl);
    }

    public function releaseLock($lockId)
    {
        $this->conn->delete($lockId);
    }
}
