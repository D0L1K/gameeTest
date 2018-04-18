<?php
namespace Logic\Dto;

use Logic\Dto;

class ScorePositionDto extends Dto
{
    /** @var int */
    public $position;
    /** @var PlayerDto */
    public $player;
    /** @var int */
    public $score;
    /** @var int */
    public $date;
}