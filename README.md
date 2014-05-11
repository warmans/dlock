Dlock
=====

[![Build Status](https://travis-ci.org/warmans/dlock.svg?branch=master)](https://travis-ci.org/warmans/dlock) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/warmans/dlock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/warmans/dlock/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/warmans/dlock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/warmans/dlock/?branch=master)

Distributed locking library for PHP. This library allows a process to be locked across multiple servers using a cache
server (Redis or Memcache).

The example use case is where you have a process that must happen e.g. hourly but cannot be duplicated such as an
import. In this case all of your application servers can be setup with the import job but each hour only one
(the one with the fastest system clock) will be able to acquire the lock so all the others will fail. If any server
goes offline another will acquire the lock and imports will continue as normal.

Like most distributed systems care should be taken to maintain consistent system clocks across severs. If servers have
clocks that are more inconsistent than the time it takes to run a job you may still duplicate jobs. To avid this you
could store a globally unique identifier for a job somewhere (e.g. in a redis set) and simply check that a job hasn't
already been done after locking (note: not before or you have a race condition).

Locking is achieved using atomic functions to avoid race conditions. To discourage the user creating their own race
conditions there is no "isLocked" functionality. You must always attempt to create a lock to deterine the locked status.

* [Usage](#usage)
* [Options](#options)
* [Adapters](#adapters)
    - [Memcache](#memcache)
    - [Redis](#redis)
* [Running the tests](#running-the-tests)

Usage
--------------

Allow lock to handle locking/unlocking automatically using locked() method:

```php
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//datastore adapter
$adapter = new \Dlock\Datastore\Memcache($memcache);

$lock = new \Dlock\Lock($adapter);
$lock->locked(function(){
    //do something
});
```

Alternatively manage it yourself:

```php
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//datastore adapter
$adapter = new \Dlock\Datastore\Memcache($memcache);

$lock = new \Dlock\Lock($adapter);
if ($lock->acquire()) {
    //do something
    $lock->release();
} else {
   //handle lock failure
}
```

Finally if you want to wait for a lock to become available you can use a blocking lock:

```php
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//datastore adapter
$adapter = new \Dlock\Datastore\Memcache($memcache);

$lock = new \Dlock\Lock($adapter);

//wait for up to 30 seconds for lock then return false
if ($lock->acquire(30)) {
    //do something
    $lock->release();
} else {
   //handle lock failure
}

//or use closure with 30 second block
$lock->locked(function(){
    //do something
}, 30);
```

Options
--------

```php
$lock = new Lock([...], [...], $options);
```

The third optional argument of the Lock's constructor is an options array. The options are as follows:

| Option        | Default   | Description                           |
| ------------- | --------- | ------------------------------------- |
| ds_key_prefix | dlock     | Prefix used by cache key in datastore |


Adapters
----------

Any class that implements the DatastoreInterface can be used as an adapter:

```php
interface DatastoreInterface
{
    public function acquireLock($lockId);
    public function releaseLock($lockId);
}
```

Included implementations are as follows:

#### Memcache

```php
//raw memcache connection
$memcache = new \Memcache();
$memcache->connect('localhost');

//configure adapter with localhost connction and an hour TTL on locks
$adapter = new \Dlock\Datastore\Memcache($memcache, 3600);
```

#### Redis

```php
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
