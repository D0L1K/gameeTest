<?php
namespace Logic\Dto;

use Logic\Dto;
use Model\Score;

class ScoreDto extends Dto
{
    /** @var int */
    public $scoreId;
    /** @var int */
    public $score;
    /** @var int */
    public $date;

    /**
     * @param Score $score
     * @return self
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromModel(Score $score): self
    {
        $dto = new self();
        $dto->scoreId = $score->scoreId;
        $dto->score = $score->score;
        $dto->date = $score->date;

        return $dto;
    }
}