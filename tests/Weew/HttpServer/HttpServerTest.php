<?php

namespace Tests\Weew\HttpServer;

use PHPUnit_Framework_TestCase;
use Weew\HttpServer\HttpServer;

class HttpServerTest extends PHPUnit_Framework_TestCase {
    public function test_start_and_stop_server() {
        $server = new HttpServer('localhost', 6789, __DIR__ . '/test');
        $server->enableOutput();
        $server->stop();
        $server->start();
        $server->start();
        $this->assertTrue($server->isRunning());
        $server->stop();
        $this->assertFalse($server->isRunning());
    }

    public function test_server_serves_files() {
        $server = new HttpServer('localhost', 6789, __DIR__);
        $server->start();
        $this->assertEquals(
            file_get_contents(__DIR__.'/test'),
            file_get_contents('http://localhost:6789/test')
        );
        $server->stop();
    }

    public function test_enable_and_disable_output() {
        $server = new HttpServer('localhost', 6789, __DIR__);
        $server->enableOutput();
        $server->disableOutput();
    }
}
