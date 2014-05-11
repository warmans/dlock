<?php
namespace Dlock;

/**
 * @author warmans
 */
class Lock
{
    /**
     * @var Datastore\DatastoreInterface
     */
    private $datastore;

    /**
     * @var string
     */
    private $id;

    /**
     * @param \Dlock\Datastore\DatastoreInterface $datastore
     * @param string $id identifier for lock to allow multiple locks to be created for different purposes.
     */
    public function __construct(Datastore\DatastoreInterface $datastore, $id = 'unnamed')
    {
        $this->datastore = $datastore;
        $this->id = $id;
    }

    /**
     * Get the configured datastore.
     *
     * @return Datastore\DatastoreInterface
     */
    public function getDatastore()
    {
        return $this->datastore;
    }

    /**
     * Get the lock identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Aquire the lock.
     *
     * @param bool $blocking should the process block waiting to aquire lock? If false fail instantly.
     * @param int $timeout number of seconds to wait for lock. If no lock in aquired within this time return false.
     *
     * @return bool false on failure
     */
    public function aquire($blocking = false, $timeout = 60)
    {
        if (false === $blocking) {
            return $this->getDatastore()->aquireLock("dlock:{$this->getId()}");
        } else {
            while (!$this->getDatastore()->aquireLock("dlock:{$this->getId()}")) {
                if (--$timeout <= 0) {
                    return false;
                }
                sleep(1);
            }
            return true;
        }
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        return $this->getDatastore()->releaseLock("dlock:{$this->getId()}");
    }

    /**
     * Lock the execution of a single function
     *
     * @param \Closure $task
     * @param bool $blocking should the process block waiting to aquire lock? If false fail instantly.
     * @param int $timeout number of seconds to wait for lock. If no lock in aquired within this time return false.
     *
     * @return mixed result of closure
     */
    public function locked(\Closure $task, $blocking = false, $timeout = 60)
    {
        if ($this->aquire($blocking, $timeout)) {
            try {
                $res = $task();
                $this->release();
                return $res;
            } catch (\Exception $e) {
                $this->release();
                throw $e;
            }
        }

        //locked throws exceptions instead of returning false because the user may return false from their function
        throw new \RuntimeException('Could not aquire lock');
    }
}
