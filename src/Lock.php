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
     * @var array
     */
    private $options = array(
        //datastore key prefix. The cache key used for the lock will be prefixed with this value.
        'ds_key_prefix'=>'dlock'
    );

    /**
     * @param \Dlock\Datastore\DatastoreInterface $datastore
     * @param string $id identifier for lock to allow multiple locks to be created for different purposes.
     * @param array $options additional options
     */
    public function __construct(Datastore\DatastoreInterface $datastore, $id = 'unnamed', array $options = array())
    {
        $this->datastore = $datastore;
        $this->id = $id;
        $this->options = array_merge($this->options, $options);
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
     * Get option value
     *
     * @param string $name option name
     * @return mixed
     */
    public function getOpt($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Set an option value
     *
     * @param string $name
     * @param mixed $value
     */
    public function setOpt($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * acquire the lock.
     *
     * @param bool $block number of seconds to block for. 0 is non-blocking.
     *
     * @return bool false on failure
     */
    public function acquire($block = 0)
    {
        $started = time();
        while (!$this->getDatastore()->acquireLock("{$this->getOpt('ds_key_prefix')}:{$this->getId()}")) {
            if ($started + $block <= time()) {
                return false;
            }
            sleep(1);
        }
        return true;
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release()
    {
        return $this->getDatastore()->releaseLock("{$this->getOpt('ds_key_prefix')}:{$this->getId()}");
    }

    /**
     * Lock the execution of a single function
     *
     * @param \Closure $task
     * @param bool $blocking should the process block waiting to acquire lock? If false fail instantly.
     * @param int $timeout number of seconds to wait for lock. If no lock in acquired within this time return false.
     *
     * @return mixed result of closure
     */
    public function locked(\Closure $task, $block = 0)
    {
        if ($this->acquire($block)) {
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
        throw new \RuntimeException('Could not acquire lock');
    }
}
