Dlock
=====

Distributed locking library for PHP. This library allows a process to be locked across multiple servers using a cache server like Redis or Memcache.

Usage
------

Allow lock to handle locking/unlocking automatically using locked() method.
```
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//datastore adapter
$adapter = new \Dlock\Datastore\Memcache($memcache);

//usable lock
$lock = new \Dlock\Lock($adapter);
$lock->locked(function(){
    //do something
});
```

Alternatively manage it yourself

```
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//datastore adapter
$adapter = new \Dlock\Datastore\Memcache($memcache);

//usable lock
$lock = new \Dlock\Lock($adapter);
if ($lock->lock()) {
    //do something
    $lock->unlock();
} else {
   //handle lock failure
}
```


Running the tests
------------------
Tests are split into two testcases; unit and integration. Integration tests require a running Redis and Memcache server on your localhost.

To run all from root directory:

    phpunit

To run just unit tests:

    phpunit --testsuite=unit

To run just integration tests:

    phpunit --testsuite=integration