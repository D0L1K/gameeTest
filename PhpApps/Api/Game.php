<?php

namespace Api;

use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Game as GameModel;

class Game
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
        $game = GameModel::getById($gameId);

        return [1];
    }
}