<?php
namespace Model;

use Model\Orm\Column;
use Model\Orm\Object;

/**
 * Class Score
 * @package Model
 *
 * @property PlayerGame $playerGame
 * @property int $score
 * @property \DateTime $date
 */
class Score extends Object
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('scores');
        $this->addProperty('date', Column::TYPE_DATE, false, true);
        $this->addProperty('score', Column::TYPE_INT, true);
        parent::initMapping();
    }

    /**
     * @param PlayerGame $playerGame
     * @param int $score
     * @return Score
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function create(PlayerGame $playerGame, int $score): self
    {
        // TODO: do not save after every set
        $obj = new self();
        $obj->playerGame = $playerGame;
        $obj->score = $score;
        $obj->date = new \DateTime();
        $obj->save();

        return $obj;
    }
}