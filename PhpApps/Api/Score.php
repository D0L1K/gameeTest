<?php

namespace Api;

use Nette\Application\BadRequestException;

class Score
{
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