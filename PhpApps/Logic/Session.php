<?php
namespace Logic;

use Kdyby\Redis\RedisClient;

class Session
{
    /** @var RedisClient */
    private $dbClient;

    /** @var self|null */
    private static $_instance;

    /**
     * Session constructor.
     * @param RedisClient $dbClient
     */
    public function __construct(RedisClient $dbClient)
    {
        $this->dbClient = $dbClient;
        self::$_instance = $this;
    }

    /**
     * @return RedisClient
     */
    public function getClient(): RedisClient
    {
        return $this->dbClient;
    }

    /**
     * @return Session
     * @throws \Exception
     */
    public static function getCurrent(): Session
    {
        if (self::$_instance === null) {
            throw new \RuntimeException('Session has not been initialized yet');
        }
        return self::$_instance;
    }
}