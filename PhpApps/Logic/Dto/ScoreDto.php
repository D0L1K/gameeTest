<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\Score;

class ScoreDto extends Dto
{
    /** @var PlayerDto */
    public $player;
    /** @var GameDto */
    public $game;
    /** @var int */
    public $score;
    /** @var int */
    public $date;

    /**
     * @param Score $score
     * @return self
     */
    public static function fromModel(Score $score): self
    {
        $dto = new self();
        $dto->game = GameDto::fromModel($score->playerGame->game);
        $dto->player = PlayerDto::fromModel($score->playerGame->player);
        $dto->score = $score->score;
        $dto->date = $score->date;

        return $dto;
    }
}