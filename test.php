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


while (!$game->isGameOver()) {
    $game->playerMove($redId, Player::DIRECTION[array_rand(Player::DIRECTION)]);
    $game->playerMove($blueId, Player::DIRECTION[array_rand(Player::DIRECTION)]);
    echo "\n\n\n\n\n\n";
    $game->printGameMap();
    echo "\n\n\n\n\n\n";
    if ($game->isGameOver()) {
        echo "GAME OVER";
        return;
    }
    usleep(300000);
}