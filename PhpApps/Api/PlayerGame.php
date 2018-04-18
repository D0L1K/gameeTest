<?php

namespace Api;

use Logic\Dto\PlayerGameDto;
use Logic\Dto\PlayerGameSimpleDto;
use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Game as GameModel;
use Model\Player as PlayerModel;
use Model\PlayerGame as PlayerGameModel;

class PlayerGame
{
    /**
     * @param int $gameId
     * @param int $playerId
     * @param bool $extended
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function get(int $gameId, int $playerId, bool $extended = null): array
    {
        $playerGame = PlayerGameModel::getByIdAndFkId($gameId, $playerId);

        $dto = $extended ? PlayerGameDto::fromModel($playerGame) : PlayerGameSimpleDto::fromModel($playerGame);

        return $dto->toDto();
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

        return PlayerGameSimpleDto::fromModel($playerGame)->toDto();
    }
}