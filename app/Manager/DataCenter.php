<?php
namespace app\Manager;

use app\Lib\Redis;

class DataCenter
{
    public static $global;

    public static function log(string $info, array $content = [], string $level = 'INFO') {
        if ($content) {
            echo sprintf("[%s][%s]: %s %s\n", date('Y-m-d H:i:s'), $level, $info,
                json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            echo sprintf("[%s][%s]: %s\n", date('Y-m-d H:i:s'), $level, $info);
        }
    }

    public static function getRedis() {
        return Redis::getInstance();
    }
}