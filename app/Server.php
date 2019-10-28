<?php
require_once __DIR__ . '/../vendor/autoload.php';

use app\Manager\DataCenter;

class Server
{
    const HOST       = '0.0.0.0';
    const PORT       = 8811;
    const FRONT_PORT = 8812;
    const CONFIG     = [
        'worker_number'         => 4,
        'document_root'         => '/mnt/d/php/SwooleGame/front', // v4.4.0以下版本, 此处必须为绝对路径
        'enable_static_handler' => true,
    ];

    private $ws;

    public function __construct()
    {
        $this->ws = new \Swoole\WebSocket\Server(self::HOST, self::PORT);
        $this->ws->set(self::CONFIG);
        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('workerStart', [$this, 'onWorkerStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('close', [$this, 'onClose']);

        // 监听前端接口
        $this->ws->listen(self::HOST, self::FRONT_PORT, SWOOLE_SOCK_TCP);

        $this->ws->start();
    }

    public function onStart($server) {
        swoole_set_process_name('hide-amd-seek');
        echo sprintf("master start (listening on %s:%d)\n", self::HOST, self::PORT);
    }

    public function onWorkerStart($server, $workerId) {
        echo "server: onWorkerStart, worker_id: {$server->worker_id}\n";
    }

    public function onOpen($server, $request) {
        DataCenter::log(sprintf('client open fd: %d', $request->fd));
    }

    public function onMessage($server, $request) {
        DataCenter::log(sprintf('client open fd: %d, message: %s', $request->fd, $request->data));
        $server->push($request->fd, 'test success');
    }

    public function onClose($server, $fd) {
        DataCenter::log(sprintf('client close fd: %d', $fd));
    }
}
new Server();