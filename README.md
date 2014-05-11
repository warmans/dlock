Dlock
=====

Distributed locking library for PHP. This library allows a process to be locked across multiple servers using a cache
server (Redis or Memcache).

The example use case is where you have a process that must happen e.g. hourly but cannot be duplicated such as an
import. In this case all of your application servers can be setup with the import job but each hour only one
(the one with the fastest system clock) will be able to aquire the lock so all the others will fail. If any server
goes offline another will aquire the lock and imports will continue as normal.

Like most distributed systems care should be taken to maintain consistent system clocks across severs. If servers have
clocks that are more inconsistent than the time it takes to run a job you may still duplicate jobs. To avid this you
could store a globally unique identifier for a job somewhere (e.g. in a redis set) and simply check that a job hasn't
already been done after locking (note: not before or you have a race condition).

Locking is achieved using atomic functions to avoid race conditions. To discourage the user creating their own race
conditions there is no "isLocked" functionality. You must always attempt to create a lock to deterine the locked status.

* [Usage](#usage)
* [Adapters](#adapters)
    - [Memcache](#memcache)
    - [Redis](#redis)
* [Running the tests](#running-the-tests)

Usage
--------------

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

Adapters
----------

Any class that implements the DatastoreInterface can be used as an adapter:

```
interface DatastoreInterface
{
    public function aquireLock($lockId);
    public function releaseLock($lockId);
}
```

Included implementations are as follows:

#### Memcache
```
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//configure adapter with localhost connction and an hour TTL on locks
$adapter = new \Dlock\Datastore\Memcache($memcache, 3600);
```

#### Redis
```
//raw redis connection
$redis = new \Redis();
$redis->connect('localhost');

//configure adapter with localhost connction and an hour TTL on locks
$adapter = new \Dlock\Datastore\Redis($memcache, 3600);
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