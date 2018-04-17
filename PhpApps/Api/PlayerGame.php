<?php

namespace Api;

use Model\Orm\Exceptions\ObjectNotFoundException;
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
}