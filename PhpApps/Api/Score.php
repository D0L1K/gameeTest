<?php

namespace Api;

use Nette\Application\BadRequestException;
use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Game as GameModel;
use Model\Player as PlayerModel;
use Model\PlayerGame as PlayerGameModel;
use Model\Score as ScoreModel;

class Score
{
    /**
     * @param int $scoreId
     * @param int $timestamp
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     */
    public function get(int $scoreId, int $timestamp): array
    {
        $score = ScoreModel::getByIdAndFkId($scoreId, $timestamp);

        return [1];
    }

    /**
     * @param int $gameId
     * @param int $playerId
     * @param int $score
     * @return int
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function insert(int $gameId, int $playerId, int $score): int
    {
        $player = PlayerModel::getById($playerId);
        $game = GameModel::getById($gameId);
        $playerGame = PlayerGameModel::getByPlayerAndGame($player, $game);
        if ($playerGame === null) {
            $playerGame = PlayerGameModel::create($player, $game);
        }
        ScoreModel::create($playerGame, $score);

        return 1;
    }

    public function getTop(int $first = null)
    {
        if ($first === null) {
            $first = 10;
        }
        throw new BadRequestException('Test exception');
    }
}