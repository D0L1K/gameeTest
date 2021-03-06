<?php
namespace Logic;

use Kdyby\Redis\RedisClient;

class Session
{
    private const GENID_HASHKEY = 'idGenerator';

    /** @var RedisClient */
    private $dbClient;
    /** @var ConnectParams */
    private $connectParams;

    /** @var self|null */
    private static $_instance;

    /**
     * Session constructor.
     * @param ConnectParams $connectParams
     * @throws \Kdyby\Redis\MissingExtensionException
     */
    public function __construct(ConnectParams $connectParams)
    {
        $this->connectParams = $connectParams;
        $this->connect();
        self::$_instance = $this;
    }

    /**
     * @throws \Kdyby\Redis\MissingExtensionException
     */
    private function connect(): void
    {
        $this->dbClient = new RedisClient(
            $this->connectParams->getHost(),
            $this->connectParams->getPort(),
            $this->connectParams->getDatabase(),
            $this->connectParams->getTimeout(),
            $this->connectParams->getAuth(),
            $this->connectParams->isPersistent()
        );
        // TODO: connect with first request, not everytime on start
        // note: have to swallow warning about compatible declaration
        @$this->dbClient->connect();
    }

    /**
     * @return RedisClient
     */
    public function getClient(): RedisClient
    {
        return $this->dbClient;
    }

    /**
     * @return ConnectParams
     */
    public function getConnectParams(): ConnectParams
    {
        return $this->connectParams;
    }

    /**
     * @param string $tableKey
     * @return int
     */
    public function generateNextId(string $tableKey): int
    {
        $actual = $this->dbClient->hGet(self::GENID_HASHKEY, $tableKey);
        if ($actual === null) {
            return $this->createIdGenerator($tableKey);
        }

        return $this->dbClient->hIncrBy(self::GENID_HASHKEY, $tableKey, 1);
    }

    /**
     * @param string $tableKey
     * @return int
     */
    private function createIdGenerator(string $tableKey): int
    {
        $this->dbClient->hSet(self::GENID_HASHKEY, $tableKey, 1);

        return 1;
    }

    /**
     * @return Session
     * @throws \RuntimeException
     */
    public static function getCurrent(): Session
    {
        if (self::$_instance === null) {
            throw new \RuntimeException('Session has not been initialized yet');
        }
        return self::$_instance;
    }
}