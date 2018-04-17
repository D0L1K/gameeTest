<?php

namespace Api;

use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Game as GameModel;
use Model\Player as PlayerModel;
use Model\PlayerGame as PlayerGameModel;

class PlayerGame
{
    /**
     * @param int $gameId
     * @param int $playerId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function get(int $gameId, int $playerId): array
    {
        $playerGame = PlayerGameModel::getByIdAndFkId($gameId, $playerId);

        return [1];
    }

    /**
     * @param int $gameId
     * @param int $playerId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function insert(int $gameId, int $playerId): array
    {
        $player = PlayerModel::getById($playerId);
        $game = GameModel::getById($gameId);
        $playerGame = PlayerGameModel::create($player, $game);

        return ['scoreId' => $playerGame->scoreId];
    }
}