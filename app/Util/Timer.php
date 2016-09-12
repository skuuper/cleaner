<?php

namespace Littlebit\Util;


class Timer
{
    private static $registry = [];

    public static function start($action = 'default') {
        self::$registry[$action] = microtime(true);
    }

    /**
     * @param string $action
     * @param bool $flush
     */
    public static function finish($action = 'default', $flush = false) {
        $diff =  microtime(true) - self::$registry[$action];
        $msg = sprintf("Finished in \t%.2f\t seconds (%s)<br>", $diff, $action);
        Debug::out($msg);

        if ($flush) {
            unset(self::$registry[$action]);
        }
    }
}