<?php

namespace Weew\HttpServer;

class Messenger {
    /**
     * @param $date
     * @param $host
     * @param $port
     *
     * @return string
     */
    public function getServerIsNotRunningMessage($date, $host, $port) {
        return s(
            '%s - Server is not running at %s:%d.',
            $date, $host, $port
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
    public function getStartMessage($date, $host, $port, $pid) {
        return s(
            '%s - HTTP server started on %s:%d with PID %d',
            $date, $host, $port, $pid
        );
    }
}
