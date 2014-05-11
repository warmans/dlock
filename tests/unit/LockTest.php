<?php
namespace Dlock;

class LockTest  extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Dlock\Lock
     */
    private $object;

    public function setUp()
    {
        $this->object = new Lock(new Datastore\Fakestore, 'testlock');
    }

    public function testGetDatastore()
    {
        $this->assertTrue($this->object->getDatastore() instanceof Datastore\DatastoreInterface);
    }

    public function testGetLockId()
    {
        $this->assertEquals('testlock', $this->object->getId());
    }

    public function testLockOk()
    {
        $this->assertTrue($this->object->aquire());
    }

    public function testLockAlreadyLocked()
    {
        $this->assertTrue($this->object->aquire());
        $this->assertFalse($this->object->aquire());
    }

    public function testUnlockOk()
    {
        $this->object->aquire();
        $this->assertFalse($this->object->aquire());
        $this->object->release();
        $this->assertTrue($this->object->aquire());
    }

    public function testLockedAlreadyLocked()
    {
        $this->object->aquire();
        try {
            $this->object->locked(function() {
                return;
            });
        } catch (\Exception $e) {
            return true;
        }

        $this->fail('Exception was not raised on duplicate lock');
    }

    public function testLockedCreatesLock()
    {
        $o = $this->object;
        $res = $this->object->locked(function() use ($o) {
            //if the lock fails we must have created one already
            return $o->aquire();
        });
        $this->assertFalse($res);
    }

    public function testLockedReleasesLock()
    {
        $this->object->locked(function() {
            return true;
        });
        $this->assertTrue($this->object->aquire());
    }

    public function testLockedRethrowsExceptions()
    {
        try {
            $this->object->locked(function() {
                throw new \Exception('foo');
            });
        } catch (\Exception $e) {
            $this->assertEquals('foo', $e->getMessage());
            return;
        }

        $this->fail('Exception was not rethrown');
    }

    public function testLockedUnlocksOnException()
    {
        try {
            $this->object->locked(function() {
                throw new \Exception();
            });
        } catch (\Exception $e) {
            $this->assertTrue($this->object->aquire());
            return;
        }

        $this->fail('Exception was not rethrown - cannot test unlock');
    }
}
