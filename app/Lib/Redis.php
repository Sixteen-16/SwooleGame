<?php
namespace app\Lib;

class Redis
{
    protected static $instance;
    protected static $config = [
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => '123456',
        'db'   => '1',
    ];

    public static function getInstance() {
        if (empty(self::$instance)) {
            $instance = new \Redis();
            $instance->connect(
                self::$config['host'],
                self::$config['port']
            );
            $instance->auth(self::$config['auth']);
            $instance->select(self::$config['db']);
            self::$instance = $instance;
        }
        return self::$instance;
    }
}