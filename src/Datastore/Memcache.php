<?php
namespace Dlock\Datastore;

/**
 * Store locks in Memcache via PECL extension
 *
 * @author warmans
 */
class Memcache implements DatastoreInterface
{
    /**
     * @var \Memcache
     */
    private $conn;

    /**
     * @var int
     */
    private $lockTtl;

    /**
     * @param \Memcache $conn
     * @param int $lockTtl Num seconds the lock will be held until it expires.
     */
    public function __construct(\Memcache $conn, $lockTtl = 3600)
    {
        $this->conn = $conn;
        $this->lockTtl = $lockTtl;
    }

    /**
     * {@inheritdoc}
     */
    public function aquireLock($lockId)
    {
        return $this->conn->add($lockId, 1, false, $this->lockTtl);
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock($lockId)
    {
        $this->conn->delete($lockId);
    }
}
