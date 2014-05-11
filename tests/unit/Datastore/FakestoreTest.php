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

    public function testAquireLockOk()
    {
        $this->assertTrue($this->object->aquireLock('foo'));
    }

    public function testAquireLockAlreadyLocked()
    {
        $this->object->aquireLock('foo');
        $this->assertFalse($this->object->aquireLock('foo'));
    }

    public function testReleaseLock()
    {
        $this->object->aquireLock('foo');
        $this->object->releaseLock('foo');
        $this->assertTrue($this->object->aquireLock('foo'));
    }

    public function testAquireMultipleLocksOk()
    {
        $this->object->aquireLock('foo');
        $this->assertTrue($this->object->aquireLock('bar'));
    }

    public function testAquireMultipleLocksAlreadyLocked()
    {
        $this->object->aquireLock('foo');
        $this->object->aquireLock('bar');

        $this->assertFalse($this->object->aquireLock('foo'));
        $this->assertFalse($this->object->aquireLock('bar'));
    }
}
