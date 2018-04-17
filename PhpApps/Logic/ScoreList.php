<?php
namespace Logic;

use Kdyby\Redis\RedisClient;
use Model\Game;
use Model\PlayerGame;

class ScoreList
{
    /** @var RedisClient */
    private $dbClient;

    /**
     * ScoreList constructor.
     * @param RedisClient $dbClient
     */
    public function __construct(RedisClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    /**
     * @param Game $game
     * @param int $top
     * @return array
     * @throws \RuntimeException
     */
    public function getTopByGame(Game $game, int $top): array
    {
        $playerIds = $this->dbClient->hGetAll(PlayerGame::getHashKey($game->getId()));
        foreach ($playerIds as $playerId) {
            // TODO: load scores, order them, load users, ez.
        }

        return [];
    }
}