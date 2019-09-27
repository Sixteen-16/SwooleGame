<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Manager\Game;
use App\Model\Player;


$redId  = 'red_player';
$blueId = 'blue_player';

$game = new Game();
$game->createPlayer($redId, 6, 1);
$game->createPlayer($blueId, 6, 10);
$game->playerMove($redId, Player::UP);
$game->playerMove($redId, Player::UP);
$game->playerMove($redId, Player::UP);
$game->printGameMap();