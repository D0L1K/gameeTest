<?php

namespace Api;

use Logic\ScoreList;
use Logic\Session;
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
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function insert(int $gameId, int $playerId, int $score): array
    {
        $player = PlayerModel::getById($playerId);
        $game = GameModel::getById($gameId);
        $playerGame = PlayerGameModel::getByIdAndFkId($gameId, $playerId);
        if ($playerGame === null) {
            $playerGame = PlayerGameModel::create($player, $game);
        }
        $scoreObj = ScoreModel::create($playerGame, $score);

        return ['scoreId' => $playerGame->scoreId, 'date' => $scoreObj->date, 'insertedScore' => $scoreObj->score];
    }

    /**
     * @param int $gameId
     * @param int|null $top
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function getTop(int $gameId, int $top = null): array
    {
        if ($top === null) {
            $top = 10;
        }
        $game = GameModel::getById($gameId);
        $session = $this->getSession();
        $scoreList = new ScoreList($session->getClient());

        return ['gameId' => $game->getId(), 'scoreList' => $scoreList->getTopByGame($game, $top)];
    }

    /**
     * @return Session
     */
    private function getSession(): Session
    {
        return Session::getCurrent();
    }
}