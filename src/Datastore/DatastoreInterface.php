<?php
namespace Dlock\Datastore;

/**
 * All datastores must implement this interface.
 * 
 * @author warmans
 */
interface DatastoreInterface
{
    /**
     * Create the lock. Returns false if lock was not possible, otherwise true.
     *
     * @param string $lockId
     * @return bool
     */
    public function acquireLock($lockId);

    /**
     * Release the lock. Returns false if lock could not be released.
     *
     * @param string $lockId
     * @return bool
     */
    public function releaseLock($lockId);
}
