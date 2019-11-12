<?php
namespace app\Manager;

class TaskManager
{
    const TASK_CODE_FIND_PLAYER = 1;

    public static function findPlayer() {
        $playerListLen = DataCenter::getPlayWaitListLen();

        if ($playerListLen >= 2) {
            $redPlayer  = DataCenter::popPlayerToWaitList();
            $bluePlayer = DataCenter::popPlayerToWaitList();

            return [
                'red_player'  => $redPlayer,
                'blue_player' => $bluePlayer
            ];
        }

        return false;
    }
}