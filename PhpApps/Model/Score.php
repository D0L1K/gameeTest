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
 * @property \DateTime $date
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
        $this->addProperty('date', Model::TYPE_DATE);
    }

    /**
     * @return string
     */
    protected function getTableKey(): string
    {
        return $this->getTableKey() . '_' . $this->game->getId();
    }

    /**
     * @return string
     */
    protected function getHashKey(): string
    {
        return $this->player->getId() . '_' . $this->date->getTimestamp();
    }
}