<?php
namespace app\Manager;

class Sender
{
    const MSG_ROOM_ID     = 1001;
    const MSG_WAIT_PLAYER = 1002;
    const MSG_ROOM_START  = 1003;
    const MSG_GAME_INFO   = 1004;
    const MSG_GAME_OVER   = 1005;

    const CODE_MSG = [
        self::MSG_ROOM_ID     => '房间ID',
        self::MSG_WAIT_PLAYER => '等待其他玩家中...',
        self::MSG_ROOM_START  => '游戏开始!',
        self::MSG_GAME_INFO   => 'game info',
        self::MSG_GAME_OVER   => '游戏结束!'
    ];

    /**
     * 发送消息
     * @param string $playerId
     * @param int $code
     * @param array $data
     */
    public static function sendMessage(string $playerId, int $code, array $data = []) {
        $playerFd = DataCenter::getPlayerFd($playerId);
        if (!$playerFd) {
            return;
        }

        $messageData = [
            'code' => $code,
            'msg'  => self::CODE_MSG[$code] ?? '',
            'data' => $data,
        ];
        DataCenter::$server->push($playerFd, json_encode($messageData));
    }
}