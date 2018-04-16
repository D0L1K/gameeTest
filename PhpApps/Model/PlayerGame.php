<?php
namespace Model;

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
        $this->addProperty('player', Player::class);
        $this->addProperty('game', Game::class, false, true);
        $this->addProperty('playerGameId', self::TYPE_INT, false, false);
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

    /**
     * @param Player $player
     * @param Game $game
     * @return self|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Logic\Exceptions\ObjectNotFoundException
     */
    public static function getByPlayerAndGame(Player $player, Game $game): ?self
    {
        $obj = new self();
        $id = $obj->getDbClient()->hGet($obj->getHashKey($game->getId()), $player->getId());
        if ($id === null) {
            return null;
        }

        return $obj->load($id);
    }
}