<?php
namespace Model;

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
        $this->addProperty('playerGame', PlayerGame::class, false, true);
        $this->addProperty('score', Object::TYPE_INT, false, false);
        $this->addProperty('date', Object::TYPE_DATE);
        parent::initMapping();
    }

    /**
     * @param PlayerGame $playerGame
     * @param int $score
     * @return Score
     * @throws \InvalidArgumentException
     */
    public static function create(PlayerGame $playerGame, int $score): self
    {
        $obj = new self();
        $obj->playerGame = $playerGame;
        $obj->score = $score;
        $obj->date = new \DateTime();
        $obj->save();

        return $obj;
    }
}