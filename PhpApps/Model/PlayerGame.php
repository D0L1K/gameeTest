<?php
namespace Model;

/**
 * Class Score
 * @package Model
 *
 * @property int $id
 * @property Player $player
 * @property Game $game
 * @property int $score
 * @property int $value
 */
class PlayerGame extends Object
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('playerGame');
        $this->addProperty('player', Player::class);
        $this->addProperty('game', Game::class, false, true);
        $this->addProperty('playerGameId', self::TYPE_INT, false, false);
        parent::initMapping();
    }
}