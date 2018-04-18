<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\PlayerGame;

class PlayerGameSimpleDto extends Dto
{
    /** @var int */
    public $gameId;
    /** @var int */
    public $playerId;
    /** @var int */
    public $scoreId;

    /**
     * @param PlayerGame $playerGame
     * @return self
     */
    public static function fromModel(PlayerGame $playerGame): self
    {
        $dto = new self();
        $dto->gameId = $playerGame->game->getId();
        $dto->playerId = $playerGame->player->getId();
        $dto->scoreId = $playerGame->scoreId;

        return $dto;
    }
}