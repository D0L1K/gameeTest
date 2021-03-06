<?php

namespace Api;

use Logic\Dto\GameDto;
use Logic\Dto\ScoreDto;
use Logic\Dto\ScoreListDto;
use Logic\ScoreList;
use Logic\Session;
use Model\Orm\Exceptions\ObjectNotFoundException;
use Model\Game as GameModel;
use Model\Player as PlayerModel;
use Model\ScoreMap as ScoreMapModel;
use Model\Score as ScoreModel;

class Score
{
    /**
     * @param int $scoreId
     * @param string $timestamp
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     */
    public function get(int $scoreId, string $timestamp): array
    {
        $score = ScoreModel::getByIdAndFkId($scoreId, $timestamp);

        return ScoreDto::fromModel($score)->toDto();
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
        $scoreMap = ScoreMapModel::getByIdAndFkId($gameId, $playerId);
        if ($scoreMap === null) {
            $scoreMap = ScoreMapModel::create($player, $game);
        }
        $scoreObj = ScoreModel::create($scoreMap, $score);

        return ScoreDto::fromModel($scoreObj)->toDto();
    }

    /**
     * @param int $gameId
     * @param int|null $top
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     * @throws \ReflectionException
     */
    public function getTop(int $gameId, int $top = null): array
    {
        if ($top === null) {
            $top = 10;
        }
        $game = GameModel::getById($gameId);
        $session = $this->getSession();
        $scoreList = new ScoreList($session->getClient());

        $dto = new ScoreListDto();
        $dto->game = GameDto::fromModel($game);
        $dto->scorePositions = $scoreList->getTopByGame($game, $top);

        return $dto->toDto();
    }

    /**
     * @return Session
     * @throws \RuntimeException
     */
    private function getSession(): Session
    {
        return Session::getCurrent();
    }
}