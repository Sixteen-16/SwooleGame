<?php
namespace app\Manager;

class Sender
{
    const MSG_ROOM_ID = 2;

    const CODE_MSG = [
        self::MSG_ROOM_ID => '房间ID'
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