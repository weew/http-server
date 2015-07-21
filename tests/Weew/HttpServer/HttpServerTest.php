<?php

namespace Tests\Weew\HttpServer;

use PHPUnit_Framework_TestCase;
use Weew\HttpServer\HttpServer;

class HttpServerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var HttpServer
     */
    public static $server;

    public static function setUpBeforeClass() {
        static::$server = new HttpServer('localhost', 6789, __DIR__);
        static::$server->start();
    }

    public static function tearDownBeforeClass() {
        static::$server->stop();
    }

    public function test_start_and_stop_server() {
        $this->assertTrue(static::$server->isRunning());
    }

    public function test_server_serves_files() {
        $this->assertEquals(
            file_get_contents(__DIR__.'/test'),
            file_get_contents('http://localhost:6789/test')
        );
    }
}
