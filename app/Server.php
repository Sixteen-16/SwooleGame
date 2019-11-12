<?php
require_once __DIR__ . '/../vendor/autoload.php';

use app\Manager\DataCenter;
use app\Manager\Logic;
use app\Manager\TaskManager;

class Server
{
    const HOST       = '0.0.0.0';
    const PORT       = 8811;
    const FRONT_PORT = 8812;
    const CONFIG     = [
        'worker_num'            => 4,
        'document_root'         => '/mnt/d/php/SwooleGame/front', // v4.4.0以下版本, 此处必须为绝对路径
        'enable_static_handler' => true,
        'dispatch_mode'         => 5,
        'task_worker_num'       => 4,
    ];

    const CLIENT_CODE_MATCH_PLAYER = 600;

    private $ws;
    private $logic;

    public function __construct()
    {
        $this->logic = new Logic();

        $this->ws = new \Swoole\WebSocket\Server(self::HOST, self::PORT);
        $this->ws->set(self::CONFIG);
        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('workerStart', [$this, 'onWorkerStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('task', [$this, 'onTask']);
        $this->ws->on('finish', [$this, 'onFinish']);
        $this->ws->on('close', [$this, 'onClose']);

        // 监听前端接口
        $this->ws->listen(self::HOST, self::FRONT_PORT, SWOOLE_SOCK_TCP);

        $this->ws->start();
    }

    /**
     * 启动master进程
     * @param $server
     */
    public function onStart($server) {
        swoole_set_process_name('hide-amd-seek');
        echo sprintf("master start (listening on %s:%d)\n", self::HOST, self::PORT);
        DataCenter::initDataCenter();
    }

    /**
     * 启动worker进程
     * @param $server
     * @param $workerId
     */
    public function onWorkerStart($server, $workerId) {
        echo "server: onWorkerStart, worker_id: {$server->worker_id}\n";
        DataCenter::$server = $server;
    }

    /**
     * 打开连接
     * @param $server
     * @param $request
     */
    public function onOpen($server, $request) {
        DataCenter::log(sprintf('client open fd: %d', $request->fd));

        $playerId = $request->get['player_id'];
        DataCenter::setPlayerInfo($playerId, $request->fd);
    }

    /**
     * 获取信息
     * @param $server
     * @param $request
     */
    public function onMessage($server, $request) {
        DataCenter::log(sprintf('client open fd: %d, message: %s', $request->fd, $request->data));

        $data = json_decode($request->data, true);
        $playerId = DataCenter::getPlayerId($request->fd);
        switch ($data['code']) {
            case self::CLIENT_CODE_MATCH_PLAYER:
                $this->logic->matchPlayer($playerId);
                break;
        }
    }

    /**
     * 执行task异步任务
     * @param $server
     * @param $taskId
     * @param $srcWorkerId
     * @param $data
     * @return array
     */
    public function onTask($server, $taskId, $srcWorkerId, $data) {
        DataCenter::log('onTask', $data);

        $result = [];
        switch ($data['code']) {
            case TaskManager::TASK_CODE_FIND_PLAYER:
                $res = TaskManager::findPlayer();
                if ($res) {
                    $result['data'] = $res;
                }
                break;
        }
        if ($result) {
            $result['code'] = $data['code'];
            return $result;
        }
    }

    /**
     * 匹配结束逻辑
     * @param $server
     * @param $taskId
     * @param $data
     */
    public function onFinish($server, $taskId, $data) {
        DataCenter::log('onFinish', $data);

        switch ($data['code']) {
            case TaskManager::TASK_CODE_FIND_PLAYER:
                $this->logic->createRoom($data['data']['red_player'], $data['data']['blue_player']);
                break;
        }
    }

    /**
     * 关闭连接
     * @param $server
     * @param $fd
     */
    public function onClose($server, $fd) {
        DataCenter::log(sprintf('client close fd: %d', $fd));
        DataCenter::delPlayerInfo($fd);
    }
}
new Server();