<?php

namespace Api;

use Logic\Dto\ScoreMapDto;
use Logic\Dto\ScoreMapSimpleDto;
use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Game as GameModel;
use Model\Player as PlayerModel;
use Model\ScoreMap as ScoreMapModel;

class ScoreMap
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
        $scoreMap = ScoreMapModel::getByIdAndFkId($gameId, $playerId);

        $dto = $extended ? ScoreMapDto::fromModel($scoreMap) : ScoreMapSimpleDto::fromModel($scoreMap);

        return $dto->toDto();
    }

    /**
     * @param int $gameId
     * @param int $playerId
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function getScoreId(int $gameId, int $playerId): int
    {
        $scoreMap = ScoreMapModel::getByIdAndFkId($gameId, $playerId);

        return $scoreMap->getId();
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
        $scoreMap = ScoreMapModel::create($player, $game);

        return ScoreMapSimpleDto::fromModel($scoreMap)->toDto();
    }
}