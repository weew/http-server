<?php

namespace Weew\HttpServer;

class Commander {
    /**
     * @param $host
     * @param $port
     * @param $root
     * @param $log
     *
     * @return string
     */
    public function getStartCommand($host, $port, $root, $log) {
        $targetFlag = '-t';

        if (is_file($root)) {
            $targetFlag = '-s';
        }

        return s(
            'php -S %s:%d %s %s >>%s 2>&1 & echo $!',
            $host, $port, $targetFlag, $root, $log
        );
    }


    /**
     * @param $pid
     *
     * @return string
     */
    public function getStopCommand($pid) {
        return s('kill -9 %d', $pid);
    }

    /**
     * @param $host
     * @param $port
     *
     * @return string
     */
    public function getPidCommand($host, $port) {
        return s('ps x | grep -v grep | grep "%s:%d"', $host, $port);
    }
}
