<?php

namespace Weew\HttpServer;

interface IHttpServer {
    /**
     * Start server.
     */
    function start();

    /**
     * Stop server.
     */
    function stop();

    /**
     * @return bool
     */
    function isRunning();
}
