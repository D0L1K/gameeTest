<?php
namespace Model;

/**
 * Class Score
 * @package Model
 *
 * @var int $id
 * @var Player $player
 * @var Game $game
 * @var int $score
 */
class Score extends Model
{
    protected function initMapping(): void
    {
        parent::initMapping();
        $this->setTableKey('scores');
        $this->addProperty('player', Player::class, 'playerId');
        $this->addProperty('game', Game::class, 'gameId');
        $this->addProperty('score', Model::TYPE_INT);
    }
}