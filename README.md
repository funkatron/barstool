# Barstool #

Barstool is a simple JSON-based document storage library. It's an  implementation of [Lawnchair](http://brianleroux.github.com/lawnchair/) in PHP.

Barstool provides an alternative to mucking around with database schemas and ORM layers. If you just need to store and retrieve data, Barstool lets you do that -- without requiring you to install a "noSQL" system like CouchDB. This means it should work well on shared hosting systems.

## Requirements ##

PHP 5.2 or greater.

## Backends supported ##
* [PDO](http://php.net/pdo)
    * Only [PDO_SQLITE](http://php.net/manual/en/ref.pdo-sqlite.php) has been tested so far
* [SQLite](http://php.net/sqlite)

## Differences from Lawnchair ##

* Lawnchair uses asynchronous connections for almost all its supported backends.  This means most method require a callback function to do anything useful – they won't return data form the method call itself. PHP data connections are almost always synchronous, so Barstool methods return results and don't rely on callbacks for the most part. Callbacks *are* supported fully, though, including anonymous functions and closures (PHP > 5.3 only).
* PHP supports associative arrays, so Barstool converts any associative arrays to stdClass objects before storage.  If you `save()` an associative array, it will come back as an object when you `get()` it.