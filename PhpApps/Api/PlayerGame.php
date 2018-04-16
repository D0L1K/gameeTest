<?php

namespace Api;

use Logic\Exceptions\ObjectNotFoundException;
use Model\PlayerGame as PlayerGameModel;

class PlayerGame
{
    /**
     * @param int $gameId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function get(int $gameId): array
    {
        $playerGame = PlayerGameModel::getById($gameId);

        return [1];
    }
}