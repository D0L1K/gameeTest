<?php

namespace Api;

use Model\Player;

class Players
{
    /**
     * @param int $playerId
     * @return array
     */
    public function get(int $playerId): array
    {
        $player = Player::getById($playerId);

        return [1];
    }
}