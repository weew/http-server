<?php

namespace Weew\HttpServer;

use Exception;
use Weew\Timer\ITimer;
use Weew\Timer\Timer;

class HttpServer implements IHttpServer {
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
     * @var Commander
     */
    protected $commander;

    /**
     * @var Messenger
     */
    protected $messenger;

    /**
     * @var string
     */
    protected $logFile = '/dev/null';

    /**
     * @param $host
     * @param $port
     * @param $root
     * @param float $waitForServer
     * @param bool $enableOutput
     */
    public function __construct(
        $host, $port, $root,
        $waitForServer = 5.0,
        $enableOutput = false
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->root = $root;
        $this->enableOutput = $enableOutput;
        $this->waitForServer = $waitForServer;

        $this->commander = $this->createCommander();
        $this->messenger = $this->createMessenger();
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
     * File the server will be logging into.
     * Use /dev/null to disable logging
     * 
     * @param string $logFile
     */
    public function setLogFile($logFile) {
        $this->logFile = $logFile;
    }

    /**
     * Start server.
     */
    public function start() {
        if ($this->isRunning()) {
            $this->echoMessage(
                $this->messenger->getServerIsAlreadyRunningMessage(
                    date('r'), $this->host, $this->port, $this->getPid()
                )
            );
        } else {
            $this->startServer();
            $this->registerShutdownHandler();
            $this->waitForServerToStart();
        }
    }

    /**
     * Stop server.
     *
     * @throws Exception
     */
    public function stop() {
        if ($this->isRunning()) {
            $this->stopServer();
            $this->waitForServerToStop();
        } else {
            $this->echoMessage(
                $this->messenger->getServerIsNotRunningMessage(
                    date('r'), $this->host, $this->port
                )
            );
        }
    }

    /**
     * @return bool
     */
    public function isRunning() {
        $command = $this->commander
            ->getPidCommand($this->host, $this->port);
        exec($command, $output);

        return count($output) > 0;
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
        $command = $this->commander
            ->getPidCommand($this->host, $this->port);
        exec($command, $output);

        return array_get($output, 0);
    }

    /**
     * Start server.
     */
    protected function startServer() {
        $command = $this->commander
            ->getStartCommand($this->host, $this->port, $this->root, $this->logFile);
        exec($command, $output);

        $this->echoMessage($this->messenger->getStartMessage(
            date('r'), $this->host, $this->port, $this->getPid()
        ));
    }

    /**
     * Stop server.
     */
    protected function stopServer() {
        $pid = $this->getPid();
        $command = $this->commander->getStopCommand($pid);
        exec($command);

        $this->echoMessage(
            $this->messenger->getStopMessage(date('r'), $pid)
        );
    }

    /**
     * Fail-safe server shutdown.
     */
    protected function registerShutdownHandler() {
        register_shutdown_function([$this, 'stop']);
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
     * @throws Exception
     */
    protected function waitForServerToStop() {
        if ($this->waitForServer > 0) {
            $timer = $this->createTimer();
            $timer->start();

            while ($this->isRunning()) {
                if ($timer->getDuration() < $this->waitForServer) {
                    usleep(100000); // 0.1 second
                } else {
                    throw new Exception(
                        s('Could not stop server after %d seconds.', $this->waitForServer)
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

    /**
     * @return Commander
     */
    protected function createCommander() {
        return new Commander();
    }

    /**
     * @return Messenger
     */
    protected function createMessenger() {
        return new Messenger();
    }
}
