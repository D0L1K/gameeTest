<?php

namespace Api;

use Nette\Application\BadRequestException;
use Logic\Exceptions\ObjectNotFoundException;
use Model\Score as ScoreModel;

class Score
{
    /**
     * @param int $gameId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws ObjectNotFoundException
     */
    public function get(int $gameId): array
    {
        $score = ScoreModel::getById($gameId);

        return [1];
    }

    public function insert(int $gameId, int $playerId, int $score)
    {
        return ['test' => 1, 'test2' => 2];
    }

    public function getTop(int $first = null)
    {
        if ($first === null) {
            $first = 10;
        }
        throw new BadRequestException('Test exception');
    }
}