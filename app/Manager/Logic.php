<?php
namespace app\Manager;

class Logic
{
    /**
     * 匹配玩家
     * @param string $playerId 玩家ID
     */
    public function matchPlayer(string $playerId) {
        DataCenter::pushPlayerToWaitList($playerId);

        DataCenter::$server->task(['code' => TaskManager::TASK_CODE_FIND_PLAYER]);
    }

    /**
     * 创建房间
     * @param string $redPlayerId
     * @param string $bluePlayerId
     */
    public function createRoom(string $redPlayerId, string $bluePlayerId) {
        $roomId = md5($redPlayerId . $bluePlayerId . time());

        $this->bindRoomWorker($redPlayerId, $roomId);
        $this->bindRoomworker($bluePlayerId, $roomId);
    }

    /**
     * 绑定房间进程
     * @param string $playerId
     * @param string $roomId
     */
    public function bindRoomWorker(string $playerId, string $roomId) {
        $playerFd = DataCenter::getPlayerFd($playerId);

        DataCenter::$server->bind($playerFd, crc32($roomId));
        Sender::sendMessage($playerId, Sender::MSG_ROOM_ID, ['room_id' => $roomId]);
    }
}