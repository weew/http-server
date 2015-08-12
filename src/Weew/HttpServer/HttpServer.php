<?php

namespace Weew\HttpServer;

use Exception;
use Weew\Timer\ITimer;
use Weew\Timer\Timer;

class HttpServer implements IHttpServer {
    /**
     * @var int
     */
    protected $pid;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var float
     */
    protected $waitForServer;

    /**
     * @var bool
     */
    protected $enableOutput;

    /**
     * @param $host
     * @param $port
     * @param $root
     * @param float $waitForServer
     * @param bool $enableOutput
     */
    public function __construct($host, $port, $root, $waitForServer = 5.0, $enableOutput = false) {
        $this->host = $host;
        $this->port = $port;
        $this->root = $root;
        $this->enableOutput = $enableOutput;
        $this->waitForServer = $waitForServer;
    }

    /**
     * Disable server output.
     */
    public function disableOutput() {
        $this->enableOutput = false;
    }

    /**
     * Enable server output.
     */
    public function enableOutput() {
        $this->enableOutput = true;
    }

    /**
     * Start server.
     */
    public function start() {
        if ( ! $this->checkIfServerIsRunning()) {
            $this->startServer();
            $this->registerShutdownHandler();
            $this->waitForServerToStart();
        }
    }

    /**
     * Stop server.
     */
    public function stop() {
        if ( ! $this->isRunning()) {
            return;
        }

        $command = $this->getStopCommand($this->pid);
        $this->echoMessage($this->getStopMessage(date('r'), $this->pid));
        exec($command);
    }

    /**
     * @return bool
     */
    public function isRunning() {
        $command = $this->getPidCommand($this->host, $this->port);
        exec($command, $output);

        return count($output) > 0;
    }

    /**
     * @param $host
     * @param $port
     * @param $root
     *
     * @return string
     */
    public function getStartCommand($host, $port, $root) {
        $targetFlag = '-t';

        if (is_file($root)) {
            $targetFlag = '-s';
        }

        return s(
            'php -S %s:%d %s %s >/dev/null 2>&1 & echo $!',
            $host, $port, $targetFlag, $root
        );
    }

    /**
     * @param $pid
     *
     * @return string
     */
    public function getStopCommand($pid) {
        return s('kill %d', $pid);
    }

    /**
     * @param $date
     * @param $host
     * @param $port
     * @param $pid
     *
     * @return string
     */
    public function getStartMessage($date, $host, $port, $pid) {
        return s(
            '%s - HTTP server started on %s:%d with PID %d',
            $date, $host, $port, $pid
        );
    }

    /**
     * @param $date
     * @param $pid
     *
     * @return string
     */
    public function getStopMessage($date, $pid) {
        return s(
            '%s - Killing process with PID %d', $date, $pid
        );
    }

    /**
     * @param $date
     * @param $host
     * @param $port
     * @param $pid
     *
     * @return string
     */
    public function getServerIsAlreadyRunningMessage($date, $host, $port, $pid) {
        return s(
            '%s - Server is already running at %s:%d with PID %d',
            $date, $host, $port, $pid
        );
    }

    /**
     * @param string $message
     */
    public function echoMessage($message = '') {
        if ( ! $this->enableOutput) {
            return;
        }

        if ($message) {
            $message = s('[HTTP SERVER] %s', $message);
        }

        echo PHP_EOL . $message . PHP_EOL;
    }

    /**
     * @return mixed
     */
    public function getPid() {
        $command = $this->getPidCommand($this->host, $this->port);
        exec($command, $output);

        return array_get($output, 0);
    }

    /**
     * @param $host
     * @param $port
     *
     * @return string
     */
    public function getPidCommand($host, $port) {
        return s('ps | grep -v grep | grep "%s:%d"', $host, $port);
    }

    /**
     * Check if server is running and print a debug message.
     *
     * @return bool
     */
    protected function checkIfServerIsRunning() {
        if ($this->isRunning()) {
            $this->pid = $this->getPid();

            $this->echoMessage(
                $this->getServerIsAlreadyRunningMessage(
                    date('r'), $this->host, $this->port, $this->pid
                )
            );

            return true;
        }

        return false;
    }

    /**
     * Start server.
     */
    protected function startServer() {
        $command = $this->getStartCommand($this->host, $this->port, $this->root);
        exec($command, $output);

        $this->pid = $this->getPid();

        $this->echoMessage($this->getStartMessage(
            date('r'), $this->host, $this->port, $this->pid
        ));
    }

    /**
     * Fail-safe server shutdown.
     */
    protected function registerShutdownHandler() {
        register_shutdown_function(function () {
            $this->stop();
        });
    }

    /**
     * @throws Exception
     */
    protected function waitForServerToStart() {
        if ($this->waitForServer > 0) {
            $timer = $this->createTimer();
            $timer->start();

            while ( ! $this->isRunning()) {
                if ($timer->getDuration() < $this->waitForServer) {
                    usleep(100000); // 0.1 second
                } else {
                    throw new Exception(
                        s('Could not start server after %d seconds.', $this->waitForServer)
                    );
                }
            }
        }
    }

    /**
     * @return ITimer
     */
    protected function createTimer() {
        return new Timer();
    }
}
