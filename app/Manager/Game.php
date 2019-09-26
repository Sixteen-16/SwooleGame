<?php
namespace App\Manager;

use app\Model\Map;
use app\Model\Player;

class Game
{
    private $gameMap = [];
    private $players = [];

    public function __construct()
    {
        $this->gameMap = new Map(12, 12);
    }

    /**
     * 创建玩家
     * @param $playerId
     * @param $x
     * @param $y
     */
    public function createPlayer(string $playerId, float $x, float $y) {
        $player = new Player($playerId, $x, $y);
        // 先来的为寻找方 后来的为隐藏方
        if (!empty($this->players)) {
            $player->setType($player::PLAY_TYPE_HIDE);
        }
        $this->players[$playerId] = $player;
    }

    /**
     * 玩家移动
     * @param $playerId
     * @param $direction
     */
    public function playerMove(string $playerId, string $direction) {
        $this->players[$playerId]->{$direction}();
    }

    /**
     * 打印地图
     */
    public function printGameMap() {
        $mapData = $this->gameMap->getMapData();
        foreach ($mapData as $y => $item) {
            foreach ($item as $x => $value) {
                if (empty($value)) {
                    echo '墙,';
                } else {
                    echo '   ';
                }
                if (!empty($this->players)) {
                    foreach ($this->players as $player) {
                        if ($player->getX() == $x && $player->getY() == $y) {
                            if ($player->getType() == Player::PLAY_TYPE_SEARCH) {
                                echo '寻!';
                            } else {
                                echo '藏!';
                            }
                        }
                    }
                }
            }
            echo "\n";
        }
    }
}