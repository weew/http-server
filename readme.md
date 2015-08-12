# HTTP server

[![Build Status](https://travis-ci.org/weew/php-http-server.svg?branch=master)](https://travis-ci.org/weew/php-http-server)
[![Code Climate](https://codeclimate.com/github/weew/php-http-server/badges/gpa.svg)](https://codeclimate.com/github/weew/php-http-server)
[![License](https://poser.pugx.org/weew/php-http-server/license)](https://packagist.org/packages/weew/php-http-server)

## Usage

##### Working with the server

```php
// all files within the given directory will be available
// at http://localhost:9999, if you've passed a file
// name instead of a directory the server will always serve this
// file, no matter how the URI looks like
$server = new HttpServer('localhost', 9999, __DIR__);
$server->isRunning(); // true
$server->stop();
```

##### Advanced configuration

```php
// starting the server will wait for the server to start
// for a maximum of 2 seconds, then it will throw an exception
// saying that the process took too long to start
// the default value is 5.0 seconds
$waitForProcess = 2.0;
// enables log messages like
// [HTTP SERVER] Wed, 12 Aug 2015 19:49:25 +0200 - HTTP server started on localhost:9999 with PID 99412
// [HTTP SERVER] Wed, 12 Aug 2015 19:56:18 +0200 - Server is already running at localhost:9999 with PID 99535
// [HTTP SERVER] Wed, 12 Aug 2015 19:49:25 +0200 - Killing process with PID 99412
$enableOutput = true;

$server = new HttpServer('localhost', 9999, __DIR__, $waitForProcess, $enableOutput);
$server->start();
```
