<?php
namespace app\Model;

class Player
{
    const UP    = 'up';
    const DOWN  = 'down';
    const LEFT  = 'left';
    const RIGHT = 'right';
    const DIRECTION = [
        self::UP,
        self::DOWN,
        self::LEFT,
        self::RIGHT
    ];

    const PLAY_TYPE_SEARCH = 1;
    const PLAY_TYPE_HIDE   = 2;

    private $id;
    private $type = self::PLAY_TYPE_SEARCH;
    private $x;
    private $y;

    public function __construct(string $id, int $x, int $y)
    {
        $this->id = $id;
        $this->x  = $x;
        $this->y  = $y;
    }

    public function setType(int $type) {
        $this->type = $type;
    }

    public function getId() {
        return $this->id;
    }

    public function up() {
        $this->x--;
    }

    public function down() {
        $this->x++;
    }

    public function left() {
        $this->y--;
    }

    public function right() {
        $this->y++;
    }

    public function getX() {
        return $this->x;
    }

    public function getY() {
        return $this->y;
    }

    public function getType() {
        return $this->type;
    }
}