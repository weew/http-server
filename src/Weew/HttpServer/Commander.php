<?php

namespace Weew\HttpServer;

class Commander {
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
