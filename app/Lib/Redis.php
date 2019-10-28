<?php
namespace app\Lib;

class Redis
{
    protected static $instance;
    protected static $config = [
        'host' => '127.0.0,1',
        'port' => 6379
    ];

    public static function getInstance() {
        if (empty(self::$instance)) {
            $instance = new \Redis();
            $instance->connect(
                self::$config['host'],
                self::$config['port']
            );
            self::$instance = $instance;
        }
        return self::$instance;
    }
}