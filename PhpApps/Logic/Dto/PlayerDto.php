<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\Player;

class PlayerDto extends Dto
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string|null */
    public $city;

    /**
     * @param Player $player
     * @return self
     */
    public static function fromModel(Player $player): self
    {
        $dto = new self();
        $dto->id = $player->getId();
        $dto->name = $player->name;
        $dto->city = $player->city;

        return $dto;
    }
}