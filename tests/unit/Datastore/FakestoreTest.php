<?php
namespace Dlock\Datastore;

class FakestoreTest  extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Fakestore
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Fakestore();
    }

    public function tearDown()
    {
        $this->object->releaseLock('foo');
        $this->object->releaseLock('bar');
    }

    public function testacquireLockOk()
    {
        $this->assertTrue($this->object->acquireLock('foo'));
    }

    public function testacquireLockAlreadyLocked()
    {
        $this->object->acquireLock('foo');
        $this->assertFalse($this->object->acquireLock('foo'));
    }

    public function testReleaseLock()
    {
        $this->object->acquireLock('foo');
        $this->object->releaseLock('foo');
        $this->assertTrue($this->object->acquireLock('foo'));
    }

    public function testacquireMultipleLocksOk()
    {
        $this->object->acquireLock('foo');
        $this->assertTrue($this->object->acquireLock('bar'));
    }

    public function testacquireMultipleLocksAlreadyLocked()
    {
        $this->object->acquireLock('foo');
        $this->object->acquireLock('bar');

        $this->assertFalse($this->object->acquireLock('foo'));
        $this->assertFalse($this->object->acquireLock('bar'));
    }
}
