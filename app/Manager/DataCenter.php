<?php
namespace app\Manager;

use app\Lib\Redis;

class DataCenter
{
    const PLAYER_ID  = 'playerId';
    const PLAYER_FD  = 'playerFd';
    const LIST_KEY   = 'wait_list';
    const ROOM_ID    = 'room_id';

    public static $global;
    public static $server;

    /**
     * 初始化redis数据
     */
    public static function initDataCenter() {
        self::getRedis()->delete(self::LIST_KEY);
        self::getRedis()->delete(self::PLAYER_ID);
        self::getRedis()->delete(self::PLAYER_FD);
        self::getRedis()->delete(self::ROOM_ID);
    }

    /**
     * 输出日志
     * @param string $info
     * @param array $content
     * @param string $level
     */
    public static function log(string $info, array $content = [], string $level = 'INFO') {
        if ($content) {
            echo sprintf("[%s][%s]: %s %s\n", date('Y-m-d H:i:s'), $level, $info,
                json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            echo sprintf("[%s][%s]: %s\n", date('Y-m-d H:i:s'), $level, $info);
        }
    }

    /**
     * 获取redis实例
     * @return \Redis
     */
    public static function getRedis() {
        return Redis::getInstance();
    }

    /**
     * 获取等待队列长度
     * @return int
     */
    public static function getPlayWaitListLen() {
        return self::getRedis()->lLen(self::LIST_KEY);
    }

    /**
     * 写入等待队列
     * @param string $playerId
     */
    public static function pushPlayerToWaitList(string $playerId) {
        self::getRedis()->lPush(self::LIST_KEY, $playerId);
    }

    /**
     * 弹出等待队列
     * @return string
     */
    public static function popPlayerToWaitList() {
        return self::getRedis()->rPop(self::LIST_KEY);
    }

    /**
     * 根据连接fd写入用户id
     * @param string $playerId 用户id
     * @param string $fd 连接fd
     */
    public static function setPlayerId(string $playerId, string $fd) {
        self::getRedis()->hSet(self::PLAYER_ID, $fd, $playerId);
    }

    /**
     * 根据连接fd获取用户id
     * @param string $fd
     * @return string
     */
    public static function getPlayerId(string $fd) {
        return self::getRedis()->hGet(self::PLAYER_ID, $fd);
    }

    /**
     * 根据连接fd删除用户id
     * @param string $fd
     */
    public static function delPlayerId(string $fd) {
        self::getRedis()->hDel(self::PLAYER_ID, $fd);
    }

    /**
     * 根据用户id写入连接fd
     * @param string $playerId
     * @param string $fd
     */
    public static function setPlayerFd(string $playerId, string $fd) {
        self::getRedis()->hSet(self::PLAYER_FD, $playerId, $fd);
    }

    /**
     * 根据用户id获取连接fd
     * @param string $playerId
     * @return string
     */
    public static function getPlayerFd(string $playerId) {
        return self::getRedis()->hGet(self::PLAYER_FD, $playerId);
    }

    /**
     * 根据用户id删除连接fd
     * @param string $playerId
     */
    public static function delPlayerFd(string $playerId) {
        self::getRedis()->hDel(self::PLAYER_FD, $playerId);
    }

    /**
     * 设置用户信息
     * @param string $playerId
     * @param string $fd
     */
    public static function setPlayerInfo(string $playerId, string $fd) {
        self::setPlayerId($playerId, $fd);
        self::setPlayerFd($playerId, $fd);
    }

    /**
     * 删除用户信息
     * @param string $fd
     */
    public static function delPlayerInfo(string $fd) {
        $playerId = self::getPlayerId($fd);

        self::delPlayerFd($playerId);
        self::delPlayerId($fd);
    }

    /**
     * 设置房间ID
     * @param string $roomId 房间ID
     * @param string $playerId 用户ID
     */
    public static function setRoomId(string $roomId, string $playerId) {
        self::getRedis()->hSet(self::ROOM_ID, $playerId, $roomId);
    }

    /**
     * 获取房间ID
     * @param string $playerId 用户ID
     * @return string
     */
    public static function getRoomId(string $playerId) {
        return self::getRedis()->hGet(self::ROOM_ID, $playerId);
    }

    /**
     * 删除房间ID
     * @param string $playerId 用户ID
     */
    public static function delRoomId(string $playerId) {
        self::getRedis()->hDel(self::ROOM_ID, $playerId);
    }
}