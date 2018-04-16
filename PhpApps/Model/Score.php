<?php
namespace Model;

/**
 * Class Score
 * @package Model
 *
 * @property int $id
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
}