<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\ScoreMap;

class ScoreMapDto extends Dto
{
    /** @var GameDto */
    public $game;
    /** @var PlayerDto */
    public $player;
    /** @var int */
    public $scoreId;

    /**
     * @param ScoreMap $scoreMap
     * @return self
     */
    public static function fromModel(ScoreMap $scoreMap): self
    {
        $dto = new self();
        $dto->game = GameDto::fromModel($scoreMap->game);
        $dto->player = PlayerDto::fromModel($scoreMap->player);
        $dto->scoreId = $scoreMap->scoreId;

        return $dto;
    }
}