<?php

namespace Api;

use Logic\Exceptions\ObjectNotFoundException;
use Model\Player;

class Players
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
        $player = Player::getById($playerId);

        $player2 = Player::getById($playerId);

        return [1];
    }
}