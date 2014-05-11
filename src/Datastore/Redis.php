<?php
namespace Dlock\Datastore;

/**
 * Store locks in Redis via PECL extension
 *
 * @author warmans
 */
class Redis implements DatastoreInterface
{
    /**
     * @var \Redis
     */
    private $conn;

    /**
     * @var int
     */
    private $lockTtl;

    /**
     * @param \Redis $conn
     * @param int $lockTtl Num seconds the lock will be held until it expires.
     */
    public function __construct(\Redis $conn, $lockTtl = 3600)
    {
        $this->conn = $conn;
        $this->lockTtl = $lockTtl;
    }

    /**
     * {@inheritdoc}
     */
    public function acquireLock($lockId)
    {
        if ($this->conn->setnx($lockId, 1)) {
            $this->conn->expire($lockId, $this->lockTtl);
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock($lockId)
    {
        $this->conn->del($lockId);
    }
}
