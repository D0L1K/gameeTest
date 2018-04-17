<?php
namespace Model;

use Model\Orm\Column;
use Model\Orm\Object;

/**
 * Class Score
 * @package Model
 *
 * @property Player $player
 * @property Game $game
 * @property int $playerGameId
 */
class PlayerGame extends Object
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('playerGame');
        $this->addProperty('game', Game::class, false, false, true);
        $this->addProperty('player', Player::class, false, true);
        $this->addProperty('scoreId', Column::TYPE_INT);
        parent::initMapping();
    }

    /**
     * @param Player $player
     * @param Game $game
     * @return PlayerGame
     * @throws \InvalidArgumentException
     */
    public static function create(Player $player, Game $game): self
    {
        $obj = new self();
        $obj->player = $player;
        $obj->game = $game;
        $obj->save();

        return $obj;
    }
}