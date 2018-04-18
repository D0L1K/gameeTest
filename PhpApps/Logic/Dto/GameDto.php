<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\Game;

class GameDto extends Dto
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;

    /**
     * @param Game $game
     * @return self
     */
    public static function fromModel(Game $game): self
    {
        $dto = new self();
        $dto->id = $game->getId();
        $dto->name = $game->name;

        return $dto;
    }
}