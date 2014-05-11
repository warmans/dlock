<?php
namespace Dlock\Datastore;

/**
 * @author warmans
 */
interface DatastoreInterface
{
    /**
     * @param string $lockId
     */
    public function aquireLock($lockId);

    /**
     * @param string $lockId
     */
    public function releaseLock($lockId);

}
