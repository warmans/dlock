<?php
namespace Dlock\Datastore;

//just run the same tests as fakestore using the live backend
require_once(__DIR__.'/../../unit/Datastore/FakestoreTest.php');

class MemcacheTest extends \Dlock\Datastore\FakestoreTest
{
    /**
     * @var \Memcache
     */
    protected $memcacheConn;

    /**
     * @var Memcache
     */
    protected $object;

    public function setUp()
    {
        $this->memcacheConn = new \Memcache();
        $this->memcacheConn->connect('localhost');

        $this->object = new Memcache($this->memcacheConn);
    }

    public function testExpiredLock()
    {
        //1 second ttl
        $this->object = new Memcache($this->memcacheConn, 1);
        $this->object->aquireLock('foo');

        sleep(2);

        //two seconds have elapsed - we should be able to lock again
        $this->assertTrue($this->object->aquireLock('foo'));
    }
}
