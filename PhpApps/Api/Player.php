<?php

namespace Api;

use Logic\Exceptions\ObjectNotFoundException;
use Model\Player as PlayerModel;

class Player
{
    /**
     * @param int $playerId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function get(int $playerId): array
    {
        $player = PlayerModel::getById($playerId);

        $player2 = PlayerModel::getById($playerId);

        return [1];
    }
}