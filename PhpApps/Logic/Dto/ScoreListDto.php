<?php
namespace Logic\Dto;

use Logic\Dto;

class ScoreListDto extends Dto
{
    /** @var GameDto */
    public $game;
    /** @var ScorePositionDto[] */
    public $scorePositions;
}