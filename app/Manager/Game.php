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
        $x = $this->players[$playerId]->getX();
        $y = $this->players[$playerId]->getY();

        $isMove = $this->checkCanMove($x, $y);
        if (!$isMove) {
            switch ($direction) {
                case Player::UP:
                    $this->players[$playerId]->{Player::DOWN}();
                    break;
                case Player::DOWN:
                    $this->players[$playerId]->{Player::UP}();
                    break;
                case Player::LEFT:
                    $this->players[$playerId]->{Player::RIGHT}();
                    break;
                default:
                    $this->players[$playerId]->{Player::LEFT}();
            }
        }
    }

    /**
     * 验证是否可以移动
     * @param int $x
     * @param int $y
     * @return bool
     */
    private function checkCanMove(int $x, int $y) {
        $mapData = $this->gameMap->getMapData();
        return (bool)$mapData[$x][$y];
    }

    /**
     * 打印地图
     */
    public function printGameMap() {
        $mapData = $this->gameMap->getMapData();
        if (!empty($this->players)) {
            $role = [2 => '寻', 3 => '藏'];
            /**
             * @var Player $player
             */
            foreach ($this->players as $player) {
                $mapData[$player->getX()][$player->getY()] = $player->getType() + 1;
            }
        }

        foreach ($mapData as $item) {
            foreach ($item as $value) {
                if (empty($value)) {
                    echo '口';
                } elseif ($value == 1) {
                    echo '  ';
                }
                if (isset($role) && $value && $value != 1) {
                    echo $role[$value];
                }
            }
            echo "\n";
        }
    }

    /**
     * 验证游戏结束
     * @return bool
     */
    public function isGameOver() {
        $players = $this->players ?: [];
        $result  = false;

        if ($players) {
            $x = -1;
            $y = -1;
            $players = array_values($this->players);
            /**
             * @var Player $player
             */
            foreach ($players as $key => $player) {
                if ($key == 0) {
                    $x = $player->getX();
                    $y = $player->getY();
                } elseif ($player->getX() == $x && $player->getY() == $y) {
                    echo $result;
                    $result = true;
                }
            }
            return $result;
        }
    }
}