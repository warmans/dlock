<?php
namespace Dlock\Datastore;

/**
 * Fake store used in testing. Keeps lock in memory so it only locks the running script. Don't use this in production.
 *
 * @author warmans
 */
class Fakestore implements DatastoreInterface
{
    /**
     * @var array
     */
    private $locks = array();

    /**
     * {@inheritdoc}
     */
    public function acquireLock($lockId)
    {
        if (isset($this->locks[$lockId])) {
            return false;
        }
        return $this->locks[$lockId] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock($lockId)
    {
        unset($this->locks[$lockId]);
    }
}
