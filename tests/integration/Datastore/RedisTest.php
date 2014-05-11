<?php
namespace Dlock\Datastore;

//just run the same tests as fakestore using the live backend
require_once(__DIR__.'/../../unit/Datastore/FakestoreTest.php');

class RedisTest extends \Dlock\Datastore\FakestoreTest
{
    /**
     * @var \Redis
     */
    protected $redisConn;

    /**
     * @var Redis
     */
    protected $object;

    public function setUp()
    {
        $this->redisConn = new \Redis();
        $this->redisConn->connect('localhost');

        $this->object = new Redis($this->redisConn);
    }

    public function testExpiredLock()
    {
        //1 second ttl
        $this->object = new Redis($this->redisConn, 1);
        $this->object->aquireLock('foo');

        sleep(2);

        //two seconds have elapsed - we should be able to lock again
        $this->assertTrue($this->object->aquireLock('foo'));
    }
}
