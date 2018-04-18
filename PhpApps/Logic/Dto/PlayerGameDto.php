<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\PlayerGame;

class PlayerGameDto extends Dto
{
    /** @var GameDto */
    public $game;
    /** @var PlayerDto */
    public $player;
    /** @var int */
    public $scoreId;

    /**
     * @param PlayerGame $playerGame
     * @return self
     */
    public static function fromModel(PlayerGame $playerGame): self
    {
        $dto = new self();
        $dto->game = GameDto::fromModel($playerGame->game);
        $dto->player = PlayerDto::fromModel($playerGame->player);
        $dto->scoreId = $playerGame->scoreId;

        return $dto;
    }
}