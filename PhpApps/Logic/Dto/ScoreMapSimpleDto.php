<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\ScoreMap;

class ScoreMapSimpleDto extends Dto
{
    /** @var int */
    public $gameId;
    /** @var int */
    public $playerId;
    /** @var int */
    public $scoreId;

    /**
     * @param ScoreMap $scoreMap
     * @return self
     */
    public static function fromModel(ScoreMap $scoreMap): self
    {
        $dto = new self();
        $dto->gameId = $scoreMap->game->getId();
        $dto->playerId = $scoreMap->player->getId();
        $dto->scoreId = $scoreMap->scoreId;

        return $dto;
    }
}