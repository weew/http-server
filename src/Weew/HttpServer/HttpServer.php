<?php

namespace Weew\HttpServer;

class HttpServer implements IHttpServer {
    /**
     * @var bool
     */
    private static $enableOutput = false;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $root;

    /**
     * @param $host
     * @param $port
     * @param $root
     */
    public function __construct($host, $port, $root) {
        $this->host = $host;
        $this->port = $port;
        $this->root = $root;
    }

    /**
     * Disable server output.
     */
    public static function disableOutput() {
        static::$enableOutput = false;
    }

    /**
     * Enable server output.
     */
    public static function enableOutput() {
        static::$enableOutput = true;
    }

    /**
     * Start server.
     */
    public function start() {
        if ($this->isRunning()) {
            $this->pid = $this->getPid();

            $this->echoMessage(
                $this->getServerIsAlreadyRunningMessage(
                    $this->host, $this->port, $this->pid
                )
            );

            return;
        }

        $command = $this->getStartCommand($this->host, $this->port, $this->root);
        exec($command, $output);

        $this->pid = $this->getPid();

        $this->echoMessage($this->getStartMessage(
            date('r'), $this->host, $this->port, $this->pid
        ));

        register_shutdown_function(function () {
            $this->stop();
        });
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
            '%s - Web server started on %s:%d with PID %d',
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
            '%s - Killing process with ID %d', $date, $pid
        );
    }

    /**
     * @param $host
     * @param $port
     * @param $pid
     *
     * @return string
     */
    public function getServerIsAlreadyRunningMessage($host, $port, $pid) {
        return s('Server is already running at %s:%d with PID %d', $host, $port, $pid);
    }

    /**
     * @param string $message
     */
    public function echoMessage($message = '') {
        if ( ! static::$enableOutput) {
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
}
