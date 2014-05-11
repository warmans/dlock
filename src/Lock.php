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
    public function __construct(Datastore\DatastoreInterface $datastore, $id='unnamed')
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
     * Apply the lock.
     *
     * @return bool
     */
    public function lock()
    {
        return $this->getDatastore()->aquireLock("dlock:{$this->getId()}");
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function unlock()
    {
        return $this->getDatastore()->releaseLock("dlock:{$this->getId()}");
    }

    /**
     * Lock the execution of a single function
     *
     * @param \Closure $task
     * @return mixed result of closure
     */
    public function locked(\Closure $task)
    {
        if ($this->lock()) {
            try {
                $res = $task();
                $this->unlock();
                return $res;
            } catch (\Exception $e) {
                $this->unlock();
                throw $e;
            }
        }

        //locked throws exceptions instead of returning false because the user may return false from their function
        throw new \RuntimeException('Could not aquire lock');
    }
}
