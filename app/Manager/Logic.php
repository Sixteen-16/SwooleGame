<?php
namespace app\Manager;

use app\Model\Player;

class Logic
{
    const PLAYER_DISPLAY_LEN = 2;

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

        DataCenter::setRoomId($roomId, $playerId);
        DataCenter::$server->bind($playerFd, crc32($roomId));
        Sender::sendMessage($playerId, Sender::MSG_ROOM_ID, ['room_id' => $roomId]);
    }

    /**
     * 启动房间
     * @param string $roomId
     * @param string $playerId
     */
    public function startRoom(string $roomId, string $playerId) {
        if (!isset(DataCenter::$global['rooms'][$roomId])) {
            DataCenter::$global['rooms'][$roomId] = [
                'id'      => $roomId,
                'manager' => new Game()
            ];
        }

        /**
         * @var Game $gameManager
         */
        $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];
        if (empty($gameManager->getPlayers())) {
            // 第一个玩家
            $gameManager->createPlayer($playerId, 6, 1);
            Sender::sendMessage($playerId, Sender::MSG_WAIT_PLAYER);
        } else {
            // 第二个玩家
            $gameManager->createPlayer($playerId, 6, 10);
            Sender::sendMessage($playerId, Sender::MSG_ROOM_START);
            $this->sendGameInfo($roomId);
        }
    }

    /**
     * 发送游戏详情
     * @param string $roomId
     */
    private function sendGameInfo(string $roomId) {
        /**
         * @var Game $gameManager
         * @var Player $player
         */
        $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];
        $players     = $gameManager->getPlayers();
        $mapData     = $gameManager->getMapData();

        foreach ($players as $player) {
            $mapData[$player->getX()][$player->getY()] = $player->getId();
        }
        foreach ($players as $player) {
            $data = [
                'players'  => $players,
                'map_data' => $this->getNearMap($mapData, $player->getX(), $player->getY())
            ];
            Sender::sendMessage($player->getId(), Sender::MSG_GAME_INFO, $data);
        }
    }

    /**
     * 获取邻近地图数据
     * @param array $mapData
     * @param int $x
     * @param int $y
     * @return array
     */
    private function getNearMap(array $mapData, int $x, int $y) {
        $result = [];
        for ($i = -1 * self::PLAYER_DISPLAY_LEN; $i <= self::PLAYER_DISPLAY_LEN; $i++) {
            $tmp = [];
            for ($j = -1 * self::PLAYER_DISPLAY_LEN; $j <= self::PLAYER_DISPLAY_LEN; $j++) {
                $tmp[] = $mapData[$x + $i][$y + $j] ?? 0;
            }
            $result[] = $tmp;
        }
        return $result;
    }

    /**
     * 玩家移动
     * @param string $playerId 玩家ID
     * @param string $direction 方向
     */
    public function playerMove(string $playerId, string $direction) {
        if (!in_array($direction, Player::DIRECTION)) {
            echo $direction;
            return;
        }

        $roomId = DataCenter::getRoomId($playerId);

        if (isset(DataCenter::$global['rooms'][$roomId])) {

            $this->checkGameOver($roomId);

            /**
             * @var Game $gameManager
             */
            $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];
            $gameManager->playerMove($playerId, $direction);
            $this->sendGameInfo($roomId);
        }
    }

    /**/
    private function checkGameOver(string $roomId) {
        /**
         * @var Game $gameManager
         * @var Player $player
         */
        $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];
        if ($gameManager->isGameOver()) {
            $players = $gameManager->getPlayers();
            $winner  = current($players)->getId();
            foreach (array_reverse($players) as $player) {
                Sender::sendMessage($player->getId(), Sender::MSG_GAME_OVER, ['winner' => $winner]);
                DataCenter::delRoomId($player->getId());
            }
            unset(DataCenter::$global['rooms'][$roomId]);
        }
    }
}