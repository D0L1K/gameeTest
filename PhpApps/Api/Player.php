<?php

namespace Api;

use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Player as PlayerModel;

class Player
{
    /**
     * @param int $playerId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     */
    public function get(int $playerId): array
    {
        $player = PlayerModel::getById($playerId);

        $player2 = PlayerModel::getById($playerId);

        return [1];
    }

    /**
     * @param string $name
     * @param string|null $city
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function insert(string $name, string $city = null): array
    {
        $player = PlayerModel::create($name, $city);

        return ['playerId' => $player->getId()];
    }
}