<?php
namespace Dlock\Datastore;

/**
 * Fake store used in testing. Keeps lock in memory so it only locks the running script.
 *
 * @author warmans
 */
class Fakestore implements DatastoreInterface
{
    private $locks = array();

    public function aquireLock($lockId)
    {
        if (isset($this->locks[$lockId])) {
            return false;
        }
        return $this->locks[$lockId] = true;
    }

    public function releaseLock($lockId)
    {
        unset($this->locks[$lockId]);
    }
}
