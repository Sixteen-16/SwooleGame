<?php
namespace app\Manager;

class Logic
{
    public function matchPlayer(int $playerId) {
        DataCenter::pushPlayerToWaitList($playerId);
    }
}