<?php
namespace Logic;

use Kdyby\Redis\RedisClient;
use Logic\Dto\PlayerDto;
use Logic\Dto\ScoreDto;
use Model\Game;
use Model\Player;
use Model\PlayerGame;
use Model\Score;

class ScoreList
{
    /** @var RedisClient */
    private $dbClient;

    /**
     * ScoreList constructor.
     * @param RedisClient $dbClient
     */
    public function __construct(RedisClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    /**
     * @param Game $game
     * @param int $top
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     * @throws \ReflectionException
     */
    public function getTopByGame(Game $game, int $top): array
    {
        $scores = $this->loadScores($game);
        if (\count($scores) === 0) {
            return [];
        }
        usort($scores, [$this, 'sortScores']);
        $scores = \array_slice($scores, 0, $top);

        return $this->resolvePositions($scores);
    }

    /**
     * @param Game $game
     * @return array
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     * @throws \ReflectionException
     */
    private function loadScores(Game $game): array
    {
        $playerIds = $this->dbClient->hGetAll(PlayerGame::getHashKey($game->getId()));
        $scores = [];
        foreach ($playerIds as $playerId => $scoreId) {
            $playerScores = $this->dbClient->hGetAll(Score::getHashKey($scoreId));
            $player = Player::getById($playerId);
            $playerDto = PlayerDto::fromModel($player);
            foreach ($playerScores as $date => $score) {
                $scores[] = ScoreDto::fromDto(['player' => $playerDto, 'score' => (int)$score, 'date' => $date]);
            }
        }

        return $scores;
    }

    /**
     * @param ScoreDto $a
     * @param ScoreDto $b
     * @return int
     */
    private function sortScores(ScoreDto $a, ScoreDto $b): int
    {
        if ($a->score === $b->score) {
            return 0;
        }

        return $a->score > $b->score ? -1 : 1;
    }

    /**
     * @param ScoreDto[] $scores
     * @return ScoreDto[]
     */
    private function resolvePositions(array $scores): array
    {
        $pos = $i = 1;
        $lastScore = null;
        /** @var ScoreDto[] $scores */
        foreach ($scores as $score) {
            if ($lastScore > $score->score) {
                $pos = $i;
            }
            $score->position = $pos;
            $lastScore = $score->score;
            $i++;
        }

        return $scores;
    }
}